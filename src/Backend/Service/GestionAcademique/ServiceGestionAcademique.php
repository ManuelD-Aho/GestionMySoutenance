<?php

namespace App\Backend\Service\GestionAcademique;

use PDO;
use App\Backend\Model\Inscrire;
use App\Backend\Model\Evaluer;
use App\Backend\Model\FaireStage;
use App\Backend\Model\Acquerir;
use App\Backend\Model\Occuper;
use App\Backend\Model\Attribuer;
use App\Backend\Model\Etudiant;
use App\Backend\Model\NiveauEtude;
use App\Backend\Model\AnneeAcademique;
use App\Backend\Model\StatutPaiementRef;
use App\Backend\Model\DecisionPassageRef;
use App\Backend\Model\Enseignant;
use App\Backend\Model\Ecue;
use App\Backend\Model\Entreprise;
use App\Backend\Model\Grade;
use App\Backend\Model\Fonction;
use App\Backend\Model\Specialite;
use App\Backend\Model\Penalite;
use App\Backend\Model\StatutPenaliteRef;
use App\Backend\Model\PersonnelAdministratif;
use App\Backend\Service\Notification\ServiceNotificationInterface;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGeneratorInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;

class ServiceGestionAcademique implements ServiceGestionAcademiqueInterface
{
    private Inscrire $inscrireModel;
    private Evaluer $evaluerModel;
    private FaireStage $faireStageModel;
    private Acquerir $acquerirModel;
    private Occuper $occuperModel;
    private Attribuer $attribuerModel;
    private Etudiant $etudiantModel;
    private NiveauEtude $niveauEtudeModel;
    private AnneeAcademique $anneeAcademiqueModel;
    private StatutPaiementRef $statutPaiementRefModel;
    private DecisionPassageRef $decisionPassageRefModel;
    private Enseignant $enseignantModel;
    private Ecue $ecueModel;
    private Entreprise $entrepriseModel;
    private Grade $gradeModel;
    private Fonction $fonctionModel;
    private Specialite $specialiteModel;
    private Penalite $penaliteModel;
    private StatutPenaliteRef $statutPenaliteRefModel;
    private PersonnelAdministratif $personnelAdministratifModel;
    private ServiceNotificationInterface $notificationService;
    private ServiceSupervisionAdminInterface $supervisionService;
    private IdentifiantGeneratorInterface $idGenerator;

    public function __construct(
        PDO $db,
        Inscrire $inscrireModel,
        Evaluer $evaluerModel,
        FaireStage $faireStageModel,
        Acquerir $acquerirModel,
        Occuper $occuperModel,
        Attribuer $attribuerModel,
        Etudiant $etudiantModel,
        NiveauEtude $niveauEtudeModel,
        AnneeAcademique $anneeAcademiqueModel,
        StatutPaiementRef $statutPaiementRefModel,
        DecisionPassageRef $decisionPassageRefModel,
        Enseignant $enseignantModel,
        Ecue $ecueModel,
        Entreprise $entrepriseModel,
        Grade $gradeModel,
        Fonction $fonctionModel,
        Specialite $specialiteModel,
        Penalite $penaliteModel,
        StatutPenaliteRef $statutPenaliteRefModel,
        PersonnelAdministratif $personnelAdministratifModel,
        ServiceNotificationInterface $notificationService,
        ServiceSupervisionAdminInterface $supervisionService,
        IdentifiantGeneratorInterface $idGenerator
    ) {
        $this->inscrireModel = $inscrireModel;
        $this->evaluerModel = $evaluerModel;
        $this->faireStageModel = $faireStageModel;
        $this->acquerirModel = $acquerirModel;
        $this->occuperModel = $occuperModel;
        $this->attribuerModel = $attribuerModel;
        $this->etudiantModel = $etudiantModel;
        $this->niveauEtudeModel = $niveauEtudeModel;
        $this->anneeAcademiqueModel = $anneeAcademiqueModel;
        $this->statutPaiementRefModel = $statutPaiementRefModel;
        $this->decisionPassageRefModel = $decisionPassageRefModel;
        $this->enseignantModel = $enseignantModel;
        $this->ecueModel = $ecueModel;
        $this->entrepriseModel = $entrepriseModel;
        $this->gradeModel = $gradeModel;
        $this->fonctionModel = $fonctionModel;
        $this->specialiteModel = $specialiteModel;
        $this->penaliteModel = $penaliteModel;
        $this->statutPenaliteRefModel = $statutPenaliteRefModel;
        $this->personnelAdministratifModel = $personnelAdministratifModel;
        $this->notificationService = $notificationService;
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
    }

    public function creerInscriptionAdministrative(string $numeroCarteEtudiant, string $idNiveauEtude, string $idAnneeAcademique, float $montantInscription, string $idStatutPaiement, ?string $numeroRecuPaiement = null): bool
    {
        $this->inscrireModel->commencerTransaction();
        try {
            if (!$this->etudiantModel->trouverParIdentifiant($numeroCarteEtudiant)) {
                throw new ElementNonTrouveException("Étudiant non trouvé.");
            }
            if (!$this->niveauEtudeModel->trouverParIdentifiant($idNiveauEtude)) {
                throw new ElementNonTrouveException("Niveau d'étude non trouvé.");
            }
            if (!$this->anneeAcademiqueModel->trouverParIdentifiant($idAnneeAcademique)) {
                throw new ElementNonTrouveException("Année académique non trouvée.");
            }
            if (!$this->statutPaiementRefModel->trouverParIdentifiant($idStatutPaiement)) {
                throw new ElementNonTrouveException("Statut de paiement non reconnu.");
            }

            if ($this->inscrireModel->trouverParCleComposite($numeroCarteEtudiant, $idNiveauEtude, $idAnneeAcademique)) {
                throw new DoublonException("L'étudiant est déjà inscrit à ce niveau pour cette année académique.");
            }

            $data = [
                'numero_carte_etudiant' => $numeroCarteEtudiant,
                'id_niveau_etude' => $idNiveauEtude,
                'id_annee_academique' => $idAnneeAcademique,
                'montant_inscription' => $montantInscription,
                'date_inscription' => date('Y-m-d H:i:s'),
                'id_statut_paiement' => $idStatutPaiement,
                'date_paiement' => ($idStatutPaiement === 'PAIE_OK') ? date('Y-m-d H:i:s') : null,
                'numero_recu_paiement' => $numeroRecuPaiement,
            ];

            $success = $this->inscrireModel->creer($data);
            if (!$success) {
                throw new OperationImpossibleException("Échec de la création de l'inscription.");
            }

            $this->inscrireModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'CREATION_INSCRIPTION',
                "Inscription de {$numeroCarteEtudiant} au niveau {$idNiveauEtude} pour l'année {$idAnneeAcademique} créée.",
                $numeroCarteEtudiant,
                'Etudiant'
            );
            return true;
        } catch (\Exception $e) {
            $this->inscrireModel->annulerTransaction();
            throw $e;
        }
    }

    public function mettreAJourInscriptionAdministrative(string $numeroCarteEtudiant, string $idNiveauEtude, string $idAnneeAcademique, array $donnees): bool
    {
        $inscription = $this->inscrireModel->trouverParCleComposite($numeroCarteEtudiant, $idNiveauEtude, $idAnneeAcademique);
        if (!$inscription) {
            throw new ElementNonTrouveException("Inscription non trouvée pour la mise à jour.");
        }

        if (isset($donnees['id_statut_paiement']) && $donnees['id_statut_paiement'] === 'PAIE_OK' && empty($inscription['date_paiement'])) {
            $donnees['date_paiement'] = date('Y-m-d H:i:s');
        }

        $success = $this->inscrireModel->mettreAJourParCleComposite($numeroCarteEtudiant, $idNiveauEtude, $idAnneeAcademique, $donnees);
        if ($success) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'MAJ_INSCRIPTION',
                "Inscription de {$numeroCarteEtudiant} au niveau {$idNiveauEtude} pour l'année {$idAnneeAcademique} mise à jour.",
                $numeroCarteEtudiant,
                'Etudiant'
            );
        }
        return $success;
    }

    public function supprimerInscriptionAdministrative(string $numeroCarteEtudiant, string $idNiveauEtude, string $idAnneeAcademique): bool
    {
        $success = $this->inscrireModel->supprimerParCleComposite($numeroCarteEtudiant, $idNiveauEtude, $idAnneeAcademique);
        if ($success) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'SUPPRESSION_INSCRIPTION',
                "Inscription de {$numeroCarteEtudiant} au niveau {$idNiveauEtude} pour l'année {$idAnneeAcademique} supprimée.",
                $numeroCarteEtudiant,
                'Etudiant'
            );
        }
        return $success;
    }

    public function listerInscriptionsAdministratives(array $criteres = [], int $page = 1, int $elementsParPage = 20): array
    {
        $offset = ($page - 1) * $elementsParPage;
        return $this->inscrireModel->trouverParCritere($criteres, ['*'], 'AND', null, $elementsParPage, $offset);
    }

    public function enregistrerDecisionPassage(string $numeroCarteEtudiant, string $idAnneeAcademique, string $idDecisionPassage): bool
    {
        $inscription = $this->inscrireModel->trouverUnParCritere(['numero_carte_etudiant' => $numeroCarteEtudiant, 'id_annee_academique' => $idAnneeAcademique]);
        if (!$inscription) {
            throw new ElementNonTrouveException("Aucune inscription trouvée pour cet étudiant pour cette année.");
        }
        return $this->mettreAJourInscriptionAdministrative($numeroCarteEtudiant, $inscription['id_niveau_etude'], $idAnneeAcademique, ['id_decision_passage' => $idDecisionPassage]);
    }

    public function enregistrerNoteEcue(string $numeroCarteEtudiant, string $idEcue, float $note): bool
    {
        $this->evaluerModel->commencerTransaction();
        try {
            if (!$this->etudiantModel->trouverParIdentifiant($numeroCarteEtudiant)) {
                throw new ElementNonTrouveException("Étudiant non trouvé.");
            }
            if (!$this->ecueModel->trouverParIdentifiant($idEcue)) {
                throw new ElementNonTrouveException("ECUE non trouvé.");
            }
            if ($note < 0 || $note > 20) {
                throw new OperationImpossibleException("La note doit être entre 0 et 20.");
            }

            $anneeActive = $this->anneeAcademiqueModel->trouverUnParCritere(['est_active' => 1]);
            if (!$anneeActive) {
                throw new OperationImpossibleException("Aucune année académique active trouvée.");
            }
            $idAnneeAcademique = $anneeActive['id_annee_academique'];

            $existingEvaluation = $this->evaluerModel->trouverEvaluationParCles($numeroCarteEtudiant, $idEcue, $idAnneeAcademique);

            $data = ['note' => $note];

            if ($existingEvaluation) {
                $success = $this->evaluerModel->mettreAJourEvaluationParCles($numeroCarteEtudiant, $idEcue, $idAnneeAcademique, $data);
                $actionType = 'MAJ_NOTE';
                $actionDetails = "Note de {$numeroCarteEtudiant} pour {$idEcue} mise à jour.";
            } else {
                $data['numero_carte_etudiant'] = $numeroCarteEtudiant;
                $data['id_ecue'] = $idEcue;
                $data['id_annee_academique'] = $idAnneeAcademique;
                $data['date_evaluation'] = date('Y-m-d H:i:s');
                $success = $this->evaluerModel->creer($data);
                $actionType = 'CREATION_NOTE';
                $actionDetails = "Note de {$numeroCarteEtudiant} pour {$idEcue} créée.";
            }

            if (!$success) {
                throw new OperationImpossibleException("Échec de l'enregistrement de la note.");
            }

            $this->evaluerModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                $actionType,
                $actionDetails,
                $numeroCarteEtudiant,
                'Etudiant'
            );
            return true;
        } catch (\Exception $e) {
            $this->evaluerModel->annulerTransaction();
            throw $e;
        }
    }

    public function supprimerNoteEcue(string $numeroCarteEtudiant, string $idEcue): bool
    {
        $anneeActive = $this->anneeAcademiqueModel->trouverUnParCritere(['est_active' => 1]);
        if (!$anneeActive) {
            throw new OperationImpossibleException("Aucune année académique active trouvée.");
        }
        $idAnneeAcademique = $anneeActive['id_annee_academique'];

        $success = $this->evaluerModel->supprimerEvaluationParCles($numeroCarteEtudiant, $idEcue, $idAnneeAcademique);
        if ($success) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'SUPPRESSION_NOTE',
                "Note de {$numeroCarteEtudiant} pour {$idEcue} supprimée.",
                $numeroCarteEtudiant,
                'Etudiant'
            );
        }
        return $success;
    }

    public function listerNotes(array $criteres = [], int $page = 1, int $elementsParPage = 20): array
    {
        $offset = ($page - 1) * $elementsParPage;
        return $this->evaluerModel->trouverParCritere($criteres, ['*'], 'AND', null, $elementsParPage, $offset);
    }

    public function enregistrerInformationsStage(string $numeroCarteEtudiant, string $idEntreprise, string $dateDebutStage, ?string $dateFinStage = null, ?string $sujetStage = null, ?string $nomTuteurEntreprise = null): bool
    {
        $this->faireStageModel->commencerTransaction();
        try {
            if (!$this->etudiantModel->trouverParIdentifiant($numeroCarteEtudiant)) {
                throw new ElementNonTrouveException("Étudiant non trouvé.");
            }
            if (!$this->entrepriseModel->trouverParIdentifiant($idEntreprise)) {
                throw new ElementNonTrouveException("Entreprise non trouvée.");
            }
            if ($dateFinStage && $dateDebutStage > $dateFinStage) {
                throw new OperationImpossibleException("La date de fin de stage ne peut pas être antérieure à la date de début.");
            }

            $existingStage = $this->faireStageModel->trouverStageParCles($idEntreprise, $numeroCarteEtudiant);

            $data = [
                'date_debut_stage' => $dateDebutStage,
                'date_fin_stage' => $dateFinStage,
                'sujet_stage' => $sujetStage,
                'nom_tuteur_entreprise' => $nomTuteurEntreprise
            ];

            if ($existingStage) {
                $success = $this->faireStageModel->mettreAJourStageParCles($idEntreprise, $numeroCarteEtudiant, $data);
                $actionType = 'MAJ_STAGE';
                $actionDetails = "Informations de stage de {$numeroCarteEtudiant} pour {$idEntreprise} mises à jour.";
            } else {
                $data['numero_carte_etudiant'] = $numeroCarteEtudiant;
                $data['id_entreprise'] = $idEntreprise;
                $success = $this->faireStageModel->creer($data);
                $actionType = 'CREATION_STAGE';
                $actionDetails = "Informations de stage de {$numeroCarteEtudiant} pour {$idEntreprise} créées.";
            }

            if (!$success) {
                throw new OperationImpossibleException("Échec de l'enregistrement des informations de stage.");
            }

            $this->faireStageModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                $actionType,
                $actionDetails,
                $numeroCarteEtudiant,
                'Etudiant'
            );
            return true;
        } catch (\Exception $e) {
            $this->faireStageModel->annulerTransaction();
            throw $e;
        }
    }

    public function validerStage(string $idEntreprise, string $numeroCarteEtudiant, string $numeroPersonnelValidateur): bool
    {
        $stage = $this->faireStageModel->trouverStageParCles($idEntreprise, $numeroCarteEtudiant);
        if (!$stage) {
            throw new ElementNonTrouveException("Stage non trouvé.");
        }
        if (!$this->personnelAdministratifModel->trouverParIdentifiant($numeroPersonnelValidateur)) {
            throw new ElementNonTrouveException("Personnel validateur non trouvé.");
        }

        $success = $this->faireStageModel->mettreAJourStageParCles($idEntreprise, $numeroCarteEtudiant, ['statut_stage' => 'VALIDE']);
        if ($success) {
            $this->supervisionService->enregistrerAction(
                $numeroPersonnelValidateur,
                'VALIDATION_STAGE',
                "Stage de {$numeroCarteEtudiant} validé.",
                $numeroCarteEtudiant,
                'Etudiant'
            );
        }
        return $success;
    }

    public function appliquerPenalite(string $numeroCarteEtudiant, float $montantPenalite, string $motif): string
    {
        $this->penaliteModel->commencerTransaction();
        try {
            if (!$this->etudiantModel->trouverParIdentifiant($numeroCarteEtudiant)) {
                throw new ElementNonTrouveException("Étudiant non trouvé.");
            }
            if (!$this->statutPenaliteRefModel->trouverParIdentifiant('PEN_DUE')) {
                throw new OperationImpossibleException("Statut de pénalité 'PEN_DUE' non défini.");
            }

            $idPenalite = $this->idGenerator->genererIdentifiantUnique('PEN');

            $data = [
                'id_penalite' => $idPenalite,
                'numero_carte_etudiant' => $numeroCarteEtudiant,
                'id_statut_penalite' => 'PEN_DUE',
                'montant_du' => $montantPenalite,
                'motif' => $motif
            ];

            if (!$this->penaliteModel->creer($data)) {
                throw new OperationImpossibleException("Échec de l'application de la pénalité.");
            }

            $this->penaliteModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'APPLICATION_PENALITE',
                "Pénalité de {$montantPenalite} appliquée à {$numeroCarteEtudiant} pour motif: '{$motif}'",
                $idPenalite,
                'Penalite'
            );
            $this->notificationService->envoyerNotificationUtilisateur(
                $numeroCarteEtudiant,
                'PENALITE_APPLIQUEE',
                "Une pénalité de {$montantPenalite} a été appliquée à votre dossier. Motif: {$motif}"
            );
            return $idPenalite;
        } catch (\Exception $e) {
            $this->penaliteModel->annulerTransaction();
            throw $e;
        }
    }

    public function regulariserPenalite(string $idPenalite, string $numeroPersonnelAdministratif): bool
    {
        $this->penaliteModel->commencerTransaction();
        try {
            $penalite = $this->penaliteModel->trouverParIdentifiant($idPenalite);
            if (!$penalite) {
                throw new ElementNonTrouveException("Pénalité non trouvée.");
            }
            if ($penalite['id_statut_penalite'] !== 'PEN_DUE') {
                throw new OperationImpossibleException("Cette pénalité n'est pas due ou a déjà été régularisée.");
            }
            if (!$this->personnelAdministratifModel->trouverParIdentifiant($numeroPersonnelAdministratif)) {
                throw new ElementNonTrouveException("Personnel administratif non trouvé.");
            }

            $success = $this->penaliteModel->mettreAJourParIdentifiant($idPenalite, [
                'id_statut_penalite' => 'PEN_REGLEE',
                'date_regularisation' => date('Y-m-d H:i:s'),
                'numero_personnel_traitant' => $numeroPersonnelAdministratif
            ]);

            if (!$success) {
                throw new OperationImpossibleException("Échec de la régularisation de la pénalité.");
            }

            $this->penaliteModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $numeroPersonnelAdministratif,
                'REGULARISATION_PENALITE',
                "Pénalité '{$idPenalite}' de {$penalite['numero_carte_etudiant']} régularisée.",
                $idPenalite,
                'Penalite'
            );
            $this->notificationService->envoyerNotificationUtilisateur(
                $penalite['numero_carte_etudiant'],
                'PENALITE_REGULARISEE',
                "Votre pénalité pour motif '{$penalite['motif']}' a été régularisée."
            );
            return true;
        } catch (\Exception $e) {
            $this->penaliteModel->annulerTransaction();
            throw $e;
        }
    }

    public function listerPenalites(array $criteres = [], int $page = 1, int $elementsParPage = 20): array
    {
        $offset = ($page - 1) * $elementsParPage;
        return $this->penaliteModel->trouverParCritere($criteres, ['*'], 'AND', null, $elementsParPage, $offset);
    }

    public function listerPenalitesEtudiant(string $numeroCarteEtudiant): array
    {
        return $this->listerPenalites(['numero_carte_etudiant' => $numeroCarteEtudiant]);
    }

    public function detecterEtAppliquerPenalitesAutomatiquement(): int
    {
        $etudiantsEnRetard = $this->etudiantModel->trouverParCritere([]); // Logique de détection à affiner
        $count = 0;
        foreach ($etudiantsEnRetard as $etudiant) {
            try {
                $this->appliquerPenalite($etudiant['numero_carte_etudiant'], 100.00, "Retard de soumission automatique");
                $count++;
            } catch (\Exception $e) {
                error_log("Échec de l'application de pénalité automatique pour {$etudiant['numero_carte_etudiant']}: " . $e->getMessage());
            }
        }
        return $count;
    }

    public function estEtudiantEligibleSoumission(string $numeroCarteEtudiant, string $idAnneeAcademique): bool
    {
        $inscription = $this->inscrireModel->trouverUnParCritere(['numero_carte_etudiant' => $numeroCarteEtudiant, 'id_annee_academique' => $idAnneeAcademique]);
        if (!$inscription || $inscription['id_statut_paiement'] !== 'PAIE_OK') {
            return false;
        }

        $stage = $this->faireStageModel->trouverUnParCritere(['numero_carte_etudiant' => $numeroCarteEtudiant, 'statut_stage' => 'VALIDE']);
        if (!$stage) {
            return false;
        }

        $penalitesNonRegul = $this->penaliteModel->trouverPenalitesNonRegul($numeroCarteEtudiant);
        if (!empty($penalitesNonRegul)) {
            return false;
        }

        return true;
    }

    public function lierGradeAEnseignant(string $idGrade, string $numeroEnseignant, string $dateAcquisition): bool
    {
        $this->acquerirModel->commencerTransaction();
        try {
            if (!$this->gradeModel->trouverParIdentifiant($idGrade)) {
                throw new ElementNonTrouveException("Grade non trouvé.");
            }
            if (!$this->enseignantModel->trouverParIdentifiant($numeroEnseignant)) {
                throw new ElementNonTrouveException("Enseignant non trouvé.");
            }
            if ($this->acquerirModel->trouverAcquisitionParCles($idGrade, $numeroEnseignant)) {
                throw new DoublonException("L'enseignant a déjà acquis ce grade.");
            }

            $success = $this->acquerirModel->creer([
                'id_grade' => $idGrade,
                'numero_enseignant' => $numeroEnseignant,
                'date_acquisition' => $dateAcquisition
            ]);
            if (!$success) {
                throw new OperationImpossibleException("Échec de la liaison du grade à l'enseignant.");
            }
            $this->acquerirModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'LIER_GRADE_ENSEIGNANT',
                "Grade '{$idGrade}' lié à l'enseignant {$numeroEnseignant}"
            );
            return true;
        } catch (\Exception $e) {
            $this->acquerirModel->annulerTransaction();
            throw $e;
        }
    }

    public function lierFonctionAEnseignant(string $idFonction, string $numeroEnseignant, string $dateDebutOccupation, ?string $dateFinOccupation = null): bool
    {
        $this->occuperModel->commencerTransaction();
        try {
            if (!$this->fonctionModel->trouverParIdentifiant($idFonction)) {
                throw new ElementNonTrouveException("Fonction non trouvée.");
            }
            if (!$this->enseignantModel->trouverParIdentifiant($numeroEnseignant)) {
                throw new ElementNonTrouveException("Enseignant non trouvé.");
            }

            $this->occuperModel->mettreAJourParCritere(
                ['numero_enseignant' => $numeroEnseignant, 'date_fin_occupation' => null],
                ['date_fin_occupation' => date('Y-m-d')]
            );

            $success = $this->occuperModel->creer([
                'id_fonction' => $idFonction,
                'numero_enseignant' => $numeroEnseignant,
                'date_debut_occupation' => $dateDebutOccupation,
                'date_fin_occupation' => $dateFinOccupation
            ]);
            if (!$success) {
                throw new OperationImpossibleException("Échec de la liaison de la fonction à l'enseignant.");
            }
            $this->occuperModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'LIER_FONCTION_ENSEIGNANT',
                "Fonction '{$idFonction}' liée à l'enseignant {$numeroEnseignant}"
            );
            return true;
        } catch (\Exception $e) {
            $this->occuperModel->annulerTransaction();
            throw $e;
        }
    }

    public function lierSpecialiteAEnseignant(string $idSpecialite, string $numeroEnseignant): bool
    {
        $this->attribuerModel->commencerTransaction();
        try {
            if (!$this->specialiteModel->trouverParIdentifiant($idSpecialite)) {
                throw new ElementNonTrouveException("Spécialité non trouvée.");
            }
            if (!$this->enseignantModel->trouverParIdentifiant($numeroEnseignant)) {
                throw new ElementNonTrouveException("Enseignant non trouvé.");
            }
            if ($this->attribuerModel->trouverAttributionParCles($numeroEnseignant, $idSpecialite)) {
                throw new DoublonException("L'enseignant a déjà cette spécialité.");
            }

            $success = $this->attribuerModel->creer([
                'id_specialite' => $idSpecialite,
                'numero_enseignant' => $numeroEnseignant
            ]);
            if (!$success) {
                throw new OperationImpossibleException("Échec de la liaison de la spécialité à l'enseignant.");
            }
            $this->attribuerModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'LIER_SPECIALITE_ENSEIGNANT',
                "Spécialité '{$idSpecialite}' liée à l'enseignant {$numeroEnseignant}"
            );
            return true;
        } catch (\Exception $e) {
            $this->attribuerModel->annulerTransaction();
            throw $e;
        }
    }
}