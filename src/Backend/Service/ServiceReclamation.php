<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Model\Reclamation;
use App\Backend\Service\Interface\ReclamationServiceInterface;
use App\Backend\Service\Interface\AuditServiceInterface;
use App\Backend\Service\Interface\NotificationServiceInterface;
use App\Backend\Service\Interface\IdentifiantGeneratorInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceReclamation implements ReclamationServiceInterface
{
    private PDO $pdo;
    private Reclamation $reclamationModel;
    private AuditServiceInterface $auditService;
    private NotificationServiceInterface $notificationService;
    private IdentifiantGeneratorInterface $identifiantGenerator;

    public function __construct(
        PDO $pdo,
        Reclamation $reclamationModel,
        AuditServiceInterface $auditService,
        NotificationServiceInterface $notificationService,
        IdentifiantGeneratorInterface $identifiantGenerator
    ) {
        $this->pdo = $pdo;
        $this->reclamationModel = $reclamationModel;
        $this->auditService = $auditService;
        $this->notificationService = $notificationService;
        $this->identifiantGenerator = $identifiantGenerator;
    }

    public function soumettreReclamation(string $numeroEtudiant, string $sujet, string $description): string
    {
        $idReclamation = $this->identifiantGenerator->generer('REC');
        $donnees = [
            'id_reclamation' => $idReclamation,
            'numero_carte_etudiant' => $numeroEtudiant,
            'sujet_reclamation' => $sujet,
            'description_reclamation' => $description,
            'date_creation' => (new \DateTime())->format('Y-m-d H:i:s'),
            'id_statut_reclamation' => 'REC_OUVERTE'
        ];

        $this->reclamationModel->creer($donnees);
        $this->auditService->enregistrerAction($numeroEtudiant, 'CLAIM_SUBMITTED', $idReclamation, 'Reclamation', $donnees);
        $this->notificationService->envoyerAGroupe('GRP_SCOLARITE', 'NEW_CLAIM_SUBMITTED_TPL', ['claim_id' => $idReclamation, 'subject' => $sujet]);

        return $idReclamation;
    }

    public function repondreAReclamation(string $idReclamation, string $idAgent, string $reponse): bool
    {
        $reclamation = $this->recupererOuEchouer($idReclamation);

        $this->pdo->beginTransaction();
        try {
            // Pour une conversation complète, on aurait une table de messages.
            // Ici, on ajoute la réponse à la description pour garder un historique simple.
            $nouvelleDescription = $reclamation['description_reclamation'] . "\n\n--- Réponse de {$idAgent} le " . date('Y-m-d H:i:s') . " ---\n" . $reponse;

            $this->reclamationModel->mettreAJourParIdentifiant($idReclamation, [
                'description_reclamation' => $nouvelleDescription,
                'id_agent_assigne' => $idAgent,
                'id_statut_reclamation' => 'REC_EN_COURS'
            ]);

            $this->auditService->enregistrerAction($idAgent, 'CLAIM_ANSWERED', $idReclamation, 'Reclamation', ['reponse' => $reponse]);
            $this->notificationService->envoyerAUtilisateur($reclamation['numero_carte_etudiant'], 'CLAIM_ANSWERED_TPL', ['claim_id' => $idReclamation]);

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function changerStatutReclamation(string $idReclamation, string $nouveauStatut): bool
    {
        $reclamation = $this->recupererOuEchouer($idReclamation);
        $resultat = $this->reclamationModel->mettreAJourParIdentifiant($idReclamation, ['id_statut_reclamation' => $nouveauStatut]);
        $this->auditService->enregistrerAction('ManuelD-Aho', 'CLAIM_STATUS_CHANGED', $idReclamation, 'Reclamation', ['nouveau_statut' => $nouveauStatut]);
        $this->notificationService->envoyerAUtilisateur($reclamation['numero_carte_etudiant'], 'CLAIM_STATUS_CHANGED_TPL', ['claim_id' => $idReclamation, 'status' => $nouveauStatut]);
        return $resultat;
    }

    public function assignerReclamation(string $idReclamation, string $idAgent): bool
    {
        $this->recupererOuEchouer($idReclamation);
        $resultat = $this->reclamationModel->mettreAJourParIdentifiant($idReclamation, ['id_agent_assigne' => $idAgent]);
        $this->auditService->enregistrerAction('ManuelD-Aho', 'CLAIM_ASSIGNED', $idReclamation, 'Reclamation', ['agent' => $idAgent]);
        $this->notificationService->envoyerAUtilisateur($idAgent, 'CLAIM_ASSIGNED_TO_YOU_TPL', ['claim_id' => $idReclamation]);
        return $resultat;
    }

    public function listerReclamations(array $filtres = []): array
    {
        return $this->reclamationModel->trouverParCritere($filtres, ['*'], 'AND', 'date_creation DESC');
    }

    public function getReclamationParId(string $idReclamation): ?array
    {
        return $this->reclamationModel->trouverParIdentifiant($idReclamation);
    }

    public function escaladerReclamation(string $idReclamation, string $niveau): bool
    {
        $reclamation = $this->recupererOuEchouer($idReclamation);
        $resultat = $this->reclamationModel->mettreAJourParIdentifiant($idReclamation, ['niveau_escalade' => $niveau]);
        $this->auditService->enregistrerAction('ManuelD-Aho', 'CLAIM_ESCALATED', $idReclamation, 'Reclamation', ['niveau' => $niveau]);
        $this->notificationService->envoyerAGroupe('GRP_ADMIN', 'CLAIM_ESCALATED_TPL', ['claim_id' => $idReclamation, 'niveau' => $niveau]);
        return $resultat;
    }

    public function cloturerReclamation(string $idReclamation): bool
    {
        $reclamation = $this->recupererOuEchouer($idReclamation);
        if ($reclamation['id_statut_reclamation'] !== 'REC_RESOLUE') {
            throw new OperationImpossibleException("Une réclamation ne peut être clôturée que si elle est à l'état 'Résolue'.");
        }
        return $this->changerStatutReclamation($idReclamation, 'REC_CLOTUREE');
    }

    private function recupererOuEchouer(string $idReclamation): array
    {
        $reclamation = $this->reclamationModel->trouverParIdentifiant($idReclamation);
        if (!$reclamation) {
            throw new ElementNonTrouveException("La réclamation avec l'ID '{$idReclamation}' n'a pas été trouvée.");
        }
        return $reclamation;
    }
}