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
use App\Backend\Model\SessionValidation;
use App\Backend\Model\SessionRapport;
use App\Backend\Model\Utilisateur;
use App\Backend\Service\Notification\ServiceNotificationInterface;
use App\Backend\Service\DocumentGenerator\ServiceDocumentGeneratorInterface;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGeneratorInterface;
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
    private SessionValidation $sessionValidationModel;
    private SessionRapport $sessionRapportModel;
    private Utilisateur $utilisateurModel;
    private ServiceNotificationInterface $notificationService;
    private ServiceDocumentGeneratorInterface $documentGenerator;
    private ServiceSupervisionAdminInterface $supervisionService;
    private IdentifiantGeneratorInterface $idGenerator;

    public function __construct(
        PDO $db,
        Affecter $affecterModel,
        VoteCommission $voteCommissionModel,
        CompteRendu $compteRenduModel,
        PvSessionRapport $pvSessionRapportModel,
        ValidationPv $validationPvModel,
        RapportEtudiant $rapportEtudiantModel,
        DecisionVoteRef $decisionVoteRefModel,
        DecisionValidationPvRef $decisionValidationPvRefModel,
        StatutRapportRef $statutRapportRefModel,
        SessionValidation $sessionValidationModel,
        SessionRapport $sessionRapportModel,
        Utilisateur $utilisateurModel,
        ServiceNotificationInterface $notificationService,
        ServiceDocumentGeneratorInterface $documentGenerator,
        ServiceSupervisionAdminInterface $supervisionService,
        IdentifiantGeneratorInterface $idGenerator
    ) {
        $this->affecterModel = $affecterModel;
        $this->voteCommissionModel = $voteCommissionModel;
        $this->compteRenduModel = $compteRenduModel;
        $this->pvSessionRapportModel = $pvSessionRapportModel;
        $this->validationPvModel = $validationPvModel;
        $this->rapportEtudiantModel = $rapportEtudiantModel;
        $this->decisionVoteRefModel = $decisionVoteRefModel;
        $this->decisionValidationPvRefModel = $decisionValidationPvRefModel;
        $this->statutRapportRefModel = $statutRapportRefModel;
        $this->sessionValidationModel = $sessionValidationModel;
        $this->sessionRapportModel = $sessionRapportModel;
        $this->utilisateurModel = $utilisateurModel;
        $this->notificationService = $notificationService;
        $this->documentGenerator = $documentGenerator;
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
    }

    public function creerSessionValidation(string $libelleSession, string $dateDebutSession, string $dateFinPrevue, ?string $numeroPresidentCommission = null, array $idsRapports = []): string
    {
        $this->sessionValidationModel->commencerTransaction();
        try {
            $idSession = $this->idGenerator->genererIdentifiantUnique('SESS');

            $data = [
                'id_session' => $idSession,
                'nom_session' => $libelleSession,
                'date_debut_session' => $dateDebutSession,
                'date_fin_prevue' => $dateFinPrevue,
                'statut_session' => 'planifiee',
                'id_president_session' => $numeroPresidentCommission
            ];

            if (!$this->sessionValidationModel->creer($data)) {
                throw new OperationImpossibleException("Échec de la création de la session de validation.");
            }

            if (!empty($idsRapports)) {
                foreach ($idsRapports as $idRapport) {
                    if (!$this->sessionRapportModel->creer(['id_session' => $idSession, 'id_rapport_etudiant' => $idRapport])) {
                        throw new OperationImpossibleException("Échec du rattachement du rapport {$idRapport} à la session {$idSession}.");
                    }
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
        } catch (\Exception $e) {
            $this->sessionValidationModel->annulerTransaction();
            throw $e;
        }
    }

    public function demarrerSession(string $idSession): bool
    {
        $session = $this->sessionValidationModel->trouverParIdentifiant($idSession);
        if (!$session) {
            throw new ElementNonTrouveException("Session de validation non trouvée.");
        }
        if ($session['statut_session'] !== 'planifiee') {
            throw new OperationImpossibleException("La session ne peut être démarrée que si son statut est 'planifiee'.");
        }

        $success = $this->sessionValidationModel->mettreAJourParIdentifiant($idSession, ['statut_session' => 'en_cours']);
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

    public function cloturerSession(string $idSession): bool
    {
        $session = $this->sessionValidationModel->trouverParIdentifiant($idSession);
        if (!$session) {
            throw new ElementNonTrouveException("Session de validation non trouvée.");
        }
        if ($session['statut_session'] !== 'en_cours') {
            throw new OperationImpossibleException("La session ne peut être clôturée que si son statut est 'en_cours'.");
        }

        $rapportsEnSession = $this->sessionRapportModel->trouverRapportsDansSession($idSession);
        foreach ($rapportsEnSession as $sr) {
            $rapport = $this->rapportEtudiantModel->trouverParIdentifiant($sr['id_rapport_etudiant'], ['id_statut_rapport']);
            if ($rapport && in_array($rapport['id_statut_rapport'], ['RAP_EN_COMM', 'RAP_BROUILLON', 'RAP_SOUMIS', 'RAP_NON_CONF'])) {
                throw new OperationImpossibleException("Tous les rapports de la session doivent avoir une décision finale avant de la clôturer. Rapport {$sr['id_rapport_etudiant']} n'est pas finalisé.");
            }
        }

        $success = $this->sessionValidationModel->mettreAJourParIdentifiant($idSession, ['statut_session' => 'cloturee']);
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

    public function listerSessionsValidation(array $criteres = []): array
    {
        return $this->sessionValidationModel->trouverParCritere($criteres);
    }

    public function prolongerSession(string $idSession, string $nouvelleDateFin): bool
    {
        $session = $this->sessionValidationModel->trouverParIdentifiant($idSession);
        if (!$session) {
            throw new ElementNonTrouveException("Session de validation non trouvée.");
        }
        if ($session['statut_session'] !== 'en_cours') {
            throw new OperationImpossibleException("Seule une session en cours peut être prolongée.");
        }

        $success = $this->sessionValidationModel->mettreAJourParIdentifiant($idSession, ['date_fin_prevue' => $nouvelleDateFin]);
        if ($success) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'PROLONGATION_SESSION',
                "Session '{$idSession}' prolongée jusqu'au {$nouvelleDateFin}",
                $idSession,
                'SessionValidation'
            );
        }
        return $success;
    }

    public function retirerRapportDeSession(string $idSession, string $idRapportEtudiant): bool
    {
        $session = $this->sessionValidationModel->trouverParIdentifiant($idSession);
        if (!$session) {
            throw new ElementNonTrouveException("Session de validation non trouvée.");
        }
        if ($session['statut_session'] !== 'en_cours') {
            throw new OperationImpossibleException("Un rapport ne peut être retiré que d'une session en cours.");
        }

        $this->sessionRapportModel->commencerTransaction();
        try {
            $this->sessionRapportModel->supprimerParClesInternes(['id_session' => $idSession, 'id_rapport_etudiant' => $idRapportEtudiant]);
            $this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapportEtudiant, ['id_statut_rapport' => 'RAP_CONF']);
            $this->sessionRapportModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'RETRAIT_RAPPORT_SESSION',
                "Rapport '{$idRapportEtudiant}' retiré de la session '{$idSession}'",
                $idSession,
                'SessionValidation'
            );
            return true;
        } catch (\Exception $e) {
            $this->sessionRapportModel->annulerTransaction();
            throw $e;
        }
    }

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

        if (in_array($idDecisionVote, ['DV_REFUSE', 'DV_DISCUSSION']) && empty($commentaireVote)) {
            throw new OperationImpossibleException("Un commentaire est obligatoire pour la décision '{$decisionRef['libelle_decision_vote']}'.");
        }

        $existingVote = $this->voteCommissionModel->trouverVoteUnique($idRapportEtudiant, $numeroEnseignant, $tourVote);
        if ($existingVote) {
            throw new OperationImpossibleException("L'enseignant a déjà voté pour ce rapport lors de ce tour de vote.");
        }

        $this->voteCommissionModel->commencerTransaction();
        try {
            $idVote = $this->idGenerator->genererIdentifiantUnique('VOTE');

            $data = [
                'id_vote' => $idVote,
                'id_rapport_etudiant' => $idRapportEtudiant,
                'numero_enseignant' => $numeroEnseignant,
                'id_decision_vote' => $idDecisionVote,
                'commentaire_vote' => $commentaireVote,
                'tour_vote' => $tourVote,
                'id_session' => $idSession
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
            $this->finaliserDecisionCommissionPourRapport($idRapportEtudiant);
            return true;
        } catch (\Exception $e) {
            $this->voteCommissionModel->annulerTransaction();
            throw $e;
        }
    }

    public function finaliserDecisionCommissionPourRapport(string $idRapportEtudiant): bool
    {
        $rapport = $this->rapportEtudiantModel->trouverParIdentifiant($idRapportEtudiant, ['id_statut_rapport']);
        if (!$rapport) {
            throw new ElementNonTrouveException("Rapport étudiant non trouvé.");
        }

        if (in_array($rapport['id_statut_rapport'], ['RAP_VALID', 'RAP_REFUSE', 'RAP_CORRECT'])) {
            return false;
        }

        $sessionRapport = $this->sessionRapportModel->trouverUnParCritere(['id_rapport_etudiant' => $idRapportEtudiant]);
        if (!$sessionRapport) return false;
        $session = $this->sessionValidationModel->trouverParIdentifiant($sessionRapport['id_session']);
        if (!$session) return false;
        $commissionMembersCount = $session['nombre_votants_requis'] ?? 4;

        $votes = $this->voteCommissionModel->trouverParCritere(['id_rapport_etudiant' => $idRapportEtudiant], ['id_decision_vote', 'tour_vote'], 'AND', 'tour_vote DESC');
        if (empty($votes)) return false;

        $currentTour = $votes[0]['tour_vote'];
        $votesThisTour = array_filter($votes, fn($v) => $v['tour_vote'] == $currentTour);

        if (count($votesThisTour) < $commissionMembersCount) {
            return false;
        }

        $decisionsCount = array_count_values(array_column($votesThisTour, 'id_decision_vote'));
        $finalStatutId = null;
        $decisionLibelle = '';

        if (count($decisionsCount) === 1) {
            $uniqueDecision = array_keys($decisionsCount)[0];
            $finalStatutId = match ($uniqueDecision) {
                'DV_APPROUVE' => 'RAP_VALID',
                'DV_REFUSE' => 'RAP_REFUSE',
                'DV_CORRECTIONS' => 'RAP_CORRECT',
                default => 'RAP_EN_COMM',
            };
            $decisionLibelle = $this->decisionVoteRefModel->trouverParIdentifiant($uniqueDecision)['libelle_decision_vote'] ?? 'Décision enregistrée';
        } else {
            $finalStatutId = 'RAP_EN_COMM';
            $decisionLibelle = 'Décision non unanime, délibération en cours';
        }

        if ($finalStatutId && $finalStatutId !== 'RAP_EN_COMM') {
            $success = $this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapportEtudiant, ['id_statut_rapport' => $finalStatutId]);
            if ($success) {
                $this->supervisionService->enregistrerAction(
                    'SYSTEM',
                    'FINALISATION_DECISION_RAPPORT',
                    "Décision finale pour rapport {$idRapportEtudiant}: {$decisionLibelle}",
                    $idRapportEtudiant,
                    'RapportEtudiant'
                );
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

    public function lancerNouveauTourVote(string $idRapportEtudiant): bool
    {
        $rapport = $this->rapportEtudiantModel->trouverParIdentifiant($idRapportEtudiant, ['id_statut_rapport']);
        if (!$rapport) {
            throw new ElementNonTrouveException("Rapport étudiant non trouvé.");
        }

        if (!in_array($rapport['id_statut_rapport'], ['RAP_EN_COMM'])) {
            throw new OperationImpossibleException("Un nouveau tour de vote ne peut être lancé que pour un rapport en délibération ou en commission.");
        }

        $latestVote = $this->voteCommissionModel->trouverParCritere(
            ['id_rapport_etudiant' => $idRapportEtudiant],
            ['tour_vote'],
            'AND',
            'tour_vote DESC',
            1
        );
        $currentTour = $latestVote[0]['tour_vote'] ?? 0;
        $newTour = $currentTour + 1;

        $this->supervisionService->enregistrerAction(
            $_SESSION['user_id'] ?? 'SYSTEM',
            'NOUVEAU_TOUR_VOTE',
            "Nouveau tour de vote ({$newTour}) lancé pour rapport {$idRapportEtudiant}",
            $idRapportEtudiant,
            'RapportEtudiant'
        );
        return true;
    }

    public function redigerOuMettreAJourPv(string $idRedacteur, string $libellePv, string $typePv, ?string $idRapportEtudiant = null, array $idsRapportsSession = [], ?string $idCompteRenduExistant = null): string
    {
        $this->compteRenduModel->commencerTransaction();
        try {
            $idCompteRendu = $idCompteRenduExistant;
            if (!$idCompteRendu) {
                $idCompteRendu = $this->idGenerator->genererIdentifiantUnique('PV');
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
                $data = [
                    'libelle_compte_rendu' => $libellePv,
                    'type_pv' => $typePv,
                    'id_redacteur' => $idRedacteur,
                ];
                if (!$this->compteRenduModel->mettreAJourParIdentifiant($idCompteRendu, $data)) {
                    throw new OperationImpossibleException("Échec de la mise à jour du PV.");
                }
            }

            if ($typePv === 'Session' && !empty($idsRapportsSession)) {
                $this->pvSessionRapportModel->supprimerParCritere(['id_compte_rendu' => $idCompteRendu]);
                foreach ($idsRapportsSession as $rapportId) {
                    if (!$this->pvSessionRapportModel->creer(['id_compte_rendu' => $idCompteRendu, 'id_rapport_etudiant' => $rapportId])) {
                        throw new OperationImpossibleException("Échec de la liaison du rapport {$rapportId} au PV de session {$idCompteRendu}.");
                    }
                }
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
        } catch (\Exception $e) {
            $this->compteRenduModel->annulerTransaction();
            throw $e;
        }
    }

    public function soumettrePvPourValidation(string $idCompteRendu): bool
    {
        $pv = $this->compteRenduModel->trouverParIdentifiant($idCompteRendu);
        if (!$pv) {
            throw new ElementNonTrouveException("Procès-verbal non trouvé.");
        }
        if ($pv['id_statut_pv'] !== 'PV_BROUILLON') {
            throw new OperationImpossibleException("Seul un PV en brouillon peut être soumis pour validation.");
        }

        $success = $this->compteRenduModel->mettreAJourParIdentifiant($idCompteRendu, ['id_statut_pv' => 'PV_SOUMIS_VALID']);
        if ($success) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'SOUMISSION_PV_VALIDATION',
                "PV '{$idCompteRendu}' soumis pour validation",
                $idCompteRendu,
                'CompteRendu'
            );
        }
        return $success;
    }

    public function validerOuRejeterPv(string $idCompteRendu, string $numeroEnseignantValidateur, string $idDecisionValidationPv, ?string $commentaireValidation): bool
    {
        $pv = $this->compteRenduModel->trouverParIdentifiant($idCompteRendu, ['id_statut_pv', 'id_redacteur']);
        if (!$pv) {
            throw new ElementNonTrouveException("Procès-verbal non trouvé.");
        }
        if ($pv['id_statut_pv'] !== 'PV_SOUMIS_VALID') {
            throw new OperationImpossibleException("Ce PV n'est pas en attente de validation.");
        }

        $this->compteRenduModel->commencerTransaction();
        try {
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

            $membresCommissionCount = $this->utilisateurModel->compterParCritere(['id_groupe_utilisateur' => 'GRP_COMMISSION', 'statut_compte' => 'actif']);
            $validateursRequis = $membresCommissionCount - 1;
            $validationsActuelles = $this->validationPvModel->compterParCritere(['id_compte_rendu' => $idCompteRendu]);

            if ($validationsActuelles >= $validateursRequis) {
                $decisions = $this->validationPvModel->trouverParCritere(['id_compte_rendu' => $idCompteRendu], ['id_decision_validation_pv']);
                $allApproved = !in_array('DV_PV_MODIF', array_column($decisions, 'id_decision_validation_pv'));

                if ($allApproved) {
                    $this->compteRenduModel->mettreAJourParIdentifiant($idCompteRendu, ['id_statut_pv' => 'PV_VALID']);
                    $this->documentGenerator->genererPvValidation($idCompteRendu);
                }
            }

            $this->compteRenduModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $numeroEnseignantValidateur,
                'VALIDATION_PV',
                "PV '{$idCompteRendu}' validé avec la décision '{$idDecisionValidationPv}'",
                $idCompteRendu,
                'CompteRendu'
            );
            return true;
        } catch (\Exception $e) {
            $this->compteRenduModel->annulerTransaction();
            throw $e;
        }
    }

    public function listerPvEnAttenteValidationParMembre(string $numeroEnseignant): array
    {
        $pvSoumis = $this->compteRenduModel->trouverParCritere(['id_statut_pv' => 'PV_SOUMIS_VALID']);
        $pvEnAttente = [];
        foreach ($pvSoumis as $pv) {
            if ($pv['id_redacteur'] === $numeroEnseignant) continue;
            $validationExistante = $this->validationPvModel->trouverValidationPvParCles($pv['id_compte_rendu'], $numeroEnseignant);
            if (!$validationExistante) {
                $pvEnAttente[] = $pv;
            }
        }
        return $pvEnAttente;
    }

    public function deleguerRedactionPv(string $idCompteRendu, string $ancienRedacteur, string $nouveauRedacteur): bool
    {
        $pv = $this->compteRenduModel->trouverParIdentifiant($idCompteRendu);
        if (!$pv || $pv['id_redacteur'] !== $ancienRedacteur) {
            throw new OperationImpossibleException("Seul le rédacteur actuel peut déléguer la rédaction.");
        }
        $success = $this->compteRenduModel->mettreAJourParIdentifiant($idCompteRendu, ['id_redacteur' => $nouveauRedacteur]);
        if ($success) {
            $this->supervisionService->enregistrerAction($ancienRedacteur, 'DELEGATION_REDACTION_PV', "Rédaction du PV {$idCompteRendu} déléguée à {$nouveauRedacteur}.");
        }
        return $success;
    }

    public function gererApprobationsPvBloquees(string $idCompteRendu, string $action, ?string $numeroPersonnelAction = null, ?string $commentaire = null): bool
    {
        $pv = $this->compteRenduModel->trouverParIdentifiant($idCompteRendu);
        if (!$pv) {
            throw new ElementNonTrouveException("PV non trouvé.");
        }

        if ($action === 'substitution') {
            $this->compteRenduModel->mettreAJourParIdentifiant($idCompteRendu, ['id_statut_pv' => 'PV_VALID']);
            $this->supervisionService->enregistrerAction($numeroPersonnelAction, 'APPROBATION_PV_SUBSTITUTION', "PV {$idCompteRendu} approuvé par substitution. Commentaire: {$commentaire}");
            return true;
        } elseif ($action === 'quorum') {
            $this->compteRenduModel->mettreAJourParIdentifiant($idCompteRendu, ['id_statut_pv' => 'PV_VALID']);
            $this->supervisionService->enregistrerAction($numeroPersonnelAction, 'VALIDATION_PV_QUORUM', "PV {$idCompteRendu} validé par quorum.");
            return true;
        }
        return false;
    }
}