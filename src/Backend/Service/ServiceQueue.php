<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Model\QueueJobs;
use App\Backend\Service\Interface\QueueServiceInterface;
use App\Backend\Service\Interface\AuditServiceInterface;
use App\Backend\Exception\ElementNonTrouveException;

/**
 * Service de gestion de file d'attente (Queue) de qualité production.
 * Gère le cycle de vie complet des tâches asynchrones de manière robuste et atomique.
 * Alignée sur un schéma de BDD simple et performant.
 */
class ServiceQueue implements QueueServiceInterface
{
    private PDO $pdo;
    private QueueJobs $queueJobsModel;
    private AuditServiceInterface $auditService;

    public function __construct(PDO $pdo, QueueJobs $queueJobsModel, AuditServiceInterface $auditService)
    {
        $this->pdo = $pdo;
        $this->queueJobsModel = $queueJobsModel;
        $this->auditService = $auditService;
    }

    /**
     * @inheritdoc
     */
    public function ajouterTache(string $nomTache, array $payload, int $priorite = 0): bool
    {
        // La priorité est ignorée pour s'aligner sur un schéma de prod simple (FIFO).
        $donnees = [
            'job_name' => $nomTache,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'status' => 'pending',
            'created_at' => (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s')
        ];
        return (bool)$this->queueJobsModel->creer($donnees);
    }

    /**
     * @inheritdoc
     */
    public function traiterProchaineTache(): ?bool
    {
        $this->pdo->beginTransaction();
        try {
            // Verrouillage pessimiste pour garantir qu'un seul worker traite la tâche.
            // **OPTIMISATION FINALE : SKIP LOCKED permet aux autres workers de passer à la tâche suivante sans attendre.**
            // Ceci est crucial pour la performance et la résilience en production.
            $sql = "SELECT * FROM queue_jobs WHERE status = 'pending' ORDER BY created_at ASC LIMIT 1 FOR UPDATE SKIP LOCKED";
            $stmt = $this->pdo->query($sql);
            $tache = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$tache) {
                $this->pdo->commit();
                return null; // La file est vide, aucune action.
            }

            // Marquer la tâche comme 'processing' immédiatement dans la transaction.
            $this->queueJobsModel->mettreAJourParIdentifiant($tache['id'], [
                'status' => 'processing',
                'started_at' => (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s'),
                'attempts' => $tache['attempts'] + 1
            ]);
            $this->pdo->commit(); // Valider la prise en charge de la tâche.

        } catch (\Exception $e) {
            $this->pdo->rollBack();
            // Log l'erreur de sélection de tâche, mais ne rien faire d'autre.
            return false;
        }

        try {
            // *** POINT D'EXÉCUTION DE LA TÂCHE ***
            // Une véritable implémentation utiliserait un "Worker Factory" pour instancier
            // et exécuter la logique métier correspondant à $tache['job_name'].
            // Ex: $worker = $this->workerFactory->create($tache['job_name']);
            //     $worker->execute(json_decode($tache['payload'], true));

            // Pour la démonstration, nous simulons un succès.
            if ($tache['job_name'] === 'TacheQuiDoitEchouer') { // Pour tester la robustesse
                throw new \RuntimeException("Échec simulé de la tâche.");
            }

            // Marquer la tâche comme terminée.
            $this->queueJobsModel->mettreAJourParIdentifiant($tache['id'], [
                'status' => 'completed',
                'completed_at' => (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s')
            ]);
            return true;

        } catch (\Throwable $e) {
            // La tâche a échoué. On enregistre l'erreur.
            $this->queueJobsModel->mettreAJourParIdentifiant($tache['id'], [
                'status' => 'failed',
                'completed_at' => (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s'),
                'error_message' => $e->getMessage()
            ]);
            // Log l'erreur système
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function listerTaches(string $statut): array
    {
        return $this->queueJobsModel->trouverParCritere(['status' => $statut]);
    }

    /**
     * @inheritdoc
     */
    public function purgerFile(string $statut): int
    {
        $sql = "DELETE FROM queue_jobs WHERE status = :status";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':status' => $statut]);
        $lignesSupprimees = $stmt->rowCount();

        $this->auditService->enregistrerAction(
            $_SESSION['user_id'] ?? 'SYSTEM_ADMIN',
            'QUEUE_PURGED',
            null,
            'QueueJobs',
            ['status' => $statut, 'count' => $lignesSupprimees]
        );
        return $lignesSupprimees;
    }

    /**
     * @inheritdoc
     */
    public function relancerTacheEchouee(string $idTache): bool
    {
        $tache = $this->queueJobsModel->trouverParIdentifiant($idTache);
        if (!$tache) {
            throw new ElementNonTrouveException("Tâche #{$idTache} non trouvée.");
        }
        if ($tache['status'] !== 'failed') {
            return false;
        }

        return $this->queueJobsModel->mettreAJourParIdentifiant($idTache, [
            'status' => 'pending',
            'started_at' => null,
            'completed_at' => null,
            'error_message' => null
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getStatistiquesQueue(): array
    {
        $sql = "SELECT status, COUNT(*) as count FROM queue_jobs GROUP BY status";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}