<?php
namespace App\Backend\Service\Rapport;

use PDO;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\StatutRapportRef;
use App\Backend\Model\TypeDocumentRef;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\SectionRapport; // Nouveau modèle
use App\Backend\Model\DocumentGenere; // Nouveau modèle
use App\Backend\Model\Approuver; // Pour la conformité
use App\Backend\Model\StatutConformiteRef;
use App\Backend\Model\VoteCommission;
use App\Backend\Model\DecisionVoteRef;
use App\Backend\Model\CompteRendu;// Pour les votes de la commission
use App\Backend\Model\PersonnelAdministratif;
use App\Backend\Service\Notification\ServiceNotification;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGenerator;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;

class ServiceRapport implements ServiceRapportInterface
{
    private RapportEtudiant $rapportEtudiantModel;
    private StatutRapportRef $statutRapportRefModel;
    private TypeDocumentRef $typeDocumentRefModel;
    private Utilisateur $utilisateurModel; // Pour vérifier l'existence de l'utilisateur qui soumet
    private SectionRapport $sectionRapportModel; // Nouveau modèle
    private DocumentGenere $documentGenereModel; // Nouveau modèle (remplace DocumentSoumis)
    private Approuver $approuverModel; // Pour la conformité
    private StatutConformiteRef $statutConformiteRefModel; // Pour la conformité
    private VoteCommission $voteCommissionModel; // Pour les votes de la commission
    private DecisionVoteRef $decisionVoteRefModel; // Pour les décisions de vote
    private CompteRendu $compteRenduModel; // Pour les PV de validation
    private PersonnelAdministratif $personnelAdministratifModel; // Pour les actions de supervision
    private ServiceNotification $notificationService;
    private ServiceSupervisionAdmin $supervisionService;
    private IdentifiantGenerator $idGenerator;

    public function __construct(
        PDO $db,
        ServiceNotification $notificationService,
        ServiceSupervisionAdmin $supervisionService,
        IdentifiantGenerator $idGenerator
    ) {
        $this->rapportEtudiantModel = new RapportEtudiant($db);
        $this->statutRapportRefModel = new StatutRapportRef($db);
        $this->typeDocumentRefModel = new TypeDocumentRef($db);
        $this->utilisateurModel = new Utilisateur($db);
        $this->sectionRapportModel = new SectionRapport($db); // Initialisation
        $this->documentGenereModel = new DocumentGenere($db); // Initialisation
        $this->approuverModel = new Approuver($db); // Pour la conformité
        $this->statutConformiteRefModel = new StatutConformiteRef($db); // Pour la conformité
        $this->voteCommissionModel = new VoteCommission($db); // Pour les votes de la commission
        $this->decisionVoteRefModel = new DecisionVoteRef($db); // Pour les décisions de vote
        $this->compteRenduModel = new CompteRendu($db); // Pour les PV de validation
        $this->personnelAdministratifModel = new PersonnelAdministratif($db); // Pour les actions de supervision

        $this->notificationService = $notificationService;
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
    }

    /**
     * Crée ou met à jour un brouillon de rapport étudiant.
     * Si le rapport existe déjà en brouillon, il est mis à jour. Sinon, un nouveau rapport brouillon est créé.
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param array $metadonnees Metadonnées du rapport (libelle_rapport_etudiant, theme, resume, nombre_pages, numero_attestation_stage).
     * @param array $sectionsContenu Tableau associatif des sections du rapport (ex: ['Introduction' => 'Contenu intro', 'Conclusion' => '...']).
     * @return string L'ID du rapport créé ou mis à jour.
     * @throws OperationImpossibleException En cas d'échec de la création ou mise à jour.
     * @throws ElementNonTrouveException Si l'étudiant n'est pas trouvé.
     */
    public function creerOuMettreAJourBrouillonRapport(string $numeroCarteEtudiant, array $metadonnees, array $sectionsContenu): string
    {
        if (!$this->utilisateurModel->trouverParIdentifiant($numeroCarteEtudiant)) {
            throw new ElementNonTrouveException("Étudiant non trouvé.");
        }

        $this->rapportEtudiantModel->commencerTransaction();
        try {
            // Tenter de trouver un brouillon existant pour cet étudiant
            // S'assurer qu'un étudiant n'a qu'un brouillon actif à la fois
            $existingRapport = $this->rapportEtudiantModel->trouverUnParCritere([
                'numero_carte_etudiant' => $numeroCarteEtudiant,
                'id_statut_rapport' => 'RAP_BROUILLON' // Statut BROUILLON
            ]);

            $idRapport = null;
            $actionType = 'CREATION_BROUILLON_RAPPORT';
            $actionDetails = "Nouveau brouillon de rapport créé pour {$numeroCarteEtudiant}.";

            if ($existingRapport) {
                $idRapport = $existingRapport['id_rapport_etudiant'];
                $metadonnees['date_derniere_modif'] = date('Y-m-d H:i:s');
                if (!$this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapport, $metadonnees)) {
                    throw new OperationImpossibleException("Échec de la mise à jour du rapport brouillon existant.");
                }
                $actionType = 'MAJ_BROUILLON_RAPPORT';
                $actionDetails = "Brouillon de rapport {$idRapport} de {$numeroCarteEtudiant} mis à jour.";
            } else {
                $idRapport = $this->idGenerator->genererIdentifiantUnique('RAP'); // RAP-AAAA-SSSS
                $metadonnees['id_rapport_etudiant'] = $idRapport;
                $metadonnees['numero_carte_etudiant'] = $numeroCarteEtudiant;
                $metadonnees['id_statut_rapport'] = 'RAP_BROUILLON'; // Statut initial
                $metadonnees['date_soumission'] = null; // Pas encore soumis
                $metadonnees['date_derniere_modif'] = date('Y-m-d H:i:s');

                if (!$this->rapportEtudiantModel->creer($metadonnees)) {
                    throw new OperationImpossibleException("Échec de la création du nouveau rapport brouillon.");
                }
            }

            // Gérer les sections du rapport
            foreach ($sectionsContenu as $nomSection => $contenu) {
                $existingSection = $this->sectionRapportModel->trouverSectionUnique($idRapport, $nomSection);
                $sectionData = ['contenu' => $contenu];
                if ($existingSection) {
                    $this->sectionRapportModel->mettreAJourParClesInternes(['id_rapport_etudiant' => $idRapport, 'nom_section' => $nomSection], $sectionData);
                } else {
                    $sectionData['id_rapport_etudiant'] = $idRapport;
                    $sectionData['nom_section'] = $nomSection;
                    // Définir l'ordre d'affichage si vous avez une logique pour ça
                    $this->sectionRapportModel->creer($sectionData);
                }
            }

            $this->rapportEtudiantModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $numeroCarteEtudiant,
                $actionType,
                $actionDetails,
                $idRapport,
                'RapportEtudiant'
            );
            return $idRapport;
        } catch (\Exception $e) {
            $this->rapportEtudiantModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $numeroCarteEtudiant,
                'ECHEC_BROUILLON_RAPPORT',
                "Erreur gestion brouillon rapport pour {$numeroCarteEtudiant}: " . $e->getMessage()
            );
            throw $e;
        }
    }


    /**
     * Soumet un rapport (qui était en brouillon ou en corrections) pour la vérification de conformité.
     * Change le statut du rapport à 'RAP_SOUMIS'.
     * @param string $idRapportEtudiant L'ID du rapport à soumettre (VARCHAR).
     * @return bool Vrai si la soumission a réussi.
     * @throws ElementNonTrouveException Si le rapport n'est pas trouvé.
     * @throws OperationImpossibleException Si le rapport n'est pas en brouillon ou en corrections.
     */
    public function soumettreRapportPourVerification(string $idRapportEtudiant): bool
    {
        $rapport = $this->rapportEtudiantModel->trouverParIdentifiant($idRapportEtudiant);
        if (!$rapport) {
            throw new ElementNonTrouveException("Rapport étudiant '{$idRapportEtudiant}' non trouvé.");
        }

        // Vérifier que le rapport est dans un état où il peut être soumis
        if (!in_array($rapport['id_statut_rapport'], ['RAP_BROUILLON', 'RAP_NON_CONF', 'RAP_CORRECT'])) {
            throw new OperationImpossibleException("Le rapport '{$rapport['id_rapport_etudiant']}' ne peut être soumis que s'il est en brouillon ou s'il a été retourné pour corrections.");
        }

        $this->rapportEtudiantModel->commencerTransaction();
        try {
            $success = $this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapportEtudiant, [
                'id_statut_rapport' => 'RAP_SOUMIS', // Statut "Soumis"
                'date_soumission' => date('Y-m-d H:i:s'),
                'date_derniere_modif' => date('Y-m-d H:i:s')
            ]);

            if (!$success) {
                throw new OperationImpossibleException("Échec de la mise à jour du statut du rapport en 'Soumis'.");
            }

            // Notifier l'étudiant
            $this->notificationService->envoyerNotificationUtilisateur(
                $rapport['numero_carte_etudiant'],
                'RAPPORT_SOUMIS',
                "Votre rapport '{$rapport['libelle_rapport_etudiant']}' a été soumis avec succès pour vérification."
            );

            // Notifier le personnel de conformité
            $this->notificationService->envoyerNotificationGroupe(
                'GRP_PERS_ADMIN', // Ou un groupe plus spécifique pour la conformité
                'NOUVEAU_RAPPORT_SOUMIS',
                "Un nouveau rapport ('{$rapport['libelle_rapport_etudiant']}') a été soumis et attend la vérification de conformité."
            );

            $this->rapportEtudiantModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $rapport['numero_carte_etudiant'],
                'SOUMISSION_RAPPORT',
                "Rapport '{$idRapportEtudiant}' soumis pour vérification.",
                $idRapportEtudiant,
                'RapportEtudiant'
            );
            return true;
        } catch (\Exception $e) {
            $this->rapportEtudiantModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $rapport['numero_carte_etudiant'],
                'ECHEC_SOUMISSION_RAPPORT',
                "Erreur soumission rapport {$idRapportEtudiant}: " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Enregistre les corrections soumises par un étudiant.
     * Le rapport doit être au statut 'RAP_NON_CONF' ou 'RAP_CORRECT'.
     * @param string $idRapportEtudiant L'ID du rapport.
     * @param array $sectionsContenuCorriges Tableau associatif des sections corrigées.
     * @param string $numeroUtilisateurUpload L'ID de l'utilisateur (étudiant) qui soumet les corrections.
     * @param string|null $noteExplicative Une note explicative des corrections.
     * @return bool Vrai si les corrections sont enregistrées.
     * @throws ElementNonTrouveException Si le rapport n'est pas trouvé.
     * @throws OperationImpossibleException Si le rapport n'est pas en attente de corrections.
     */
    public function enregistrerCorrectionsSoumises(string $idRapportEtudiant, array $sectionsContenuCorriges, string $numeroUtilisateurUpload, ?string $noteExplicative = null): bool
    {
        $rapport = $this->rapportEtudiantModel->trouverParIdentifiant($idRapportEtudiant);
        if (!$rapport) {
            throw new ElementNonTrouveException("Rapport étudiant '{$idRapportEtudiant}' non trouvé.");
        }
        if ($rapport['numero_carte_etudiant'] !== $numeroUtilisateurUpload) {
            throw new OperationImpossibleException("L'utilisateur n'est pas autorisé à soumettre des corrections pour ce rapport.");
        }

        // Vérifier que le rapport est dans un état où les corrections peuvent être soumises
        if (!in_array($rapport['id_statut_rapport'], ['RAP_NON_CONF', 'RAP_CORRECT'])) {
            throw new OperationImpossibleException("Le rapport '{$idRapportEtudiant}' n'est pas dans un état ('Non Conforme' ou 'Corrections Demandées') permettant la soumission de corrections.");
        }

        $this->rapportEtudiantModel->commencerTransaction();
        try {
            // Mettre à jour les sections du rapport
            foreach ($sectionsContenuCorriges as $nomSection => $contenu) {
                $existingSection = $this->sectionRapportModel->trouverSectionUnique($idRapportEtudiant, $nomSection);
                $sectionData = ['contenu' => $contenu];
                if ($existingSection) {
                    $this->sectionRapportModel->mettreAJourParClesInternes(['id_rapport_etudiant' => $idRapportEtudiant, 'nom_section' => $nomSection], $sectionData);
                } else {
                    $sectionData['id_rapport_etudiant'] = $idRapportEtudiant;
                    $sectionData['nom_section'] = $nomSection;
                    $this->sectionRapportModel->creer($sectionData);
                }
            }

            // Mettre à jour le statut du rapport et les dates
            $success = $this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapportEtudiant, [
                'id_statut_rapport' => 'RAP_SOUMIS', // Remettre en statut "Soumis" pour nouvelle vérification de conformité
                'date_derniere_modif' => date('Y-m-d H:i:s')
            ]);

            if (!$success) {
                throw new OperationImpossibleException("Échec de la mise à jour du statut du rapport après corrections.");
            }

            // Si vous voulez enregistrer la "note explicative" quelque part, il faudrait un champ dans rapport_etudiant
            // ou une table d'historique des soumissions.

            $this->rapportEtudiantModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $numeroUtilisateurUpload,
                'SOUMISSION_CORRECTIONS',
                "Corrections soumises pour le rapport '{$idRapportEtudiant}' par {$numeroUtilisateurUpload}" . ($noteExplicative ? " (Note: {$noteExplicative})" : ""),
                $idRapportEtudiant,
                'RapportEtudiant'
            );
            // Notifier le personnel de conformité qu'il y a de nouvelles corrections à vérifier
            $this->notificationService->envoyerNotificationGroupe(
                'GRP_PERS_ADMIN',
                'CORRECTIONS_RAPPORT_SOUMISES',
                "Des corrections ont été soumises pour le rapport '{$rapport['libelle_rapport_etudiant']}' (ID: {$idRapportEtudiant})."
            );
            return true;
        } catch (\Exception $e) {
            $this->rapportEtudiantModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $numeroUtilisateurUpload,
                'ECHEC_ENREG_CORRECTIONS',
                "Erreur enregistrement corrections rapport {$idRapportEtudiant}: " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Récupère toutes les informations complètes d'un rapport étudiant.
     * Inclut les métadonnées, les sections de contenu, le statut, l'historique de conformité et de vote.
     * @param string $idRapportEtudiant L'ID du rapport étudiant.
     * @return array|null Les données complètes du rapport ou null si non trouvé.
     */
    public function recupererInformationsRapportComplet(string $idRapportEtudiant): ?array
    {
        $rapport = $this->rapportEtudiantModel->trouverParIdentifiant($idRapportEtudiant);
        if (!$rapport) {
            return null;
        }

        // Récupérer les sections de contenu
        $rapport['sections'] = $this->sectionRapportModel->trouverSectionsPourRapport($idRapportEtudiant);

        // Récupérer les informations de conformité
        $rapport['conformite_historique'] = $this->approuverModel->trouverParCritere(['id_rapport_etudiant' => $idRapportEtudiant], ['*'], 'AND', 'date_verification_conformite DESC');
        // Joindre les libellés de statut de conformité
        foreach ($rapport['conformite_historique'] as &$entry) {
            $statutRef = $this->statutConformiteRefModel->trouverParIdentifiant($entry['id_statut_conformite']);
            $entry['libelle_statut_conformite'] = $statutRef['libelle_statut_conformite'] ?? 'Inconnu';
            // Joindre les détails du personnel
            $personnel = $this->utilisateurModel->trouverParIdentifiant($entry['numero_personnel_administratif'], ['nom', 'prenom']);
            $entry['nom_personnel'] = ($personnel['prenom'] ?? '') . ' ' . ($personnel['nom'] ?? '');
        }

        // Récupérer les informations de vote et décision de la commission
        $rapport['votes'] = $this->voteCommissionModel->trouverParCritere(['id_rapport_etudiant' => $idRapportEtudiant], ['*'], 'AND', 'date_vote DESC');
        // Joindre les libellés de décision de vote
        foreach ($rapport['votes'] as &$vote) {
            $decisionRef = $this->decisionVoteRefModel->trouverParIdentifiant($vote['id_decision_vote']);
            $vote['libelle_decision_vote'] = $decisionRef['libelle_decision_vote'] ?? 'Inconnue';
            // Joindre les détails de l'enseignant
            $enseignant = $this->utilisateurModel->trouverParIdentifiant($vote['numero_enseignant'], ['nom', 'prenom']);
            $vote['nom_enseignant'] = ($enseignant['prenom'] ?? '') . ' ' . ($enseignant['nom'] ?? '');
        }

        // Récupérer le PV de validation final (si existant)
        $pvFinal = $this->compteRenduModel->trouverUnParCritere(['id_rapport_etudiant' => $idRapportEtudiant, 'id_statut_pv' => 'PV_VALID']);
        if ($pvFinal) {
            $rapport['pv_final'] = $pvFinal;
            // Vous pourriez également joindre les chemins de fichiers de document_genere si le PV est un PDF
            $documentPv = $this->documentGenereModel->trouverParEntiteSource($pvFinal['id_compte_rendu'], 'PV');
            $rapport['pv_final']['document_path'] = $documentPv[0]['chemin_fichier'] ?? null;
        } else {
            $rapport['pv_final'] = null;
        }

        // Récupérer les documents (fichiers) liés au rapport
        // A NOTER: Le document soumis est maintenant `document_genere` avec `id_type_document_ref` = 'DOC_RAP_MAIN'
        $rapport['fichiers_joints'] = $this->documentGenereModel->trouverParEntiteSource($idRapportEtudiant, 'RAPPORT'); // Assurez-vous que le type est correctement enregistré

        return $rapport;
    }

    /**
     * Met à jour le statut d'un rapport étudiant.
     * @param string $idRapportEtudiant L'ID du rapport.
     * @param string $newStatutId Le nouvel ID de statut (ex: 'RAP_VALID', 'RAP_REFUSE').
     * @return bool Vrai si la mise à jour a réussi.
     * @throws ElementNonTrouveException Si le rapport ou le statut n'est pas trouvé.
     * @throws OperationImpossibleException En cas d'échec de la mise à jour.
     */
    public function mettreAJourStatutRapport(string $idRapportEtudiant, string $newStatutId): bool
    {
        $rapport = $this->rapportEtudiantModel->trouverParIdentifiant($idRapportEtudiant);
        if (!$rapport) {
            throw new ElementNonTrouveException("Rapport étudiant non trouvé.");
        }
        if (!$this->statutRapportRefModel->trouverParIdentifiant($newStatutId)) {
            throw new ElementNonTrouveException("Statut de rapport '{$newStatutId}' non reconnu.");
        }

        $success = $this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapportEtudiant, ['id_statut_rapport' => $newStatutId]);

        if ($success) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'MAJ_STATUT_RAPPORT',
                "Statut du rapport '{$idRapportEtudiant}' changé à '{$newStatutId}'",
                $idRapportEtudiant,
                'RapportEtudiant'
            );
        }
        return $success;
    }

    /**
     * Permet de réactiver l'édition d'un rapport pour des corrections ou une reprise.
     * @param string $idRapportEtudiant L'ID du rapport.
     * @param string $motifActivation Un motif expliquant la réactivation.
     * @return bool Vrai si l'édition est réactivée.
     * @throws ElementNonTrouveException Si le rapport n'est pas trouvé.
     * @throws OperationImpossibleException Si le rapport est déjà en brouillon ou n'est pas dans un état permettant la réactivation.
     */
    public function reactiverEditionRapport(string $idRapportEtudiant, string $motifActivation = 'Reprise demandée'): bool
    {
        $rapport = $this->rapportEtudiantModel->trouverParIdentifiant($idRapportEtudiant);
        if (!$rapport) {
            throw new ElementNonTrouveException("Rapport étudiant non trouvé.");
        }

        // Un rapport peut être réactivé pour édition s'il est refusé, ou corrections demandées.
        if (!in_array($rapport['id_statut_rapport'], ['RAP_REFUSE', 'RAP_CORRECT'])) {
            throw new OperationImpossibleException("Le rapport '{$idRapportEtudiant}' n'est pas dans un état permettant la réactivation de l'édition.");
        }
        if ($rapport['id_statut_rapport'] === 'RAP_BROUILLON') {
            throw new OperationImpossibleException("Le rapport est déjà en statut brouillon.");
        }

        $this->rapportEtudiantModel->commencerTransaction();
        try {
            // Changer le statut à 'BROUILLON' pour permettre à l'étudiant de modifier
            $success = $this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapportEtudiant, [
                'id_statut_rapport' => 'RAP_BROUILLON',
                'date_derniere_modif' => date('Y-m-d H:i:s')
            ]);

            if (!$success) {
                throw new OperationImpossibleException("Échec de la réactivation de l'édition du rapport.");
            }

            $this->rapportEtudiantModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? $rapport['numero_carte_etudiant'], // Qui a déclenché (admin/RS ou l'étudiant via réclamation)
                'REACTIVATION_RAPPORT_EDITION',
                "Édition du rapport '{$idRapportEtudiant}' réactivée. Motif: '{$motifActivation}'",
                $idRapportEtudiant,
                'RapportEtudiant'
            );
            $this->notificationService->envoyerNotificationUtilisateur(
                $rapport['numero_carte_etudiant'],
                'RAPPORT_EDITION_REACTIVEE',
                "L'édition de votre rapport '{$rapport['libelle_rapport_etudiant']}' a été réactivée. Motif: {$motifActivation}"
            );
            return true;
        } catch (\Exception $e) {
            $this->rapportEtudiantModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_REACTIVATION_RAPPORT_EDITION',
                "Erreur réactivation édition rapport {$idRapportEtudiant}: " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Liste des rapports étudiants en fonction de critères.
     * @param array $criteres Critères de filtre (ex: ['id_statut_rapport' => 'RAP_VALID']).
     * @param array $colonnes Les colonnes à sélectionner.
     * @param string $operateurLogique L'opérateur logique entre les critères ('AND' ou 'OR').
     * @param string|null $orderBy Colonne pour le tri.
     * @param int|null $limit Limite de résultats.
     * @param int|null $offset Offset pour la pagination.
     * @return array Liste des rapports trouvés.
     */
    public function listerRapportsParCriteres(array $criteres = [], array $colonnes = ['*'], string $operateurLogique = 'AND', ?string $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->rapportEtudiantModel->trouverParCritere($criteres, $colonnes, $operateurLogique, $orderBy, $limit, $offset);
    }
}