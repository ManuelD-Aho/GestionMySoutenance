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
use App\Backend\Model\Penalite; // Nouveau
use App\Backend\Model\StatutPenaliteRef; // Nouveau
use App\Backend\Service\Notification\ServiceNotification;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGenerator; // Si des IDs sont générés par ce service
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException; // Pour les cas de doublons (ex: double inscription)

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
    private Penalite $penaliteModel; // Nouveau
    private StatutPenaliteRef $statutPenaliteRefModel; // Nouveau

    private ServiceNotification $notificationService;
    private ServiceSupervisionAdmin $supervisionService;
    private IdentifiantGenerator $idGenerator; // Pour générer des IDs si besoin

    public function __construct(
        PDO $db,
        ServiceNotification $notificationService,
        ServiceSupervisionAdmin $supervisionService,
        IdentifiantGenerator $idGenerator
    ) {
        $this->inscrireModel = new Inscrire($db);
        $this->evaluerModel = new Evaluer($db);
        $this->faireStageModel = new FaireStage($db);
        $this->acquerirModel = new Acquerir($db);
        $this->occuperModel = new Occuper($db);
        $this->attribuerModel = new Attribuer($db);
        $this->etudiantModel = new Etudiant($db);
        $this->niveauEtudeModel = new NiveauEtude($db);
        $this->anneeAcademiqueModel = new AnneeAcademique($db);
        $this->statutPaiementRefModel = new StatutPaiementRef($db);
        $this->decisionPassageRefModel = new DecisionPassageRef($db);
        $this->enseignantModel = new Enseignant($db);
        $this->ecueModel = new Ecue($db);
        $this->entrepriseModel = new Entreprise($db);
        $this->gradeModel = new Grade($db);
        $this->fonctionModel = new Fonction($db);
        $this->specialiteModel = new Specialite($db);
        $this->penaliteModel = new Penalite($db); // Initialisation
        $this->statutPenaliteRefModel = new StatutPenaliteRef($db); // Initialisation

        $this->notificationService = $notificationService;
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
    }

    // --- GESTION DES INSCRIPTIONS ---

    /**
     * Crée une nouvelle inscription administrative pour un étudiant.
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param string $idNiveauEtude L'ID du niveau d'étude.
     * @param string $idAnneeAcademique L'ID de l'année académique.
     * @param float $montantInscription Le montant des frais d'inscription.
     * @param string $idStatutPaiement Le statut initial du paiement (ex: 'PAIE_NOK').
     * @param string|null $numeroRecuPaiement Le numéro du reçu de paiement si payé.
     * @return bool Vrai si l'inscription a été créée avec succès.
     * @throws ElementNonTrouveException Si étudiant, niveau, année, ou statut paiement n'existe pas.
     * @throws DoublonException Si l'étudiant est déjà inscrit à ce niveau pour cette année.
     */
    public function creerInscriptionAdministrative(string $numeroCarteEtudiant, string $idNiveauEtude, string $idAnneeAcademique, float $montantInscription, string $idStatutPaiement, ?string $numeroRecuPaiement = null): bool
    {
        $this->inscrireModel->commencerTransaction();
        try {
            // Vérifier l'existence des entités liées
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

            // Vérifier si l'inscription existe déjà pour cette clé composite
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
        } catch (DoublonException $e) {
            $this->inscrireModel->annulerTransaction();
            throw $e;
        } catch (\Exception $e) {
            $this->inscrireModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_CREATION_INSCRIPTION',
                "Erreur création inscription pour {$numeroCarteEtudiant}: " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Met à jour une inscription administrative existante.
     * @param string $numeroCarteEtudiant L'ID de l'étudiant.
     * @param string $idNiveauEtude L'ID du niveau d'étude.
     * @param string $idAnneeAcademique L'ID de l'année académique.
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour réussit.
     * @throws ElementNonTrouveException Si l'inscription n'est pas trouvée.
     * @throws DoublonException Si la mise à jour provoque une violation d'unicité (ex: numéro de reçu).
     */
    public function mettreAJourInscriptionAdministrative(string $numeroCarteEtudiant, string $idNiveauEtude, string $idAnneeAcademique, array $donnees): bool
    {
        $inscription = $this->inscrireModel->trouverParCleComposite($numeroCarteEtudiant, $idNiveauEtude, $idAnneeAcademique);
        if (!$inscription) {
            throw new ElementNonTrouveException("Inscription non trouvée pour la mise à jour.");
        }

        // Mettre à jour la date_paiement si le statut passe à PAIE_OK et qu'elle n'est pas déjà définie
        if (isset($donnees['id_statut_paiement']) && $donnees['id_statut_paiement'] === 'PAIE_OK' && empty($inscription['date_paiement'])) {
            $donnees['date_paiement'] = date('Y-m-d H:i:s');
        }

        $this->inscrireModel->commencerTransaction();
        try {
            $success = $this->inscrireModel->mettreAJourParCleComposite($numeroCarteEtudiant, $idNiveauEtude, $idAnneeAcademique, $donnees);
            if (!$success) {
                throw new OperationImpossibleException("Échec de la mise à jour de l'inscription.");
            }

            $this->inscrireModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'MAJ_INSCRIPTION',
                "Inscription de {$numeroCarteEtudiant} au niveau {$idNiveauEtude} pour l'année {$idAnneeAcademique} mise à jour.",
                $numeroCarteEtudiant,
                'Etudiant'
            );
            return true;
        } catch (DoublonException $e) {
            $this->inscrireModel->annulerTransaction();
            throw $e;
        } catch (\Exception $e) {
            $this->inscrireModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_MAJ_INSCRIPTION',
                "Erreur mise à jour inscription pour {$numeroCarteEtudiant}: " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Liste les inscriptions administratives, avec filtres et pagination.
     * @param array $criteres Critères de filtre.
     * @param int $page Numéro de page.
     * @param int $elementsParPage Nombre d'éléments par page.
     * @return array Liste des inscriptions.
     */
    public function listerInscriptionsAdministratives(array $criteres = [], int $page = 1, int $elementsParPage = 20): array
    {
        $offset = ($page - 1) * $elementsParPage;
        // Pour des données complètes, cette méthode pourrait joindre etudiant, niveau_etude, annee_academique, statut_paiement_ref
        return $this->inscrireModel->trouverParCritere($criteres, ['*'], 'AND', null, $elementsParPage, $offset);
    }

    // --- GESTION DES NOTES ---

    /**
     * Enregistre ou met à jour la note d'un étudiant pour un ECUE.
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param string $idEcue L'ID de l'ECUE.
     * @param float $note La note obtenue.
     * @return bool Vrai si l'opération a réussi.
     * @throws ElementNonTrouveException Si étudiant ou ECUE n'est pas trouvé.
     * @throws OperationImpossibleException Si la note est invalide.
     */
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
            if ($note < 0 || $note > 20) { // Exemple de validation de note
                throw new OperationImpossibleException("La note doit être entre 0 et 20.");
            }

            $existingEvaluation = $this->evaluerModel->trouverEvaluationParCles($numeroCarteEtudiant, $idEcue);

            $data = [
                'date_evaluation' => date('Y-m-d H:i:s'),
                'note' => $note
            ];

            if ($existingEvaluation) {
                $success = $this->evaluerModel->mettreAJourEvaluationParCles($numeroCarteEtudiant, $idEcue, $data);
                $actionType = 'MAJ_NOTE';
                $actionDetails = "Note de {$numeroCarteEtudiant} pour {$idEcue} mise à jour.";
            } else {
                $data['numero_carte_etudiant'] = $numeroCarteEtudiant;
                $data['id_ecue'] = $idEcue;
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
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_ENREG_NOTE',
                "Erreur enregistrement note pour {$numeroCarteEtudiant}: " . $e->getMessage()
            );
            throw $e;
        }
    }

    // --- GESTION DES STAGES ---

    /**
     * Enregistre ou met à jour les informations d'un stage pour un étudiant.
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param string $idEntreprise L'ID de l'entreprise.
     * @param string $dateDebutStage Date de début du stage (YYYY-MM-DD).
     * @param string|null $dateFinStage Date de fin du stage (YYYY-MM-DD).
     * @param string|null $sujetStage Sujet du stage.
     * @param string|null $nomTuteurEntreprise Nom du tuteur en entreprise.
     * @return bool Vrai si l'opération a réussi.
     * @throws ElementNonTrouveException Si l'étudiant ou l'entreprise n'est pas trouvée.
     * @throws OperationImpossibleException Si les dates du stage sont invalides.
     * @throws DoublonException Si le même stage existe déjà pour la même entreprise et étudiant.
     */
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
            // Notifier l'étudiant si le stage est enregistré/validé pour la soumission de rapport
            $this->notificationService->envoyerNotificationUtilisateur(
                $numeroCarteEtudiant,
                'STAGE_ENREGISTRE',
                "Votre stage avec {$idEntreprise} a été enregistré."
            );

            return true;
        } catch (DoublonException $e) {
            $this->faireStageModel->annulerTransaction();
            throw $e;
        } catch (\Exception $e) {
            $this->faireStageModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_ENREG_STAGE',
                "Erreur enregistrement stage pour {$numeroCarteEtudiant}: " . $e->getMessage()
            );
            throw $e;
        }
    }

    // --- GESTION DES PÉNALITÉS ---

    /**
     * Applique une pénalité à un étudiant.
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param float $montantPenalite Le montant de la pénalité.
     * @param string $motif Le motif de la pénalité.
     * @return string L'ID de la pénalité créée.
     * @throws ElementNonTrouveException Si l'étudiant n'est pas trouvé.
     * @throws OperationImpossibleException En cas d'échec de l'application de la pénalité.
     */
    public function appliquerPenalite(string $numeroCarteEtudiant, float $montantPenalite, string $motif): string
    {
        $this->penaliteModel->commencerTransaction();
        try {
            if (!$this->etudiantModel->trouverParIdentifiant($numeroCarteEtudiant)) {
                throw new ElementNonTrouveException("Étudiant non trouvé.");
            }
            // Vérifier que le statut par défaut 'PEN_DUE' existe
            if (!$this->statutPenaliteRefModel->trouverParIdentifiant('PEN_DUE')) {
                throw new OperationImpossibleException("Statut de pénalité 'PEN_DUE' non défini.");
            }

            $idPenalite = $this->idGenerator->genererIdentifiantUnique('PEN'); // PEN-AAAA-SSSS

            $data = [
                'id_penalite' => $idPenalite,
                'numero_carte_etudiant' => $numeroCarteEtudiant,
                'id_statut_penalite' => 'PEN_DUE', // Pénalité due par défaut
                'montant_penalite' => $montantPenalite,
                'date_application' => date('Y-m-d'),
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
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_APPLICATION_PENALITE',
                "Erreur application pénalité pour {$numeroCarteEtudiant}: " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Régularise une pénalité pour un étudiant.
     * @param string $idPenalite L'ID de la pénalité à régulariser.
     * @param string $numeroPersonnelAdministratif Le numéro du personnel qui régularise.
     * @return bool Vrai si la pénalité a été régularisée.
     * @throws ElementNonTrouveException Si la pénalité ou le personnel n'est pas trouvé.
     * @throws OperationImpossibleException Si la pénalité est déjà réglée ou non due.
     */
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
                'id_statut_penalite' => 'PEN_REGLEE', // Statut réglé
                'date_regularisation' => date('Y-m-d'),
                // Ajouter le numéro du personnel traitant si besoin dans la table penalite
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
            $this->supervisionService->enregistrerAction(
                $numeroPersonnelAdministratif,
                'ECHEC_REGULARISATION_PENALITE',
                "Erreur régularisation pénalité {$idPenalite}: " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Vérifie si un étudiant est éligible à la soumission d'un rapport (inscription, stage validé, pénalités régularisées).
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param string $idAnneeAcademique L'ID de l'année académique actuelle.
     * @return bool Vrai si l'étudiant est éligible, faux sinon.
     */
    public function estEtudiantEligibleSoumission(string $numeroCarteEtudiant, string $idAnneeAcademique): bool
    {
        $etudiant = $this->etudiantModel->trouverParIdentifiant($numeroCarteEtudiant);
        if (!$etudiant) {
            return false; // Étudiant non trouvé
        }

        // 1. Vérifier l'inscription pour l'année académique en cours
        $inscription = $this->inscrireModel->trouverParCleComposite($numeroCarteEtudiant, $etudiant['id_niveau_etude'] ?? '', $idAnneeAcademique);
        // Assurez-vous que l'étudiant a un champ 'id_niveau_etude' ou qu'il est récupéré via une autre méthode d'inscription
        if (!$inscription || $inscription['id_statut_paiement'] !== 'PAIE_OK') {
            return false; // Non inscrit ou paiement non réglé
        }

        // 2. Vérifier la validation du stage (présence d'un stage et son statut si géré)
        $stages = $this->faireStageModel->trouverParCritere(['numero_carte_etudiant' => $numeroCarteEtudiant]);
        // On suppose qu'il doit y avoir au moins un stage enregistré et 'validé' si un champ statut_stage existait
        if (empty($stages)) {
            return false; // Aucun stage enregistré
        }
        // Si la table faire_stage avait un statut de validation, il faudrait le vérifier ici.
        // Ex: $stageValide = array_filter($stages, fn($s) => $s['statut_stage'] === 'VALIDE'); if(empty($stageValide)) return false;


        // 3. Vérifier les pénalités non régularisées
        $penalitesNonRegul = $this->penaliteModel->trouverPenalitesNonRegul($numeroCarteEtudiant);
        if (!empty($penalitesNonRegul)) {
            return false; // Pénalités non régularisées
        }

        return true; // Tous les critères sont remplis
    }


    // --- GESTION DES CARRIÈRES ENSEIGNANTS (GRADES, FONCTIONS, SPÉCIALITÉS) ---

    /**
     * Lie un grade à un enseignant (historise l'acquisition d'un grade).
     * @param string $idGrade L'ID du grade.
     * @param string $numeroEnseignant Le numéro de l'enseignant.
     * @param string $dateAcquisition Date d'acquisition (YYYY-MM-DD).
     * @return bool Vrai si l'opération a réussi.
     * @throws ElementNonTrouveException Si grade ou enseignant non trouvé.
     * @throws DoublonException Si l'acquisition existe déjà.
     */
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
            // Vérifier si cette acquisition de grade existe déjà pour cette date ou si déjà un grade supérieur (logique métier)
            if ($this->acquerirModel->trouverAcquisitionParCles($idGrade, $numeroEnseignant)) {
                throw new DoublonException("L'enseignant a déjà acquis ce grade à cette date.");
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
                "Grade '{$idGrade}' lié à l'enseignant {$numeroEnseignant}",
                $numeroEnseignant,
                'Enseignant'
            );
            return true;
        } catch (\Exception $e) {
            $this->acquerirModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_LIER_GRADE_ENSEIGNANT',
                "Erreur liaison grade à enseignant {$numeroEnseignant}: " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Lie une fonction à un enseignant (historise l'occupation d'une fonction).
     * @param string $idFonction L'ID de la fonction.
     * @param string $numeroEnseignant Le numéro de l'enseignant.
     * @param string $dateDebutOccupation Date de début de l'occupation (YYYY-MM-DD).
     * @param string|null $dateFinOccupation Date de fin de l'occupation (YYYY-MM-DD).
     * @return bool Vrai si l'opération a réussi.
     * @throws ElementNonTrouveException Si fonction ou enseignant non trouvé.
     * @throws DoublonException Si l'occupation existe déjà.
     */
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
            // Vérifier si cette occupation existe déjà
            if ($this->occuperModel->trouverOccupationParCles($idFonction, $numeroEnseignant)) { // Peut-être vérifier par date aussi si plusieurs fois la même fonction.
                throw new DoublonException("L'enseignant occupe déjà cette fonction.");
            }

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
                "Fonction '{$idFonction}' liée à l'enseignant {$numeroEnseignant}",
                $numeroEnseignant,
                'Enseignant'
            );
            return true;
        } catch (\Exception $e) {
            $this->occuperModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_LIER_FONCTION_ENSEIGNANT',
                "Erreur liaison fonction à enseignant {$numeroEnseignant}: " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Lie une spécialité à un enseignant.
     * @param string $idSpecialite L'ID de la spécialité.
     * @param string $numeroEnseignant Le numéro de l'enseignant.
     * @return bool Vrai si l'opération a réussi.
     * @throws ElementNonTrouveException Si spécialité ou enseignant non trouvé.
     * @throws DoublonException Si l'enseignant a déjà cette spécialité.
     */
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
            // Vérifier si cette spécialité est déjà attribuée à l'enseignant
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
                "Spécialité '{$idSpecialite}' liée à l'enseignant {$numeroEnseignant}",
                $numeroEnseignant,
                'Enseignant'
            );
            return true;
        } catch (\Exception $e) {
            $this->attribuerModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_LIER_SPECIALITE_ENSEIGNANT',
                "Erreur liaison spécialité à enseignant {$numeroEnseignant}: " . $e->getMessage()
            );
            throw $e;
        }
    }
}