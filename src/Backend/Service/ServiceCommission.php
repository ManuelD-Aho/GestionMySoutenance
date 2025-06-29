<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Model\SessionValidation;
use App\Backend\Model\SessionRapport;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\Affecter;
use App\Backend\Service\Interface\CommissionServiceInterface;
use App\Backend\Service\Interface\AuditServiceInterface;
use App\Backend\Service\Interface\NotificationServiceInterface;
use App\Backend\Service\Interface\IdentifiantGeneratorInterface;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceCommission implements CommissionServiceInterface
{
    private PDO $pdo;
    private SessionValidation $sessionValidationModel;
    private SessionRapport $sessionRapportModel;
    private RapportEtudiant $rapportEtudiantModel;
    private Affecter $affecterModel;
    private AuditServiceInterface $auditService;
    private NotificationServiceInterface $notificationService;
    private IdentifiantGeneratorInterface $identifiantGenerator;

    public function __construct(
        PDO $pdo,
        SessionValidation $sessionValidationModel,
        SessionRapport $sessionRapportModel,
        RapportEtudiant $rapportEtudiantModel,
        Affecter $affecterModel,
        AuditServiceInterface $auditService,
        NotificationServiceInterface $notificationService,
        IdentifiantGeneratorInterface $identifiantGenerator
    ) {
        $this->pdo = $pdo;
        $this->sessionValidationModel = $sessionValidationModel;
        $this->sessionRapportModel = $sessionRapportModel;
        $this->rapportEtudiantModel = $rapportEtudiantModel;
        $this->affecterModel = $affecterModel;
        $this->auditService = $auditService;
        $this->notificationService = $notificationService;
        $this->identifiantGenerator = $identifiantGenerator;
    }

    public function creerSessionValidation(array $donnees): string
    {
        $idSession = $this->identifiantGenerator->generer('SES');
        $donnees['id_session'] = $idSession;
        $membres = $donnees['membres'] ?? [];
        unset($donnees['membres']);

        $this->pdo->beginTransaction();
        try {
            $this->sessionValidationModel->creer($donnees);

            foreach ($membres as $membre) {
                $this->affecterModel->creer([
                    'numero_enseignant' => $membre['id'],
                    'id_session' => $idSession,
                    'id_statut_jury' => $membre['statut_jury']
                ]);
            }

            $this->auditService->enregistrerAction('SYSTEM', 'SESSION_CREATE', $idSession, 'SessionValidation', $donnees);
            $this->notificationService->envoyerAGroupe('GRP_COMMISSION', 'SESSION_CREATED_TPL', ['session_name' => $donnees['nom_session']]);
            $this->pdo->commit();

            return $idSession;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function mettreAJourSession(string $idSession, array $donnees): bool
    {
        $session = $this->recupererOuEchouer($idSession);
        $resultat = $this->sessionValidationModel->mettreAJourParIdentifiant($idSession, $donnees);
        $this->auditService->enregistrerAction('SYSTEM', 'SESSION_UPDATE', $idSession, 'SessionValidation', ['anciennes_valeurs' => $session, 'nouvelles_valeurs' => $donnees]);
        return $resultat;
    }

    public function cloturerSession(string $idSession): bool
    {
        $session = $this->recupererOuEchouer($idSession);
        $rapportsEnCours = $this->sessionRapportModel->compterParCritere(['id_session' => $idSession]);
        if ($rapportsEnCours > 0) {
            throw new OperationImpossibleException("Impossible de clôturer la session : {$rapportsEnCours} rapport(s) sont encore en cours de traitement.");
        }

        $resultat = $this->sessionValidationModel->mettreAJourParIdentifiant($idSession, ['id_statut_session' => 'SES_CLOTUREE']);
        $this->auditService->enregistrerAction('SYSTEM', 'SESSION_CLOSE', $idSession, 'SessionValidation');
        return $resultat;
    }

    public function ajouterRapportASession(string $idSession, string $idRapport): bool
    {
        $session = $this->recupererOuEchouer($idSession);
        if ($session['id_statut_session'] !== 'SES_PLANIFIEE') {
            throw new OperationImpossibleException("Un rapport ne peut être ajouté qu'à une session planifiée.");
        }

        $rapport = $this->rapportEtudiantModel->trouverParIdentifiant($idRapport);
        if (!$rapport || $rapport['id_statut_rapport'] !== 'RAP_CONFORME') {
            throw new OperationImpossibleException("Le rapport n'est pas à l'état 'Conforme' et ne peut être ajouté.");
        }

        $this->pdo->beginTransaction();
        try {
            $this->sessionRapportModel->creer(['id_session' => $idSession, 'id_rapport_etudiant' => $idRapport]);
            $this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapport, ['id_statut_rapport' => 'RAP_EN_COMMISSION']);
            $this->auditService->enregistrerAction('SYSTEM', 'SESSION_ADD_REPORT', $idSession, 'SessionValidation', ['id_rapport' => $idRapport]);
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function retirerRapportDeSession(string $idSession, string $idRapport): bool
    {
        $this->recupererOuEchouer($idSession);
        $this->pdo->beginTransaction();
        try {
            $this->sessionRapportModel->supprimerParCles(['id_session' => $idSession, 'id_rapport_etudiant' => $idRapport]);
            $this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapport, ['id_statut_rapport' => 'RAP_CONFORME']);
            $this->auditService->enregistrerAction('SYSTEM', 'SESSION_REMOVE_REPORT', $idSession, 'SessionValidation', ['id_rapport' => $idRapport]);
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function listerSessions(array $filtres = []): array
    {
        return $this->sessionValidationModel->trouverParCritere($filtres, ['*'], 'AND', 'date_session_debut DESC');
    }

    public function recupererSessionParId(string $idSession): ?array
    {
        return $this->sessionValidationModel->trouverParIdentifiant($idSession);
    }

    public function lancerVotePourRapport(string $idRapport, string $idSession): bool
    {
        $this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapport, ['id_statut_rapport' => 'RAP_VOTE_EN_COURS']);
        $this->auditService->enregistrerAction('SYSTEM', 'SESSION_START_VOTE', $idSession, 'SessionValidation', ['id_rapport' => $idRapport]);
        return true;
    }

    public function lancerNouveauTourDeVote(string $idRapport, string $idSession): bool
    {
        $this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapport, ['tour_vote' => new \PDOStatement()]); // Simule une incrémentation
        $this->auditService->enregistrerAction('SYSTEM', 'SESSION_NEW_VOTE_ROUND', $idSession, 'SessionValidation', ['id_rapport' => $idRapport]);
        return true;
    }

    private function recupererOuEchouer(string $idSession): array
    {
        $session = $this->recupererSessionParId($idSession);
        if (!$session) {
            throw new ElementNonTrouveException("La session de validation avec l'ID '{$idSession}' n'a pas été trouvée.");
        }
        return $session;
    }
}