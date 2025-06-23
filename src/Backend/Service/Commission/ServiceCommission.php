<?php
namespace App\Backend\Service\Commission;

use PDO;
use App\Backend\Model\SessionValidation;
use App\Backend\Model\SessionRapport;
use App\Backend\Model\VoteCommission;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\CompteRendu;
use App\Backend\Model\ValidationPv;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGeneratorInterface;
use App\Backend\Service\Notification\ServiceNotificationInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceCommission implements ServiceCommissionInterface
{
    private SessionValidation $sessionValidationModel;
    private SessionRapport $sessionRapportModel;
    private VoteCommission $voteCommissionModel;
    private RapportEtudiant $rapportEtudiantModel;
    private CompteRendu $compteRenduModel;
    private ValidationPv $validationPvModel;
    private IdentifiantGeneratorInterface $idGenerator;
    private ServiceNotificationInterface $notificationService;

    public function __construct(PDO $db, IdentifiantGeneratorInterface $idGenerator, ServiceNotificationInterface $notificationService)
    {
        $this->sessionValidationModel = new SessionValidation($db);
        $this->sessionRapportModel = new SessionRapport($db);
        $this->voteCommissionModel = new VoteCommission($db);
        $this->rapportEtudiantModel = new RapportEtudiant($db);
        $this->compteRenduModel = new CompteRendu($db);
        $this->validationPvModel = new ValidationPv($db);
        $this->idGenerator = $idGenerator;
        $this->notificationService = $notificationService;
    }

    public function creerSessionValidation(string $nomSession, string $idPresident, array $idsRapports, ?string $dateFinPrevue = null): string
    {
        $this->sessionValidationModel->commencerTransaction();
        try {
            $idSession = $this->idGenerator->generate('session_validation');
            $this->sessionValidationModel->creer([
                'id_session' => $idSession,
                'nom_session' => $nomSession,
                'id_president_session' => $idPresident,
                'date_fin_prevue' => $dateFinPrevue,
                'statut_session' => 'planifiee'
            ]);
            foreach ($idsRapports as $idRapport) {
                $this->sessionRapportModel->creer(['id_session' => $idSession, 'id_rapport_etudiant' => $idRapport]);
            }
            $this->sessionValidationModel->validerTransaction();
            return $idSession;
        } catch (\Exception $e) {
            $this->sessionValidationModel->annulerTransaction();
            throw $e;
        }
    }

    public function demarrerSession(string $idSession): bool
    {
        return $this->sessionValidationModel->mettreAJourParIdentifiant($idSession, ['statut_session' => 'en_cours']);
    }

    public function cloturerSession(string $idSession): bool
    {
        $session = $this->sessionValidationModel->trouverParIdentifiant($idSession);
        if (!$session || $session['statut_session'] !== 'en_cours') {
            throw new OperationImpossibleException("La session ne peut être clôturée.");
        }
        return $this->sessionValidationModel->mettreAJourParIdentifiant($idSession, ['statut_session' => 'cloturee']);
    }

    public function listerSessionsValidation(array $criteres = []): array
    {
        return $this->sessionValidationModel->trouverParCritere($criteres);
    }

    public function recupererRapportsPourSession(string $idSession): array
    {
        $liaisons = $this->sessionRapportModel->trouverParCritere(['id_session' => $idSession]);
        $idsRapports = array_column($liaisons, 'id_rapport_etudiant');
        if (empty($idsRapports)) return [];
        return $this->rapportEtudiantModel->trouverParCritere(['id_rapport_etudiant' => ['operator' => 'in', 'values' => $idsRapports]]);
    }

    public function enregistrerVote(string $idSession, string $idRapport, string $idEnseignant, string $idDecision, ?string $commentaire, int $tour): bool
    {
        $idVote = $this->idGenerator->generate('vote_commission');
        $success = (bool) $this->voteCommissionModel->creer([
            'id_vote' => $idVote,
            'id_session' => $idSession,
            'id_rapport_etudiant' => $idRapport,
            'numero_enseignant' => $idEnseignant,
            'id_decision_vote' => $idDecision,
            'commentaire_vote' => $commentaire,
            'tour_vote' => $tour
        ]);
        if ($success) {
            $this->verifierConsensus($idSession, $idRapport, $tour);
        }
        return $success;
    }

    private function verifierConsensus(string $idSession, string $idRapport, int $tour): void
    {
        $session = $this->sessionValidationModel->trouverParIdentifiant($idSession);
        $votes = $this->voteCommissionModel->trouverParCritere(['id_session' => $idSession, 'id_rapport_etudiant' => $idRapport, 'tour_vote' => $tour]);
        if (count($votes) >= $session['nombre_votants_requis']) {
            $decisions = array_column($votes, 'id_decision_vote');
            if (count(array_unique($decisions)) === 1) {
                $decisionFinale = $decisions[0];
                $statutFinal = ($decisionFinale === 'DV_APPROUVE') ? 'RAP_VALID' : 'RAP_REFUSE';
                $this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapport, ['id_statut_rapport' => $statutFinal]);
            } else {
                $this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapport, ['id_statut_rapport' => 'RAP_EN_DELIBERATION']);
            }
        }
    }

    public function lancerNouveauTourDeVote(string $idSession, string $idRapport): bool
    {
        $dernierVote = $this->voteCommissionModel->trouverUnParCritere(['id_session' => $idSession, 'id_rapport_etudiant' => $idRapport], ['*'], 'AND', 'tour_vote DESC');
        $nouveauTour = ($dernierVote['tour_vote'] ?? 0) + 1;
        // La logique de réouverture des votes se fait en enregistrant de nouveaux votes avec le numéro de tour incrémenté.
        return true;
    }

    public function redigerPv(string $idSession, string $idRedacteur, string $contenu): string
    {
        $idPv = $this->idGenerator->generate('compte_rendu');
        $this->compteRenduModel->creer([
            'id_compte_rendu' => $idPv,
            'type_pv' => 'Session',
            'libelle_compte_rendu' => $contenu,
            'id_statut_pv' => 'PV_BROUILLON',
            'id_redacteur' => $idRedacteur
        ]);
        return $idPv;
    }

    public function approuverPv(string $idCompteRendu, string $idApprobateur): bool
    {
        return (bool) $this->validationPvModel->creer([
            'id_compte_rendu' => $idCompteRendu,
            'numero_enseignant' => $idApprobateur,
            'id_decision_validation_pv' => 'DV_PV_APPROUVE',
            'date_validation' => date('Y-m-d H:i:s')
        ]);
    }
}