<?php
// src/Backend/Service/Systeme/ServiceSysteme.php

namespace App\Backend\Service\Systeme;

use PDO;
use App\Backend\Model\GenericModel;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Config\Container; // Important pour accéder au schéma

class ServiceSysteme implements ServiceSystemeInterface
{
    private PDO $db;
    private GenericModel $parametresModel;
    private GenericModel $anneeAcademiqueModel;
    private GenericModel $sequencesModel;
    private ServiceSupervisionInterface $supervisionService;
    private Container $container; // Le conteneur est injecté pour accéder au schéma
    private ?array $parametresCache = null;
    private array $modelFactoryCache = [];

    public function __construct(
        PDO $db,
        GenericModel $parametresModel,
        GenericModel $anneeAcademiqueModel,
        GenericModel $sequencesModel,
        ServiceSupervisionInterface $supervisionService,
        Container $container // Injection du conteneur lui-même
    ) {
        $this->db = $db;
        $this->parametresModel = $parametresModel;
        $this->anneeAcademiqueModel = $anneeAcademiqueModel;
        $this->sequencesModel = $sequencesModel;
        $this->supervisionService = $supervisionService;
        $this->container = $container;
    }

    // --- Gestion des Identifiants ---
    public function genererIdentifiantUnique(string $prefixe): string
    {
        $this->db->beginTransaction();
        try {
            $anneeActive = $this->getAnneeAcademiqueActive();
            if (!$anneeActive) {
                throw new OperationImpossibleException("Impossible de générer un ID : aucune année académique n'est active.");
            }
            $annee = (int) substr($anneeActive['libelle_annee_academique'], 0, 4);

            $stmt = $this->db->prepare("SELECT valeur_actuelle FROM sequences WHERE nom_sequence = :prefixe AND annee = :annee FOR UPDATE");
            $stmt->execute([':prefixe' => $prefixe, ':annee' => $annee]);
            $sequence = $stmt->fetch(PDO::FETCH_ASSOC);

            $nextValue = $sequence ? $sequence['valeur_actuelle'] + 1 : 1;

            $sequencesModel = $this->container->getModelForTable('sequences');
            if ($sequence) {
                // Note: BaseModel n'a pas de méthode pour les clés composites, il faut utiliser une requête directe ou l'améliorer.
                // Pour la robustesse, utilisons une requête directe ici.
                $updateStmt = $this->db->prepare("UPDATE sequences SET valeur_actuelle = :valeur WHERE nom_sequence = :prefixe AND annee = :annee");
                $updateStmt->execute([':valeur' => $nextValue, ':prefixe' => $prefixe, ':annee' => $annee]);
            } else {
                $sequencesModel->creer([
                    'nom_sequence' => $prefixe,
                    'annee' => $annee,
                    'valeur_actuelle' => $nextValue
                ]);
            }

            $this->db->commit();
            $identifiant = sprintf('%s-%d-%04d', $prefixe, $annee, $nextValue);
            $this->supervisionService->enregistrerAction('SYSTEM', 'GENERATION_ID_UNIQUE', null, $identifiant, 'Identifiant');
            return $identifiant;
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->supervisionService->enregistrerAction('SYSTEM', 'ECHEC_GENERATION_ID_UNIQUE', null, $prefixe, 'Identifiant', ['error' => $e->getMessage()]);
            throw new OperationImpossibleException("Échec de la génération de l'identifiant unique pour le préfixe '{$prefixe}'.", 0, $e);
        }
    }

    // --- Gestion des Paramètres et du Mode Maintenance ---
    public function getParametre(string $cle, mixed $defaut = null)
    {
        if ($this->parametresCache === null) {
            $this->getAllParametres();
        }
        return $this->parametresCache[$cle] ?? $defaut;
    }

    public function getAllParametres(): array
    {
        if ($this->parametresCache === null) {
            $params = $this->parametresModel->trouverTout();
            $this->parametresCache = array_column($params, 'valeur', 'cle');
        }
        return $this->parametresCache;
    }

    public function setParametres(array $parametres): bool
    {
        $this->parametresModel->commencerTransaction();
        try {
            foreach ($parametres as $cle => $valeur) {
                $this->parametresModel->mettreAJourParIdentifiant($cle, ['valeur' => (string) $valeur]);
            }
            $this->parametresModel->validerTransaction();
            $this->parametresCache = null;
            $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'MISE_AJOUR_PARAMETRES');
            return true;
        } catch (\Exception $e) {
            $this->parametresModel->annulerTransaction();
            throw $e;
        }
    }

    public function activerMaintenanceMode(bool $actif, string $message = "Le site est en cours de maintenance. Veuillez réessayer plus tard."): bool
    {
        return $this->setParametres([
            'MAINTENANCE_MODE_ENABLED' => $actif ? '1' : '0',
            'MAINTENANCE_MODE_MESSAGE' => $message
        ]);
    }

    public function estEnMaintenance(): bool
    {
        return (bool) $this->getParametre('MAINTENANCE_MODE_ENABLED', false);
    }

    // --- Gestion des Années Académiques ---
    public function getAnneeAcademiqueActive(): ?array
    {
        return $this->anneeAcademiqueModel->trouverUnParCritere(['est_active' => 1]);
    }

    public function setAnneeAcademiqueActive(string $idAnneeAcademique): bool
    {
        $this->anneeAcademiqueModel->commencerTransaction();
        try {
            $this->db->exec("UPDATE annee_academique SET est_active = 0 WHERE est_active = 1");
            $success = $this->anneeAcademiqueModel->mettreAJourParIdentifiant($idAnneeAcademique, ['est_active' => 1]);
            if (!$success) throw new OperationImpossibleException("Impossible d'activer l'année académique '{$idAnneeAcademique}'.");
            $this->anneeAcademiqueModel->validerTransaction();
            $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'CHANGEMENT_ANNEE_ACTIVE', $idAnneeAcademique, 'AnneeAcademique');
            return true;
        } catch (\Exception $e) {
            $this->anneeAcademiqueModel->annulerTransaction();
            throw $e;
        }
    }

    // --- Gestion des Référentiels ---
    public function gererReferentiel(string $operation, string $nomReferentiel, ?string $id = null, ?array $donnees = null)
    {
        $model = $this->container->getModelForTable($nomReferentiel);

        switch (strtolower($operation)) {
            case 'list':
                return $model->trouverTout();

            case 'read':
                if ($id === null) throw new \InvalidArgumentException("L'ID est requis pour l'opération 'read'.");
                return $model->trouverParIdentifiant($id);

            case 'create':
                if ($donnees === null) throw new \InvalidArgumentException("Les données sont requises pour l'opération 'create'.");
                $result = $model->creer($donnees);
                $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'CREATE_REFERENTIEL', null, $id, $nomReferentiel, $donnees);
                return $result;

            case 'update':
                if ($id === null || $donnees === null) throw new \InvalidArgumentException("L'ID et les données sont requis pour l'opération 'update'.");
                $result = $model->mettreAJourParIdentifiant($id, $donnees);
                $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'UPDATE_REFERENTIEL', null, $id, $nomReferentiel, $donnees);
                return $result;

            case 'delete':
                if ($id === null) throw new \InvalidArgumentException("L'ID est requis pour l'opération 'delete'.");
                // Ajouter une vérification de dépendances avant de supprimer serait une bonne pratique
                $result = $model->supprimerParIdentifiant($id);
                $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'DELETE_REFERENTIEL', null, $id, $nomReferentiel);
                return $result;

            default:
                throw new \InvalidArgumentException("Opération '{$operation}' non reconnue sur le référentiel '{$nomReferentiel}'.");
        }
    }
}