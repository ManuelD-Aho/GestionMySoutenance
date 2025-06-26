<?php

namespace App\Backend\Service\AnneeAcademique;

use PDO;
use App\Backend\Model\AnneeAcademique;
use App\Backend\Model\Inscrire;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\ParametreSysteme;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;

class ServiceAnneeAcademique implements ServiceAnneeAcademiqueInterface
{
    private AnneeAcademique $anneeAcademiqueModel;
    private Inscrire $inscrireModel;
    private RapportEtudiant $rapportEtudiantModel;
    private ParametreSysteme $parametreSystemeModel;
    private ServiceSupervisionAdminInterface $supervisionService;

    public function __construct(
        PDO $db,
        AnneeAcademique $anneeAcademiqueModel,
        Inscrire $inscrireModel,
        RapportEtudiant $rapportEtudiantModel,
        ParametreSysteme $parametreSystemeModel,
        ServiceSupervisionAdminInterface $supervisionService
    ) {
        $this->anneeAcademiqueModel = $anneeAcademiqueModel;
        $this->inscrireModel = $inscrireModel;
        $this->rapportEtudiantModel = $rapportEtudiantModel;
        $this->parametreSystemeModel = $parametreSystemeModel;
        $this->supervisionService = $supervisionService;
    }

    public function definirAnneeAcademiqueActive(string $idAnneeAcademique): bool
    {
        if (!$this->anneeAcademiqueModel->trouverParIdentifiant($idAnneeAcademique)) {
            throw new ElementNonTrouveException("Année académique non trouvée : {$idAnneeAcademique}");
        }

        $this->anneeAcademiqueModel->commencerTransaction();
        try {
            $this->anneeAcademiqueModel->mettreAJourParCritere(['est_active' => 1], ['est_active' => 0]);
            $success = $this->anneeAcademiqueModel->mettreAJourParIdentifiant($idAnneeAcademique, ['est_active' => 1]);

            if (!$success) {
                throw new OperationImpossibleException("Échec de l'activation de l'année académique.");
            }

            $this->anneeAcademiqueModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'CONFIG_ANNEE_ACTIVE',
                "Année académique '{$idAnneeAcademique}' définie comme active"
            );
            return true;
        } catch (\Exception $e) {
            $this->anneeAcademiqueModel->annulerTransaction();
            throw $e;
        }
    }

    public function creerAnneeAcademique(string $idAnneeAcademique, string $libelle, string $dateDebut, string $dateFin, bool $estActive): bool
    {
        $this->anneeAcademiqueModel->commencerTransaction();
        try {
            if ($this->anneeAcademiqueModel->trouverParIdentifiant($idAnneeAcademique)) {
                throw new DoublonException("L'ID d'année académique '{$idAnneeAcademique}' existe déjà.");
            }
            if ($estActive) {
                $this->anneeAcademiqueModel->mettreAJourParCritere(['est_active' => 1], ['est_active' => 0]);
            }

            $data = [
                'id_annee_academique' => $idAnneeAcademique,
                'libelle_annee_academique' => $libelle,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'est_active' => $estActive
            ];

            $success = $this->anneeAcademiqueModel->creer($data);
            if (!$success) {
                throw new OperationImpossibleException("Échec de la création de l'année académique.");
            }

            $this->anneeAcademiqueModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'CREATION_ANNEE_ACADEMIQUE',
                "Année académique '{$libelle}' (ID: {$idAnneeAcademique}) créée."
            );
            return true;
        } catch (\Exception $e) {
            $this->anneeAcademiqueModel->annulerTransaction();
            throw $e;
        }
    }

    public function modifierAnneeAcademique(string $idAnneeAcademique, array $donnees): bool
    {
        $annee = $this->anneeAcademiqueModel->trouverParIdentifiant($idAnneeAcademique);
        if (!$annee) {
            throw new ElementNonTrouveException("Année académique '{$idAnneeAcademique}' non trouvée.");
        }

        $this->anneeAcademiqueModel->commencerTransaction();
        try {
            if (isset($donnees['libelle_annee_academique']) && $donnees['libelle_annee_academique'] !== $annee['libelle_annee_academique']) {
                if ($this->anneeAcademiqueModel->trouverUnParCritere(['libelle_annee_academique' => $donnees['libelle_annee_academique']])) {
                    throw new DoublonException("Le libellé d'année académique '{$donnees['libelle_annee_academique']}' existe déjà.");
                }
            }

            $success = $this->anneeAcademiqueModel->mettreAJourParIdentifiant($idAnneeAcademique, $donnees);
            if (!$success) {
                throw new OperationImpossibleException("Échec de la mise à jour de l'année académique.");
            }
            $this->anneeAcademiqueModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'MODIF_ANNEE_ACADEMIQUE',
                "Année académique '{$idAnneeAcademique}' modifiée."
            );
            return true;
        } catch (\Exception $e) {
            $this->anneeAcademiqueModel->annulerTransaction();
            throw $e;
        }
    }

    public function supprimerAnneeAcademique(string $idAnneeAcademique): bool
    {
        $annee = $this->anneeAcademiqueModel->trouverParIdentifiant($idAnneeAcademique);
        if (!$annee) {
            throw new ElementNonTrouveException("Année académique '{$idAnneeAcademique}' non trouvée.");
        }
        if ($annee['est_active']) {
            throw new OperationImpossibleException("Impossible de supprimer une année académique active.");
        }

        if ($this->inscrireModel->compterParCritere(['id_annee_academique' => $idAnneeAcademique]) > 0) {
            throw new OperationImpossibleException("Impossible de supprimer l'année académique : des inscriptions y sont rattachées.");
        }

        $success = $this->anneeAcademiqueModel->supprimerParIdentifiant($idAnneeAcademique);
        if ($success) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'SUPPRESSION_ANNEE_ACADEMIQUE',
                "Année académique '{$idAnneeAcademique}' supprimée."
            );
        }
        return $success;
    }

    public function recupererAnneeAcademiqueActive(): ?array
    {
        return $this->anneeAcademiqueModel->trouverUnParCritere(['est_active' => 1]);
    }

    public function listerAnneesAcademiques(): array
    {
        return $this->anneeAcademiqueModel->trouverTout();
    }

    public function definirReglesTransitionCohortes(array $regles): bool
    {
        $reglesJson = json_encode($regles);
        return $this->parametreSystemeModel->mettreAJourParIdentifiant('COHORT_TRANSITION_RULES', ['valeur' => $reglesJson]);
    }

    public function verifierEligibiliteEtudiantPourAnnee(string $numeroEtudiant, string $idAnneeAcademique): bool
    {
        $anneeCible = $this->anneeAcademiqueModel->trouverParIdentifiant($idAnneeAcademique);
        if (!$anneeCible) {
            throw new ElementNonTrouveException("Année académique cible non trouvée.");
        }

        $inscriptions = $this->inscrireModel->trouverParCritere(['numero_carte_etudiant' => $numeroEtudiant], ['*'], 'AND', 'id_annee_academique DESC');
        if (empty($inscriptions)) {
            return false;
        }

        $derniereInscription = $inscriptions[0];
        $anneeDerniereInscription = $this->anneeAcademiqueModel->trouverParIdentifiant($derniereInscription['id_annee_academique']);
        if (!$anneeDerniereInscription) {
            return false;
        }

        $reglesParam = $this->parametreSystemeModel->trouverParIdentifiant('COHORT_TRANSITION_RULES');
        $regles = $reglesParam ? json_decode($reglesParam['valeur'], true) : [];

        $niveauEtude = $derniereInscription['id_niveau_etude'];
        $delaiMaxAns = $regles[$niveauEtude] ?? 1;

        $dateFinNominale = new \DateTime($anneeDerniereInscription['date_fin']);
        $dateLimite = $dateFinNominale->modify("+{$delaiMaxAns} year");
        $dateCible = new \DateTime($anneeCible['date_fin']);

        return $dateCible <= $dateLimite;
    }
}