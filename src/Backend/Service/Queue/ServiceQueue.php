<?php

namespace App\Backend\Service\Queue;

use PDO;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\Email\ServiceEmailInterface;
use App\Backend\Service\DocumentAdministratif\ServiceDocumentAdministratifInterface;
use App\Backend\Exception\OperationImpossibleException;

class ServiceQueue implements ServiceQueueInterface
{
    private PDO $db;
    private ServiceSupervisionAdminInterface $supervisionService;
    private ServiceEmailInterface $emailService;
    private ServiceDocumentAdministratifInterface $documentAdminService;

    public function __construct(
        PDO $db,
        ServiceSupervisionAdminInterface $supervisionService,
        ServiceEmailInterface $emailService,
        ServiceDocumentAdministratifInterface $documentAdminService
    ) {
        $this->db = $db;
        $this->supervisionService = $supervisionService;
        $this->emailService = $emailService;
        $this->documentAdminService = $documentAdminService;
    }

    public function ajouterTache(string $jobName, array $payload): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO queue_jobs (job_name, payload, status, created_at) VALUES (:job_name, :payload, 'pending', NOW())"
        );
        $stmt->bindParam(':job_name', $jobName);
        $stmt->bindParam(':payload', json_encode($payload));
        return $stmt->execute();
    }

    public function traiterProchaineTache(): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT * FROM queue_jobs WHERE status = 'pending' ORDER BY created_at ASC LIMIT 1 FOR UPDATE SKIP LOCKED");
            $stmt->execute();
            $job = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$job) {
                $this->db->commit();
                return false;
            }

            $updateStmt = $this->db->prepare("UPDATE queue_jobs SET status = 'processing', started_at = NOW(), attempts = attempts + 1 WHERE id = :id");
            $updateStmt->bindParam(':id', $job['id']);
            $updateStmt->execute();
            $this->db->commit();

            $this->executerTache($job);

            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            if (isset($job['id'])) {
                $this->marquerTacheEchouee($job['id'], $e->getMessage());
            }
            error_log("Erreur traitement de la tâche: " . $e->getMessage());
            return false;
        }
    }

    private function executerTache(array $job): void
    {
        $payload = json_decode($job['payload'], true);

        try {
            switch ($job['job_name']) {
                case 'send_mass_email':
                    $this->handleSendMassEmail($payload);
                    break;
                case 'generate_mass_bulletins':
                    $this->handleGenerateMassBulletins($payload);
                    break;
                default:
                    throw new \InvalidArgumentException("Tâche '{$job['job_name']}' non reconnue.");
            }
            $this->marquerTacheTerminee($job['id']);
        } catch (\Exception $e) {
            $this->marquerTacheEchouee($job['id'], $e->getMessage());
        }
    }

    private function handleSendMassEmail(array $payload): void
    {
        $destinataires = $payload['destinataires'] ?? [];
        $sujet = $payload['sujet'] ?? 'Information';
        $corpsHtml = $payload['corps_html'] ?? '';
        $corpsTexte = $payload['corps_texte'] ?? '';

        if (empty($destinataires) || empty($corpsHtml)) {
            throw new \InvalidArgumentException("Données manquantes pour l'envoi d'email en masse.");
        }

        foreach ($destinataires as $email) {
            $emailData = [
                'destinataire_email' => $email,
                'sujet' => $sujet,
                'corps_html' => $corpsHtml,
                'corps_texte' => $corpsTexte,
            ];
            try {
                $this->emailService->envoyerEmail($emailData);
                // Petite pause pour ne pas surcharger le serveur SMTP
                sleep(1);
            } catch (\Exception $e) {
                error_log("Échec de l'envoi à {$email} dans la tâche de masse: " . $e->getMessage());
            }
        }
    }

    private function handleGenerateMassBulletins(array $payload): void
    {
        $etudiants = $payload['etudiants'] ?? [];
        $idAnneeAcademique = $payload['id_annee_academique'] ?? null;

        if (empty($etudiants) || $idAnneeAcademique === null) {
            throw new \InvalidArgumentException("Données manquantes pour la génération de bulletins en masse.");
        }

        foreach ($etudiants as $numeroEtudiant) {
            try {
                $this->documentAdminService->genererBulletinNotes($numeroEtudiant, $idAnneeAcademique);
            } catch (\Exception $e) {
                error_log("Échec de la génération du bulletin pour {$numeroEtudiant} dans la tâche de masse: " . $e->getMessage());
            }
        }
    }

    private function marquerTacheTerminee(int $jobId): void
    {
        $stmt = $this->db->prepare("UPDATE queue_jobs SET status = 'completed', completed_at = NOW() WHERE id = :id");
        $stmt->bindParam(':id', $jobId);
        $stmt->execute();
    }

    private function marquerTacheEchouee(int $jobId, string $errorMessage): void
    {
        $stmt = $this->db->prepare("UPDATE queue_jobs SET status = 'failed', error_message = :error_message, completed_at = NOW() WHERE id = :id");
        $stmt->bindParam(':id', $jobId);
        $stmt->bindParam(':error_message', $errorMessage);
        $stmt->execute();
    }

    public function listerTachesEnAttente(array $filters = []): array
    {
        $sql = "SELECT * FROM queue_jobs";
        $whereClauses = [];
        $params = [];

        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                $whereClauses[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}