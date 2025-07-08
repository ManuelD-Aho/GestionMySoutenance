<?php

namespace App\Backend\Service\Systeme;

use PDO;
use App\Backend\Model\GenericModel;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Config\Container;

class ServiceSysteme implements ServiceSystemeInterface
{
    private PDO $db;
    private GenericModel $parametresModel;
    private GenericModel $anneeAcademiqueModel;
    private GenericModel $sequencesModel;
    private ServiceSupervisionInterface $supervisionService;
    private Container $container;
    private ?array $parametresCache = null;

    public function __construct(
        PDO $db,
        GenericModel $parametresModel,
        GenericModel $anneeAcademiqueModel,
        GenericModel $sequencesModel,
        ServiceSupervisionInterface $supervisionService,
        Container $container
    ) {
        $this->db = $db;
        $this->parametresModel = $parametresModel;
        $this->anneeAcademiqueModel = $anneeAcademiqueModel;
        $this->sequencesModel = $sequencesModel;
        $this->supervisionService = $supervisionService;
        $this->container = $container;
    }

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

            if ($sequence) {
                $updateStmt = $this->db->prepare("UPDATE sequences SET valeur_actuelle = :valeur WHERE nom_sequence = :prefixe AND annee = :annee");
                $updateStmt->execute([':valeur' => $nextValue, ':prefixe' => $prefixe, ':annee' => $annee]);
            } else {
                $this->sequencesModel->creer(['nom_sequence' => $prefixe, 'annee' => $annee, 'valeur_actuelle' => $nextValue]);
            }

            $this->db->commit();
            return sprintf('%s-%d-%04d', strtoupper($prefixe), $annee, $nextValue);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->supervisionService->enregistrerAction('SYSTEM', 'ECHEC_GENERATION_ID_UNIQUE', null, $prefixe, 'Identifiant', ['error' => $e->getMessage()]);
            throw new OperationImpossibleException("Échec de la génération de l'identifiant pour '{$prefixe}'.", 0, $e);
        }
    }

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
            $this->parametresCache = null; // Invalider le cache
            $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'MISE_AJOUR_PARAMETRES');
            return true;
        } catch (\Exception $e) {
            $this->parametresModel->annulerTransaction();
            throw $e;
        }
    }

    public function activerMaintenanceMode(bool $actif, string $message = "Le site est en cours de maintenance."): bool
    {
        return $this->setParametres(['MAINTENANCE_MODE_ENABLED' => $actif ? '1' : '0', 'MAINTENANCE_MODE_MESSAGE' => $message]);
    }

    public function estEnMaintenance(): bool
    {
        return (bool) $this->getParametre('MAINTENANCE_MODE_ENABLED', false);
    }

    public function creerAnneeAcademique(string $libelle, string $dateDebut, string $dateFin, bool $estActive = false): string
    {
        $idAnnee = "ANNEE-" . str_replace('/', '-', $libelle);
        $this->anneeAcademiqueModel->creer([
            'id_annee_academique' => $idAnnee, 'libelle_annee_academique' => $libelle,
            'date_debut' => $dateDebut, 'date_fin' => $dateFin, 'est_active' => $estActive ? 1 : 0
        ]);
        if ($estActive) $this->setAnneeAcademiqueActive($idAnnee);

        $this->supervisionService->enregistrerAction($_SESSION['user_id'], 'CREATE_ANNEE_ACADEMIQUE', $idAnnee, 'AnneeAcademique');
        return $idAnnee;
    }

    public function lireAnneeAcademique(string $idAnneeAcademique): ?array
    {
        return $this->anneeAcademiqueModel->trouverParIdentifiant($idAnneeAcademique);
    }

    public function mettreAJourAnneeAcademique(string $idAnneeAcademique, array $donnees): bool
    {
        return $this->anneeAcademiqueModel->mettreAJourParIdentifiant($idAnneeAcademique, $donnees);
    }

    public function supprimerAnneeAcademique(string $idAnneeAcademique): bool
    {
        $inscriptionModel = $this->container->getModelForTable('inscrire');
        if ($inscriptionModel->trouverUnParCritere(['id_annee_academique' => $idAnneeAcademique])) {
            throw new OperationImpossibleException("Suppression impossible : des inscriptions sont liées à cette année académique.");
        }
        return $this->anneeAcademiqueModel->supprimerParIdentifiant($idAnneeAcademique);
    }

    public function listerAnneesAcademiques(): array
    {
        return $this->anneeAcademiqueModel->trouverParCritere([], ['*'], 'AND', 'date_debut DESC');
    }

    public function getAnneeAcademiqueActive(): ?array
    {
        return $this->anneeAcademiqueModel->trouverUnParCritere(['est_active' => 1]);
    }

    public function setAnneeAcademiqueActive(string $idAnneeAcademique): bool
    {
        $this->db->beginTransaction();
        try {
            $this->db->exec("UPDATE annee_academique SET est_active = 0 WHERE est_active = 1");
            $this->anneeAcademiqueModel->mettreAJourParIdentifiant($idAnneeAcademique, ['est_active' => 1]);
            $this->db->commit();
            $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'CHANGEMENT_ANNEE_ACTIVE', $idAnneeAcademique, 'AnneeAcademique');
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function gererReferentiel(string $operation, string $nomReferentiel, ?string $id = null, ?array $donnees = null)
    {
        $model = $this->container->getModelForTable($nomReferentiel);
        switch (strtolower($operation)) {
            case 'list': return $model->trouverTout();
            case 'read':
                if ($id === null) throw new \InvalidArgumentException("L'ID est requis pour l'opération 'read'.");
                return $model->trouverParIdentifiant($id);
            case 'create':
                if ($donnees === null) throw new \InvalidArgumentException("Les données sont requises pour 'create'.");
                $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'CREATE_REFERENTIEL', null, $nomReferentiel, $donnees);
                return $model->creer($donnees);
            case 'update':
                if ($id === null || $donnees === null) throw new \InvalidArgumentException("L'ID et les données sont requis pour 'update'.");
                $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'UPDATE_REFERENTIEL', $id, $nomReferentiel, $donnees);
                return $model->mettreAJourParIdentifiant($id, $donnees);
            case 'delete':
                if ($id === null) throw new \InvalidArgumentException("L'ID est requis pour 'delete'.");
                $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'DELETE_REFERENTIEL', $id, $nomReferentiel);
                return $model->supprimerParIdentifiant($id);
            default:
                throw new \InvalidArgumentException("Opération '{$operation}' non reconnue sur le référentiel.");
        }
    }

    public function updateMenuStructure(array $menuStructure): bool
    {
        $traitementModel = $this->container->getModelForTable('traitement', 'id_traitement');
        $this->db->beginTransaction();
        try {
            foreach ($menuStructure as $item) {
                if (!isset($item['id']) || !isset($item['ordre'])) {
                    throw new OperationImpossibleException("Structure de menu invalide: id ou ordre manquant.");
                }
                $dataToUpdate = [
                    'ordre_affichage' => (int) $item['ordre'],
                    'id_parent_traitement' => $item['parent'] ?? null
                ];
                if (!$traitementModel->mettreAJourParIdentifiant($item['id'], $dataToUpdate)) {
                    throw new OperationImpossibleException("Échec de la mise à jour de l'élément de menu : " . $item['id']);
                }
            }
            $this->db->commit();
            $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'UPDATE_MENU_STRUCTURE', null, 'Menu');
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}