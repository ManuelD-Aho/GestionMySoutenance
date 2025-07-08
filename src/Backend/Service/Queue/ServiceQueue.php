<?php

namespace App\Backend\Service\Queue;

use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use PDO;

class ServiceQueue implements ServiceQueueInterface
{
    private PDO $db;
    private ServiceSupervisionInterface $supervisionService;

    public function __construct(PDO $db, ServiceSupervisionInterface $supervisionService)
    {
        $this->db = $db;
        $this->supervisionService = $supervisionService;
    }

    public function getQueueStatus(): array
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM queue_jobs WHERE status = 'processing'");
        $processing = (int)$stmt->fetchColumn();

        return [
            'is_running' => $processing > 0,
            'processing_jobs' => $processing,
            'last_processed' => $this->getLastProcessedTime()
        ];
    }

    public function getQueueStats(): array
    {
        $stmt = $this->db->query("
            SELECT 
                status, 
                COUNT(*) as count,
                AVG(TIMESTAMPDIFF(SECOND, created_at, completed_at)) as avg_duration
            FROM queue_jobs
            GROUP BY status
        ");

        $stats = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stats[$row['status']] = [
                'count' => (int)$row['count'],
                'avg_duration' => round((float)$row['avg_duration'], 2)
            ];
        }

        return $stats;
    }

    public function getRecentJobs(int $limit): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM queue_jobs 
            ORDER BY created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getJobTypes(): array
    {
        $stmt = $this->db->query("SELECT DISTINCT job_name FROM queue_jobs");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function processQueue(int $limit): array
    {
        // Simulation du traitement de la queue
        $processed = 0;
        $failed = 0;

        $stmt = $this->db->prepare("
            SELECT * FROM queue_jobs 
            WHERE status = 'pending' 
            ORDER BY created_at ASC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($jobs as $job) {
            // Marquer comme en cours
            $this->updateJobStatus($job['id'], 'processing');

            // Simuler le traitement
            sleep(1);

            if (rand(0, 10) > 8) { // 20% de chance d'échec
                $this->updateJobStatus($job['id'], 'failed', 'Erreur simulée');
                $failed++;
            } else {
                $this->updateJobStatus($job['id'], 'completed');
                $processed++;
            }
        }

        return ['processed' => $processed, 'failed' => $failed];
    }

    public function clearQueue(string $type): array
    {
        $whereClause = "";
        switch ($type) {
            case 'completed':
                $whereClause = "WHERE status = 'completed'";
                break;
            case 'failed':
                $whereClause = "WHERE status = 'failed'";
                break;
            case 'all':
                $whereClause = "WHERE status IN ('completed', 'failed')";
                break;
            default:
                return ['cleared' => 0];
        }

        $stmt = $this->db->exec("DELETE FROM queue_jobs $whereClause");
        return ['cleared' => $stmt];
    }

    public function getJobDetails(string $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM queue_jobs WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getJobLogs(string $id): array
    {
        // Si vous avez une table de logs de jobs
        return [];
    }

    public function retryJob(string $id): string
    {
        $job = $this->getJobDetails($id);
        if (!$job) {
            throw new \RuntimeException("Job non trouvé");
        }

        $newJobId = $this->addJob([
            'job_name' => $job['job_name'],
            'payload' => json_decode($job['payload'], true)
        ]);

        $this->updateJobStatus($id, 'failed_retried');

        return $newJobId;
    }

    public function cancelJob(string $id): bool
    {
        return $this->updateJobStatus($id, 'cancelled');
    }

    public function addJob(array $jobData): string
    {
        $stmt = $this->db->prepare("
            INSERT INTO queue_jobs (job_name, payload, status, attempts, created_at)
            VALUES (:job_name, :payload, 'pending', 0, NOW())
        ");

        $stmt->execute([
            'job_name' => $jobData['job_name'],
            'payload' => json_encode($jobData['payload'])
        ]);

        return $this->db->lastInsertId();
    }

    public function getConfiguration(): array
    {
        return [
            'max_workers' => 3,
            'timeout' => 60,
            'retry_delay' => 300,
            'max_attempts' => 5,
            'cleanup_after_days' => 90
        ];
    }

    public function updateConfiguration(array $config): bool
    {
        // Sauvegarder la configuration en base ou fichier
        return true;
    }

    public function getAvailableJobTypes(): array
    {
        return [
            'SEND_EMAIL' => 'Envoyer un email',
            'GENERATE_REPORT' => 'Générer un rapport',
            'BACKUP_DATABASE' => 'Sauvegarder la base de données',
            'CLEANUP_LOGS' => 'Nettoyer les logs'
        ];
    }

    private function getLastProcessedTime(): ?string
    {
        $stmt = $this->db->query("
            SELECT MAX(completed_at) FROM queue_jobs 
            WHERE status = 'completed'
        ");
        return $stmt->fetchColumn() ?: null;
    }

    private function updateJobStatus(string $id, string $status, ?string $errorMessage = null): bool
    {
        $sql = "UPDATE queue_jobs SET status = :status, error_message = :error_message";

        if ($status === 'processing') {
            $sql .= ", started_at = NOW()";
        }

        if (in_array($status, ['completed', 'failed', 'cancelled'])) {
            $sql .= ", completed_at = NOW()";
        }

        $sql .= " WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'status' => $status,
            'error_message' => $errorMessage
        ]);
    }
}