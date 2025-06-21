<?php
namespace App\Backend\Service\Commission;

use PDO;
use App\Backend\Model\Affecter;
use App\Backend\Model\VoteCommission;
use App\Backend\Model\CompteRendu;
use App\Backend\Model\PvSessionRapport;
use App\Backend\Model\ValidationPv;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\DecisionVoteRef;
use App\Backend\Model\DecisionValidationPvRef;
use App\Backend\Model\StatutRapportRef;
use App\Backend\Model\SessionValidation; // Nouveau modèle
use App\Backend\Model\SessionRapport; // Nouveau modèle
use App\Backend\Model\Utilisateur; // Pour récupérer le nombre de membres de la commission si besoin
use App\Backend\Service\Notification\ServiceNotification; // Pour les notifications
use App\Backend\Service\DocumentGenerator\ServiceDocumentGenerator; // Pour la génération de PV
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin; // Pour journalisation
use App\Backend\Service\IdentifiantGenerator\IdentifiantGenerator; // Pour générer les IDs de session/vote
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;

class ServiceCommission implements ServiceCommissionInterface
{
    private Affecter $affecterModel;
    private VoteCommission $voteCommissionModel;
    private CompteRendu $compteRenduModel;
    private PvSessionRapport $pvSessionRapportModel;
    private ValidationPv $validationPvModel;
    private RapportEtudiant $rapportEtudiantModel;
    private DecisionVoteRef $decisionVoteRefModel;
    private DecisionValidationPvRef $decisionValidationPvRefModel;
    private StatutRapportRef $statutRapportRefModel;
    private SessionValidation $sessionValidationModel; // Nouveau
    private SessionRapport $sessionRapportModel; // Nouveau
    private Utilisateur $utilisateurModel; // Utilisé pour compter les membres de la commission
    private ServiceNotification $notificationService;
    private ServiceDocumentGenerator $documentGenerator;
    private ServiceSupervisionAdmin $supervisionService;
    private IdentifiantGenerator $idGenerator;


    public function __construct(
        PDO $db,
        ServiceNotification $notificationService,
        ServiceDocumentGenerator $documentGenerator,
        ServiceSupervisionAdmin $supervisionService,
        IdentifiantGenerator $idGenerator
    ) {
        $this->affecterModel = new Affecter($db);
        $this->voteCommissionModel = new VoteCommission($db);
        $this->compteRenduModel = new CompteRendu($db);
        $this->pvSessionRapportModel = new PvSessionRapport($db);
        $this->validationPvModel = new ValidationPv($db);
        $this->rapportEtudiantModel = new RapportEtudiant($db);
        $this->decisionVoteRefModel = new DecisionVoteRef($db);
        $this->decisionValidationPvRefModel = new DecisionValidationPvRef($db);
        $this->statutRapportRefModel = new StatutRapportRef($db);
        $this->sessionValidationModel = new SessionValidation($db); // Initialisation
        $this->sessionRapportModel = new SessionRapport($db); // Initialisation
        $this->utilisateurModel = new Utilisateur($db); // Initialisation

        $this->notificationService = $notificationService;
        $this->documentGenerator = $documentGenerator;
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
    }

    // --- GESTION DES SESSIONS DE VALIDATION ---

    /**
     * Crée une nouvelle session de validation de la commission.
     * @param string $libelleSession Le libellé de la session.
     * @param string $dateDebutSession La date et heure de début.
     * @param string $dateFinPrevue La date et heure de fin prévue.
     * @param string|null $numeroPresidentCommission Le numéro de l'enseignant président.
     * @param array $idsRapports Initialement rattachés à la session.
     * @return string L'ID de la session créée.
     * @throws DoublonException Si une session avec le même libellé pour la même période existe.
     * @throws \Exception En cas d'erreur de création.
     */
    public function creerSessionValidation(string $libelleSession, string $dateDebutSession, string $dateFinPrevue, ?string $numeroPresidentCommission = null, array $idsRapports = []): string
    {
        $this->sessionValidationModel->commencerTransaction();
        try {
            $idSession = $this->idGenerator->genererIdentifiantUnique('SESS'); // SESS-AAAA-SSSS

            $data = [
                'id_session' => $idSession,
                'libelle_session' => $libelleSession,
                'date_debut_session' => $dateDebutSession,
                'date_fin_prevue' => $dateFinPrevue,
                'statut_session' => 'Planifiee',
                'numero_president_commission' => $numeroPresidentCommission
            ];

            if (!$this->sessionValidationModel->creer($data)) {
                throw new OperationImpossibleException("Échec de la création de la session de validation.");
            }

            if (!empty($idsRapports)) {
                foreach ($idsRapports as $idRapport) {
                    // Rattachement dans session_rapport
                    if (!$this->sessionRapportModel->creer(['id_session' => $idSession, 'id_rapport_etudiant' => $idRapport])) {
                        throw new OperationImpossibleException("Échec du rattachement du rapport {$idRapport} à la session {$idSession}.");
                    }
                    // Mettre à jour le statut du rapport vers "En commission"
                    $this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapport, ['id_statut_rapport' => 'RAP_EN_COMM']);
                }
            }

            $this->sessionValidationModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $numeroPresidentCommission ?? 'SYSTEM',
                'CREATION_SESSION_VALIDATION',
                "Session de validation '{$libelleSession}' créée (ID: {$idSession})",
                $idSession,
                'SessionValidation'
            );
            return $idSession;
        } catch (DoublonException $e) {
            $this->sessionValidationModel->annulerTransaction();
            throw $e;
        } catch (\Exception $e) {
            $this->sessionValidationModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $numeroPresidentCommission ?? 'SYSTEM',
                'ECHEC_CREATION_SESSION_VALIDATION',
                "Erreur création session de validation: " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Démarre une session de validation, passant son statut à 'En cours'.
     * @param string $idSession L'ID de la session.
     * @return bool Vrai si la mise à jour a réussi.
     * @throws ElementNonTrouveException Si la session n'est pas trouvée.
     * @throws OperationImpossibleException Si la session est déjà en cours ou clôturée.
     */
    public function demarrerSession(string $idSession): bool
    {
        $session = $this->sessionValidationModel->trouverParIdentifiant($idSession);
        if (!$session) {
            throw new ElementNonTrouveException("Session de validation non trouvée.");
        }
        if ($session['statut_session'] !== 'Planifiee') {
            throw new OperationImpossibleException("La session ne peut être démarrée que si son statut est 'Planifiee'.");
        }

        $success = $this->sessionValidationModel->mettreAJourParIdentifiant($idSession, ['statut_session' => 'En cours']);
        if ($success) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'DEMARRAGE_SESSION_VALIDATION',
                "Session de validation '{$idSession}' démarrée",
                $idSession,
                'SessionValidation'
            );
        }
        return $success;
    }

    /**
     * Clôture une session de validation, passant son statut à 'Cloturee'.
     * @param string $idSession L'ID de la session.
     * @return bool Vrai si la mise à jour a réussi.
     * @throws ElementNonTrouveException Si la session n'est pas trouvée.
     * @throws OperationImpossibleException Si la session n'est pas 'En cours'.
     */
    public function cloturerSession(string $idSession): bool
    {
        $session = $this->sessionValidationModel->trouverParIdentifiant($idSession);
        if (!$session) {
            throw new ElementNonTrouveException("Session de validation non trouvée.");
        }
        if ($session['statut_session'] !== 'En cours') {
            throw new OperationImpossibleException("La session ne peut être clôturée que si son statut est 'En cours'.");
        }

        // Vérifier que tous les rapports de la session ont une décision finale (Validé/Refusé/Corrigé)
        $rapportsEnSession = $this->sessionRapportModel->trouverRapportsDansSession($idSession);
        foreach ($rapportsEnSession as $sr) {
            $rapport = $this->rapportEtudiantModel->trouverParIdentifiant($sr['id_rapport_etudiant'], ['id_statut_rapport']);
            if ($rapport && in_array($rapport['id_statut_rapport'], ['RAP_EN_COMM', 'RAP_BROUILLON', 'RAP_SOUMIS', 'RAP_NON_CONF'])) {
                throw new OperationImpossibleException("Tous les rapports de la session doivent avoir une décision finale avant de la clôturer. Rapport {$sr['id_rapport_etudiant']} n'est pas finalisé.");
            }
        }


        $success = $this->sessionValidationModel->mettreAJourParIdentifiant($idSession, ['statut_session' => 'Cloturee']);
        if ($success) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'CLOTURE_SESSION_VALIDATION',
                "Session de validation '{$idSession}' clôturée",
                $idSession,
                'SessionValidation'
            );
        }
        return $success;
    }

    /**
     * Récupère la liste des rapports assignés à un membre du jury pour une session donnée.
     * @param string $numeroEnseignant Le numéro de l'enseignant.
     * @param string|null $idSession L'ID de la session si spécifiée.
     * @return array Liste des rapports.
     */
    public function recupererRapportsAssignedToJury(string $numeroEnseignant, ?string $idSession = null): array
    {
        $criteres = ['numero_enseignant' => $numeroEnseignant];
        if ($idSession) {
            $criteres['id_session'] = $idSession; // Assurez-vous que Affecter a id_session ou joindre via session_rapport
            // Implémentation réelle pourrait nécessiter une jointure complexe ici dans le model Affecter ou une méthode de service
            // Pour simplifier ici, on suppose que Affecter a id_session ou on récupère les rapports de la session, puis on filtre par enseignant.
        }
        // Ceci est une simplification. Dans une vraie application, ServiceAffecter serait plus approprié ou une méthode de jointure
        // dans AffecterModel pour récupérer les rapports complets.
        $affectations = $this->affecterModel->trouverParCritere($criteres);

        $rapports = [];
        foreach ($affectations as $affectation) {
            $rapports[] = $this->rapportEtudiantModel->trouverParIdentifiant($affectation['id_rapport_etudiant']);
        }
        return array_filter($rapports); // Filtrer les nulls
    }


    // --- ÉVALUATION ET VOTE SUR LES RAPPORTS ---

    /**
     * Enregistre le vote d'un membre de la commission pour un rapport.
     * @param string $idRapportEtudiant L'ID du rapport.
     * @param string $numeroEnseignant Le numéro de l'enseignant votant.
     * @param string $idDecisionVote L'ID de la décision de vote (ex: 'DV_APPROUVE', 'DV_REFUSE').
     * @param string|null $commentaireVote Le commentaire associé au vote.
     * @param int $tourVote Le tour de vote actuel.
     * @param string|null $idSession L'ID de la session si le vote est rattaché à une session.
     * @return bool Vrai si le vote est enregistré.
     * @throws ElementNonTrouveException Si rapport ou décision de vote n'existe pas.
     * @throws OperationImpossibleException Si le commentaire est manquant pour une décision non-simple ou si le membre a déjà voté pour ce tour.
     */
    public function enregistrerVotePourRapport(string $idRapportEtudiant, string $numeroEnseignant, string $idDecisionVote, ?string $commentaireVote, int $tourVote, ?string $idSession = null): bool
    {
        $rapport = $this->rapportEtudiantModel->trouverParIdentifiant($idRapportEtudiant);
        if (!$rapport) {
            throw new ElementNonTrouveException("Rapport étudiant non trouvé.");
        }
        $decisionRef = $this->decisionVoteRefModel->trouverParIdentifiant($idDecisionVote);
        if (!$decisionRef) {
            throw new ElementNonTrouveException("Décision de vote non reconnue.");
        }

        // Vérifier si le commentaire est obligatoire pour la décision
        if (in_array($idDecisionVote, ['DV_REFUSE', 'DV_DISCUSSION']) && empty($commentaireVote)) {
            throw new OperationImpossibleException("Un commentaire est obligatoire pour la décision '{$decisionRef['libelle_decision_vote']}'.");
        }

        // Vérifier si l'enseignant a déjà voté pour ce rapport et ce tour de vote
        $existingVote = $this->voteCommissionModel->trouverVoteUnique($idRapportEtudiant, $numeroEnseignant, $tourVote);
        if ($existingVote) {
            throw new OperationImpossibleException("L'enseignant a déjà voté pour ce rapport lors de ce tour de vote.");
        }

        $this->voteCommissionModel->commencerTransaction();
        try {
            $idVote = $this->idGenerator->genererIdentifiantUnique('VOTE'); // VOTE-AAAA-SSSS

            $data = [
                'id_vote' => $idVote,
                'id_rapport_etudiant' => $idRapportEtudiant,
                'numero_enseignant' => $numeroEnseignant,
                'id_decision_vote' => $idDecisionVote,
                'commentaire_vote' => $commentaireVote,
                'tour_vote' => $tourVote,
                'id_session' => $idSession // Ajout de l'ID de session
            ];

            if (!$this->voteCommissionModel->creer($data)) {
                throw new OperationImpossibleException("Échec de l'enregistrement du vote.");
            }

            $this->supervisionService->enregistrerAction(
                $numeroEnseignant,
                'VOTE_RAPPORT',
                "Vote '{$decisionRef['libelle_decision_vote']}' pour rapport {$idRapportEtudiant} (Tour: {$tourVote})",
                $idRapportEtudiant,
                'RapportEtudiant'
            );

            $this->voteCommissionModel->validerTransaction();

            // Après le vote, tenter de finaliser la décision si tous les votes sont là
            $this->finaliserDecisionCommissionPourRapport($idRapportEtudiant);

            return true;
        } catch (\Exception $e) {
            $this->voteCommissionModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $numeroEnseignant,
                'ECHEC_VOTE_RAPPORT',
                "Erreur vote pour rapport {$idRapportEtudiant}: " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Tente de finaliser la décision de la commission pour un rapport.
     * Déclenchée après chaque vote pour vérifier l'unanimité.
     * @param string $idRapportEtudiant L'ID du rapport.
     * @return bool Vrai si la décision a été finalisée, faux sinon (si non-unanimité ou votes incomplets).
     * @throws ElementNonTrouveException Si le rapport n'est pas trouvé.
     * @throws OperationImpossibleException Si un statut de rapport final n'est pas reconnu.
     */
    public function finaliserDecisionCommissionPourRapport(string $idRapportEtudiant): bool
    {
        $rapport = $this->rapportEtudiantModel->trouverParIdentifiant($idRapportEtudiant, ['id_statut_rapport']);
        if (!$rapport) {
            throw new ElementNonTrouveException("Rapport étudiant non trouvé.");
        }

        // Si le rapport a déjà un statut final, ne rien faire
        if (in_array($rapport['id_statut_rapport'], ['RAP_VALID', 'RAP_REFUSE', 'RAP_CORRECT'])) {
            return false;
        }

        // Récupérer le nombre de membres requis pour le jury (assumer 4 membres pour la commission)
        // Dans une vraie application, on obtiendrait cela depuis l'affectation du jury
        // Pour l'instant, on se base sur le rôle GRP_COMMISSION
        $commissionMembersCount = $this->utilisateurModel->compterParCritere(['id_groupe_utilisateur' => 'GRP_COMMISSION', 'statut_compte' => 'actif']);
        if ($commissionMembersCount === 0) {
            // Si aucun membre de commission actif, on ne peut pas finaliser
            return false;
        }

        // Récupérer tous les votes pour ce rapport et le tour de vote actuel
        $votes = $this->voteCommissionModel->trouverParCritere(['id_rapport_etudiant' => $idRapportEtudiant], ['id_decision_vote', 'numero_enseignant', 'tour_vote'], 'AND', 'tour_vote DESC', null, null);

        if (empty($votes)) {
            return false; // Aucun vote encore
        }

        $currentTour = $votes[0]['tour_vote']; // Le tour le plus récent
        $votesThisTour = array_filter($votes, fn($v) => $v['tour_vote'] == $currentTour);

        if (count($votesThisTour) < $commissionMembersCount) {
            return false; // Pas encore tous les votes pour ce tour
        }

        // Vérifier l'unanimité
        $decisionsCount = array_count_values(array_column($votesThisTour, 'id_decision_vote'));

        $finalStatutId = null;
        $decisionLibelle = '';

        if (count($decisionsCount) === 1) {
            $uniqueDecision = array_keys($decisionsCount)[0];
            if ($uniqueDecision === 'DV_APPROUVE') {
                $finalStatutId = 'RAP_VALID';
                $decisionLibelle = 'Validé par la commission';
            } elseif ($uniqueDecision === 'DV_REFUSE') {
                $finalStatutId = 'RAP_REFUSE';
                $decisionLibelle = 'Refusé par la commission';
            } elseif ($uniqueDecision === 'DV_DISCUSSION') {
                // Si tous veulent discuter, cela reste "En commission" ou passe à "En délibération"
                $finalStatutId = 'RAP_EN_COMM'; // Ou un nouveau statut 'EN_DELIBERATION' si nécessaire
                $decisionLibelle = 'Délibération nécessaire';
            }
        } else {
            // Non-unanimité, ou combinaisons de votes qui ne mènent pas à un statut final simple
            // Si des "Corrections" sont demandées par certains, c'est le statut le plus "indulgent" qui prévaut après unanimité sur ces décisions.
            // Sinon, c'est 'RAP_EN_COMM' ou un nouveau tour de vote est requis.
            if (isset($decisionsCount['DV_APPROUVE']) && isset($decisionsCount['DV_DISCUSSION']) && !isset($decisionsCount['DV_REFUSE'])) {
                // Majorité Approbation / Discussion => Recommandation de corrections si pas de refus franc
                $finalStatutId = 'RAP_CORRECT'; // Demande de corrections
                $decisionLibelle = 'Corrections demandées par la commission';
            } else {
                $finalStatutId = 'RAP_EN_COMM'; // Reste en commission, nécessite une nouvelle action ou un nouveau tour
                $decisionLibelle = 'Décision non unanime, délibération en cours';
            }
        }

        if ($finalStatutId) {
            // Mettre à jour le statut du rapport
            $success = $this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapportEtudiant, ['id_statut_rapport' => $finalStatutId]);

            if ($success) {
                $this->supervisionService->enregistrerAction(
                    'SYSTEM', // Action système
                    'FINALISATION_DECISION_RAPPORT',
                    "Décision finale pour rapport {$idRapportEtudiant}: {$decisionLibelle}",
                    $idRapportEtudiant,
                    'RapportEtudiant'
                );
                // Notifier l'étudiant de la décision finale
                $rapportEtudiantData = $this->rapportEtudiantModel->trouverParIdentifiant($idRapportEtudiant, ['numero_carte_etudiant']);
                if ($rapportEtudiantData) {
                    $this->notificationService->envoyerNotificationUtilisateur(
                        $rapportEtudiantData['numero_carte_etudiant'],
                        'DECISION_RAPPORT',
                        "Votre rapport '{$idRapportEtudiant}' a été évalué: {$decisionLibelle}."
                    );
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Lance un nouveau tour de vote pour un rapport.
     * @param string $idRapportEtudiant L'ID du rapport.
     * @return bool Vrai si le nouveau tour est initié.
     * @throws ElementNonTrouveException Si le rapport n'est pas trouvé.
     * @throws OperationImpossibleException Si le rapport n'est pas dans un état permettant un nouveau tour de vote.
     */
    public function lancerNouveauTourVote(string $idRapportEtudiant): bool
    {
        $rapport = $this->rapportEtudiantModel->trouverParIdentifiant($idRapportEtudiant, ['id_statut_rapport']);
        if (!$rapport) {
            throw new ElementNonTrouveException("Rapport étudiant non trouvé.");
        }

        // Autoriser un nouveau tour seulement si le rapport est en commission et pas encore finalisé
        if (!in_array($rapport['id_statut_rapport'], ['RAP_EN_COMM'])) { // Ou 'EN_DELIBERATION' si vous l'ajoutez
            throw new OperationImpossibleException("Un nouveau tour de vote ne peut être lancé que pour un rapport en délibération ou en commission.");
        }

        $this->voteCommissionModel->commencerTransaction();
        try {
            // Récupérer le tour de vote actuel le plus élevé
            $latestVote = $this->voteCommissionModel->trouverParCritere(
                ['id_rapport_etudiant' => $idRapportEtudiant],
                ['tour_vote'],
                'AND',
                'tour_vote DESC',
                1
            );
            $currentTour = $latestVote[0]['tour_vote'] ?? 0;
            $newTour = $currentTour + 1;

            // Mettre à jour le rapport pour indiquer le nouveau tour de vote si nécessaire
            // Ou simplement les votes suivants utiliseront ce nouveau tour
            // Ce service ne supprime pas les anciens votes, il crée juste de nouveaux votes pour le nouveau tour
            // La suppression/archive des anciens votes est une décision de conception si nécessaire.

            $this->voteCommissionModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'NOUVEAU_TOUR_VOTE',
                "Nouveau tour de vote ({$newTour}) lancé pour rapport {$idRapportEtudiant}",
                $idRapportEtudiant,
                'RapportEtudiant'
            );
            return true;
        } catch (\Exception $e) {
            $this->voteCommissionModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_NOUVEAU_TOUR_VOTE',
                "Erreur lancement nouveau tour vote pour rapport {$idRapportEtudiant}: " . $e->getMessage()
            );
            throw $e;
        }
    }


    // --- GESTION DES PROCÈS-VERBAUX (PV) ---

    /**
     * Rédige ou met à jour un Procès-Verbal (PV).
     * @param string $idRedacteur Le numéro de l'utilisateur rédacteur.
     * @param string $libellePv Le libellé ou titre du PV.
     * @param string $typePv Le type de PV ('Individuel' ou 'Session').
     * @param string|null $idRapportEtudiant L'ID du rapport si PV Individuel.
     * @param array $idsRapportsSession Les IDs des rapports si PV de Session.
     * @param string|null $idCompteRenduExistant L'ID du PV existant pour une mise à jour.
     * @return string L'ID du PV créé ou mis à jour.
     * @throws OperationImpossibleException En cas d'erreur de création ou de lien.
     * @throws ElementNonTrouveException Si un rapport ou un rédacteur n'existe pas.
     */
    public function redigerOuMettreAJourPv(string $idRedacteur, string $libellePv, string $typePv, ?string $idRapportEtudiant = null, array $idsRapportsSession = [], ?string $idCompteRenduExistant = null): string
    {
        $this->compteRenduModel->commencerTransaction();
        try {
            $idCompteRendu = $idCompteRenduExistant;
            if (!$idCompteRendu) {
                $idCompteRendu = $this->idGenerator->genererIdentifiantUnique('PV'); // PV_-AAAA-SSSS
                $data = [
                    'id_compte_rendu' => $idCompteRendu,
                    'libelle_compte_rendu' => $libellePv,
                    'type_pv' => $typePv,
                    'id_statut_pv' => 'PV_BROUILLON',
                    'id_redacteur' => $idRedacteur,
                    'date_creation_pv' => date('Y-m-d H:i:s')
                ];
                if ($typePv === 'Individuel' && $idRapportEtudiant) {
                    $data['id_rapport_etudiant'] = $idRapportEtudiant;
                }
                if (!$this->compteRenduModel->creer($data)) {
                    throw new OperationImpossibleException("Échec de la création du PV.");
                }
            } else {
                // Mise à jour d'un PV existant
                $data = [
                    'libelle_compte_rendu' => $libellePv,
                    'type_pv' => $typePv,
                    'id_redacteur' => $idRedacteur, // L'ID du rédacteur peut changer s'il est transféré
                    'date_creation_pv' => date('Y-m-d H:i:s') // Mettre à jour la date de dernière modification
                ];
                if ($typePv === 'Individuel' && $idRapportEtudiant) {
                    $data['id_rapport_etudiant'] = $idRapportEtudiant;
                } else if ($typePv === 'Session') { // Assurez-vous que id_rapport_etudiant est null pour session si la colonne est utilisée pour le type Individuel
                    $data['id_rapport_etudiant'] = null;
                }

                if (!$this->compteRenduModel->mettreAJourParIdentifiant($idCompteRendu, $data)) {
                    throw new OperationImpossibleException("Échec de la mise à jour du PV.");
                }
            }

            // Gérer les liens PvSessionRapport si type est 'Session'
            if ($typePv === 'Session' && !empty($idsRapportsSession)) {
                // Supprimer les anciennes liaisons pour ce PV de session avant de recréer
                // (Si la gestion est "tout ou rien" pour les rapports d'une session de PV)
                $this->pvSessionRapportModel->supprimerParCritere(['id_compte_rendu' => $idCompteRendu]);
                foreach ($idsRapportsSession as $rapportId) {
                    if (!$this->pvSessionRapportModel->creer(['id_compte_rendu' => $idCompteRendu, 'id_rapport_etudiant' => $rapportId])) {
                        throw new OperationImpossibleException("Échec de la liaison du rapport {$rapportId} au PV de session {$idCompteRendu}.");
                    }
                }
            } elseif ($typePv === 'Individuel') {
                // S'assurer qu'il n'y a pas de liaisons session_rapport pour un PV individuel
                $this->pvSessionRapportModel->supprimerParCritere(['id_compte_rendu' => $idCompteRendu]);
            }


            $this->compteRenduModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $idRedacteur,
                $idCompteRenduExistant ? 'MISE_AJOUR_PV' : 'REDIGER_PV',
                "PV {$idCompteRendu} de type '{$typePv}' " . ($idCompteRenduExistant ? "mis à jour" : "rédigé"),
                $idCompteRendu,
                'CompteRendu'
            );
            return $idCompteRendu;
        } catch (DoublonException $e) {
            $this->compteRenduModel->annulerTransaction();
            throw $e;
        } catch (\Exception $e) {
            $this->compteRenduModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $idRedacteur,
                $idCompteRenduExistant ? 'ECHEC_MISE_AJOUR_PV' : 'ECHEC_REDIGER_PV',
                "Erreur rédaction/maj PV: " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Soumet un PV pour validation par les autres membres de la commission.
     * @param string $idCompteRendu L'ID du PV à soumettre.
     * @return bool Vrai si la soumission réussit.
     * @throws ElementNonTrouveException Si le PV n'est pas trouvé.
     * @throws OperationImpossibleException Si le statut du PV ne permet pas la soumission.
     */
    public function soumettrePvPourValidation(string $idCompteRendu): bool
    {
        $pv = $this->compteRenduModel->trouverParIdentifiant($idCompteRendu);
        if (!$pv) {
            throw new ElementNonTrouveException("Procès-verbal non trouvé.");
        }
        if ($pv['id_statut_pv'] !== 'PV_BROUILLON') {
            throw new OperationImpossibleException("Seul un PV en brouillon peut être soumis pour validation.");
        }

        $this->compteRenduModel->commencerTransaction();
        try {
            $success = $this->compteRenduModel->mettreAJourParIdentifiant($idCompteRendu, ['id_statut_pv' => 'PV_SOUMIS_VALID']);

            if ($success) {
                // Notifier les autres membres de la commission qui doivent valider ce PV
                // Récupérer les membres du groupe 'GRP_COMMISSION' (sauf le rédacteur si on veut)
                $membresCommission = $this->utilisateurModel->trouverParCritere(['id_groupe_utilisateur' => 'GRP_COMMISSION', 'statut_compte' => 'actif']);
                foreach ($membresCommission as $membre) {
                    if ($membre['numero_utilisateur'] !== $pv['id_redacteur']) { // Ne pas notifier le rédacteur de valider son propre PV
                        $this->notificationService->envoyerNotificationUtilisateur(
                            $membre['numero_utilisateur'],
                            'PV_SOUMIS_VALIDATION',
                            "Un nouveau PV ({$pv['libelle_compte_rendu']}) a été soumis pour votre validation."
                        );
                    }
                }

                $this->compteRenduModel->validerTransaction();
                $this->supervisionService->enregistrerAction(
                    $_SESSION['user_id'] ?? 'SYSTEM',
                    'SOUMISSION_PV_VALIDATION',
                    "PV '{$idCompteRendu}' soumis pour validation",
                    $idCompteRendu,
                    'CompteRendu'
                );
                return true;
            }
            $this->compteRenduModel->annulerTransaction();
            return false;
        } catch (\Exception $e) {
            $this->compteRenduModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_SOUMISSION_PV_VALIDATION',
                "Erreur soumission PV pour validation: " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Gère la validation ou le rejet d'un PV par un membre de la commission.
     * @param string $idCompteRendu L'ID du PV.
     * @param string $numeroEnseignantValidateur Le numéro de l'enseignant qui valide/rejette.
     * @param string $idDecisionValidationPv L'ID de la décision (ex: 'DV_PV_APPROUVE', 'DV_PV_MODIF').
     * @param string|null $commentaireValidation Le commentaire du validateur.
     * @return bool Vrai si la validation a réussi.
     * @throws ElementNonTrouveException Si PV ou décision non trouvée.
     * @throws OperationImpossibleException Si le PV n'est pas en attente de validation ou si le membre a déjà validé.
     */
    public function validerOuRejeterPv(string $idCompteRendu, string $numeroEnseignantValidateur, string $idDecisionValidationPv, ?string $commentaireValidation): bool
    {
        $pv = $this->compteRenduModel->trouverParIdentifiant($idCompteRendu, ['id_statut_pv', 'id_redacteur', 'id_rapport_etudiant']);
        if (!$pv) {
            throw new ElementNonTrouveException("Procès-verbal non trouvé.");
        }
        if ($pv['id_statut_pv'] !== 'PV_SOUMIS_VALID') {
            throw new OperationImpossibleException("Ce PV n'est pas en attente de validation.");
        }
        if ($pv['id_redacteur'] === $numeroEnseignantValidateur) {
            throw new OperationImpossibleException("Le rédacteur du PV ne peut pas valider son propre PV.");
        }

        $decisionRef = $this->decisionValidationPvRefModel->trouverParIdentifiant($idDecisionValidationPv);
        if (!$decisionRef) {
            throw new ElementNonTrouveException("Décision de validation PV non reconnue.");
        }

        // Vérifier si le validateur a déjà donné son avis
        $existingValidation = $this->validationPvModel->trouverValidationPvParCles($idCompteRendu, $numeroEnseignantValidateur);
        if ($existingValidation) {
            throw new OperationImpossibleException("Vous avez déjà validé ce PV.");
        }

        $this->compteRenduModel->commencerTransaction();
        try {
            // Enregistrer l'avis du validateur
            $validationData = [
                'id_compte_rendu' => $idCompteRendu,
                'numero_enseignant' => $numeroEnseignantValidateur,
                'id_decision_validation_pv' => $idDecisionValidationPv,
                'date_validation' => date('Y-m-d H:i:s'),
                'commentaire_validation_pv' => $commentaireValidation
            ];
            if (!$this->validationPvModel->creer($validationData)) {
                throw new OperationImpossibleException("Échec de l'enregistrement de la validation du PV.");
            }

            // Vérifier si tous les validateurs ont donné leur avis pour finaliser le PV
            $membresCommissionCount = $this->utilisateurModel->compterParCritere(['id_groupe_utilisateur' => 'GRP_COMMISSION', 'statut_compte' => 'actif']);
            // Ne pas inclure le rédacteur dans le compte des validateurs requis s'il ne valide pas son propre PV
            $validateursRequis = $membresCommissionCount - 1; // Si le rédacteur est membre de la commission

            $validationsActuelles = $this->validationPvModel->compterParCritere(['id_compte_rendu' => $idCompteRendu]);

            $finaliserPv = false;
            $statutFinalPv = 'PV_SOUMIS_VALID'; // Par défaut, reste en attente

            if ($validationsActuelles >= $validateursRequis) {
                // Tous les avis sont là. Vérifier si unanimité d'approbation ou s'il y a des demandes de modification.
                $decisions = $this->validationPvModel->trouverParCritere(['id_compte_rendu' => $idCompteRendu], ['id_decision_validation_pv']);
                $allApproved = true;
                foreach ($decisions as $dec) {
                    if ($dec['id_decision_validation_pv'] !== 'DV_PV_APPROUVE') {
                        $allApproved = false;
                        break;
                    }
                }

                if ($allApproved) {
                    $finaliserPv = true;
                    $statutFinalPv = 'PV_VALID'; // PV Validé
                } else {
                    // S'il y a des demandes de modification, le PV reste en statut soumis ou passe à un nouveau statut "modifs_requises"
                    // Pour l'instant, on le laisse en "SOUMIS_VALID" et le rédacteur devra le modifier et le resoumettre.
                    // Ou on peut ajouter un statut spécifique comme 'PV_MODIFS_REQUISES'
                    $finaliserPv = false; // Ne pas finaliser automatiquement si des modifs sont demandées
                    $statutFinalPv = 'PV_SOUMIS_VALID'; // Reste dans le même statut pour relecture par le rédacteur
                }
            }

            if ($finaliserPv) {
                $this->compteRenduModel->mettreAJourParIdentifiant($idCompteRendu, ['id_statut_pv' => $statutFinalPv]);

                // Gérer la génération du PV PDF et la notification à l'étudiant
                if ($statutFinalPv === 'PV_VALID') {
                    $generatedFilePath = $this->documentGenerator->genererPvValidation($idCompteRendu); // Appelle le service de génération
                    // Enregistrer le document généré dans la table document_genere
                    if ($generatedFilePath) {
                        $idDocumentGenere = $this->idGenerator->genererIdentifiantUnique('DOC');
                        $this->documentGenerator->getDocumentGenereModel()->creer([
                            'id_document_genere' => $idDocumentGenere,
                            'libelle_document' => "PV de validation pour {$pv['libelle_compte_rendu']}",
                            'chemin_fichier' => $generatedFilePath,
                            'id_type_document_ref' => 'DOC_PV', // ID de référence pour les PV
                            'numero_utilisateur_concerne' => $pv['id_rapport_etudiant'] ?
                                $this->rapportEtudiantModel->trouverParIdentifiant($pv['id_rapport_etudiant'])['numero_carte_etudiant'] : 'N/A', // Récupérer le numéro de l'étudiant
                            'id_entite_source' => $idCompteRendu,
                            'type_entite_source' => 'PV'
                        ]);

                        // Notifier l'étudiant concerné par le PV
                        if ($pv['id_rapport_etudiant']) { // Si PV individuel
                            $rapportEtudiantData = $this->rapportEtudiantModel->trouverParIdentifiant($pv['id_rapport_etudiant'], ['numero_carte_etudiant']);
                            if ($rapportEtudiantData) {
                                $this->notificationService->envoyerNotificationUtilisateur(
                                    $rapportEtudiantData['numero_carte_etudiant'],
                                    'PV_VALIDE',
                                    "Le PV de votre rapport '{$pv['libelle_compte_rendu']}' est maintenant validé et disponible."
                                );
                            }
                        }
                        // Pour les PV de session, notifier tous les étudiants des rapports concernés
                        if ($pv['type_pv'] === 'Session') {
                            $rapportsAffectes = $this->pvSessionRapportModel->trouverParCritere(['id_compte_rendu' => $idCompteRendu]);
                            foreach ($rapportsAffectes as $ra) {
                                $rapportEtudiantData = $this->rapportEtudiantModel->trouverParIdentifiant($ra['id_rapport_etudiant'], ['numero_carte_etudiant']);
                                if ($rapportEtudiantData) {
                                    $this->notificationService->envoyerNotificationUtilisateur(
                                        $rapportEtudiantData['numero_carte_etudiant'],
                                        'PV_VALIDE_SESSION',
                                        "Le PV de la session concernant votre rapport est validé."
                                    );
                                }
                            }
                        }
                    }
                }
            }

            $this->compteRenduModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $numeroEnseignantValidateur,
                'VALIDATION_PV',
                "PV '{$idCompteRendu}' validé avec la décision '{$decisionRef['libelle_decision_validation_pv']}'",
                $idCompteRendu,
                'CompteRendu'
            );
            return true;
        } catch (\Exception $e) {
            $this->compteRenduModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $numeroEnseignantValidateur,
                'ECHEC_VALIDATION_PV',
                "Erreur validation PV {$idCompteRendu}: " . $e->getMessage()
            );
            throw $e;
        }
    }
}