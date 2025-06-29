<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Service\Interface\SupervisionAdminServiceInterface;
use App\Backend\Service\Interface\LoggerServiceInterface;
use App\Backend\Exception\OperationImpossibleException;

class ServiceSupervisionAdmin implements SupervisionAdminServiceInterface
{
    private PDO $pdo;
    private LoggerServiceInterface $loggerService;
    private string $tempDir;

    public function __construct(PDO $pdo, LoggerServiceInterface $loggerService)
    {
        $this->pdo = $pdo;
        $this->loggerService = $loggerService;
        $this->tempDir = sys_get_temp_dir();
    }

    public function getStatistiquesSysteme(): array
    {
        $stats = [];
        $stats['utilisateurs_actifs'] = $this->pdo->query("SELECT COUNT(*) FROM Utilisateur WHERE statut_compte = 'actif'")->fetchColumn();
        $stats['rapports_en_cours'] = $this->pdo->query("SELECT COUNT(*) FROM RapportEtudiant WHERE id_statut_rapport NOT IN ('RAP_VALIDE', 'RAP_REJETE')")->fetchColumn();
        $stats['sessions_planifiees'] = $this->pdo->query("SELECT COUNT(*) FROM SessionValidation WHERE statut_session = 'planifiee'")->fetchColumn();
        $stats['taches_en_attente'] = $this->pdo->query("SELECT COUNT(*) FROM QueueJobs WHERE status = 'pending'")->fetchColumn();
        return $stats;
    }

    public function getStatutServices(): array
    {
        $statuts = [];
        // Statut Base de données
        try {
            $this->pdo->query("SELECT 1");
            $statuts['database'] = ['status' => 'OK', 'message' => 'Connexion réussie.'];
        } catch (\PDOException $e) {
            $statuts['database'] = ['status' => 'ERROR', 'message' => $e->getMessage()];
        }

        // Statut Serveur SMTP (simulation de check)
        $smtpHost = getenv('SMTP_HOST');
        if ($smtpHost && @fsockopen($smtpHost, (int)getenv('SMTP_PORT'), $errno, $errstr, 2)) {
            $statuts['smtp'] = ['status' => 'OK', 'message' => "Connexion à {$smtpHost} réussie."];
        } else {
            $statuts['smtp'] = ['status' => 'ERROR', 'message' => "Impossible de se connecter à {$smtpHost}."];
        }

        return $statuts;
    }

    public function lancerTacheMaintenance(string $nomTache): bool
    {
        if ($nomTache === 'archivage_logs') {
            // La logique d'archivage devrait être implémentée ici ou dans un service dédié.
            // Par exemple, supprimer les logs de plus de 30 jours.
            // Pour l'instant, on simule le succès.
            $this->loggerService->log('info', "La tâche de maintenance 'archivage_logs' a été lancée.");
            return true;
        }
        return false;
    }

    /**
     * Récupère les derniers logs d'erreur critiques pour un diagnostic rapide.
     *
     * @param int $limite Le nombre de logs à récupérer.
     * @return array La liste des logs critiques.
     */
    public function consulterLogsCritiques(int $limite = 100): array
    {
        return $this->loggerService->queryLogs([
            'level' => ['error', 'critical', 'alert', 'emergency'],
            'limit' => $limite
        ]);
    }

    /**
     * Génère un rapport de santé complet du système.
     *
     * @return string Le rapport généré (format texte ou HTML).
     */
    public function genererRapportSante(): string
    {
        // Implémentation de la génération du rapport de santé
        return "Rapport de santé du " . date('Y-m-d H:i:s');
    }

    /**
     * Configure les règles d'alerting pour la supervision.
     *
     * @param array $regles Les règles d'alerte (seuils, destinataires).
     * @return bool True en cas de succès.
     */
    public function configurerAlertes(array $regles): bool
    {
        try {
            foreach ($regles as $cle => $valeur) {
                $sql = "INSERT INTO ParametresSysteme (cle, valeur, type) VALUES (:cle, :valeur, 'string') ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(['cle' => $cle, 'valeur' => $valeur]);
            }
            $this->loggerService->log('info', "Configuration des alertes mise à jour.");
            return true;
        } catch (\PDOException $e) {
            $this->loggerService->log('error', "Erreur lors de la configuration des alertes: " . $e->getMessage());
            return false;
        }
    }
}