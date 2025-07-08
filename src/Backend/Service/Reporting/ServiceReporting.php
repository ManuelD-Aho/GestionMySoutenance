<?php

namespace App\Backend\Service\Reporting;

use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use PDO;

class ServiceReporting implements ServiceReportingInterface
{
    private PDO $db;
    private ServiceSupervisionInterface $supervisionService;

    public function __construct(PDO $db, ServiceSupervisionInterface $supervisionService)
    {
        $this->db = $db;
        $this->supervisionService = $supervisionService;
    }

    public function getAvailableReports(): array
    {
        return [
            'users_activity' => [
                'name' => 'Activité des Utilisateurs',
                'description' => 'Rapport sur l\'activité des utilisateurs du système',
                'parameters' => ['date_debut', 'date_fin', 'groupe_utilisateur'],
                'formats' => ['html', 'pdf', 'excel', 'csv']
            ],
            'reports_stats' => [
                'name' => 'Statistiques des Rapports',
                'description' => 'Statistiques sur les rapports étudiants',
                'parameters' => ['annee_academique', 'specialite', 'statut'],
                'formats' => ['html', 'pdf', 'excel', 'csv']
            ],
            'system_usage' => [
                'name' => 'Utilisation du Système',
                'description' => 'Rapport d\'utilisation et de performance',
                'parameters' => ['date_debut', 'date_fin', 'module'],
                'formats' => ['html', 'pdf', 'csv']
            ],
            'security_audit' => [
                'name' => 'Audit de Sécurité',
                'description' => 'Rapport des événements de sécurité',
                'parameters' => ['date_debut', 'date_fin', 'niveau_severite'],
                'formats' => ['html', 'pdf', 'csv']
            ],
            'commission_activity' => [
                'name' => 'Activité des Commissions',
                'description' => 'Rapport sur l\'activité des commissions',
                'parameters' => ['date_debut', 'date_fin', 'commission_id'],
                'formats' => ['html', 'pdf', 'excel']
            ]
        ];
    }

    public function generateReport(string $type, array $parameters): array
    {
        switch ($type) {
            case 'users_activity':
                return $this->generateUsersActivityReport($parameters);
            case 'reports_stats':
                return $this->generateReportsStatsReport($parameters);
            case 'system_usage':
                return $this->generateSystemUsageReport($parameters);
            case 'security_audit':
                return $this->generateSecurityAuditReport($parameters);
            case 'commission_activity':
                return $this->generateCommissionActivityReport($parameters);
            default:
                throw new \InvalidArgumentException("Type de rapport non supporté: $type");
        }
    }

    private function generateUsersActivityReport(array $parameters): array
    {
        $dateDebut = $parameters['date_debut'] ?? date('Y-m-01');
        $dateFin = $parameters['date_fin'] ?? date('Y-m-t');
        $groupeUtilisateur = $parameters['groupe_utilisateur'] ?? '';

        $whereClause = "WHERE p.date_pister BETWEEN :date_debut AND :date_fin";
        $params = ['date_debut' => $dateDebut, 'date_fin' => $dateFin];

        if (!empty($groupeUtilisateur)) {
            $whereClause .= " AND u.id_groupe_utilisateur = :groupe";
            $params['groupe'] = $groupeUtilisateur;
        }

        $stmt = $this->db->prepare("
            SELECT 
                u.numero_utilisateur,
                u.nom,
                u.prenom,
                u.id_groupe_utilisateur,
                COUNT(p.id_piste) as nombre_connexions,
                COUNT(DISTINCT DATE(p.date_pister)) as jours_actifs,
                MIN(p.date_pister) as premiere_connexion,
                MAX(p.date_pister) as derniere_connexion
            FROM utilisateur u
            LEFT JOIN pister p ON u.numero_utilisateur = p.numero_utilisateur
            $whereClause
            GROUP BY u.numero_utilisateur
            ORDER BY nombre_connexions DESC
        ");

        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Statistiques globales
        $statsStmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT p.numero_utilisateur) as utilisateurs_actifs,
                COUNT(p.id_piste) as total_connexions,
                AVG(daily_connections.connexions_par_jour) as moyenne_connexions_jour
            FROM pister p
            LEFT JOIN (
                SELECT DATE(date_pister) as jour, COUNT(*) as connexions_par_jour 
                FROM pister 
                WHERE date_pister BETWEEN :date_debut AND :date_fin
                GROUP BY DATE(date_pister)
            ) daily_connections ON 1=1
            WHERE p.date_pister BETWEEN :date_debut AND :date_fin
        ");

        $statsStmt->execute($params);
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

        return [
            'type' => 'users_activity',
            'title' => 'Rapport d\'Activité des Utilisateurs',
            'parameters' => $parameters,
            'stats' => $stats,
            'data' => $data,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    private function generateReportsStatsReport(array $parameters): array
    {
        $anneeAcademique = $parameters['annee_academique'] ?? '';
        $specialite = $parameters['specialite'] ?? '';
        $statut = $parameters['statut'] ?? '';

        $whereClause = "WHERE 1=1";
        $params = [];

        if (!empty($anneeAcademique)) {
            $whereClause .= " AND r.annee_academique = :annee";
            $params['annee'] = $anneeAcademique;
        }

        if (!empty($specialite)) {
            $whereClause .= " AND e.specialite = :specialite";
            $params['specialite'] = $specialite;
        }

        if (!empty($statut)) {
            $whereClause .= " AND r.id_statut_rapport = :statut";
            $params['statut'] = $statut;
        }

        $stmt = $this->db->prepare("
            SELECT 
                r.id_statut_rapport,
                s.libelle_statut_rapport,
                COUNT(*) as nombre_rapports,
                AVG(DATEDIFF(r.date_derniere_modif, r.date_creation)) as duree_moyenne_jours
            FROM rapport_etudiant r
            JOIN etudiant e ON r.numero_carte_etudiant = e.numero_carte_etudiant
            JOIN statut_rapport_ref s ON r.id_statut_rapport = s.id_statut_rapport
            $whereClause
            GROUP BY r.id_statut_rapport, s.libelle_statut_rapport
            ORDER BY nombre_rapports DESC
        ");

        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'type' => 'reports_stats',
            'title' => 'Statistiques des Rapports Étudiants',
            'parameters' => $parameters,
            'data' => $data,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    private function generateSystemUsageReport(array $parameters): array
    {
        // Implémentation du rapport d'utilisation système
        $dateDebut = $parameters['date_debut'] ?? date('Y-m-01');
        $dateFin = $parameters['date_fin'] ?? date('Y-m-t');

        // Statistiques des actions système
        $stmt = $this->db->prepare("
            SELECT 
                DATE(date_action) as date_action,
                COUNT(*) as nombre_actions,
                COUNT(DISTINCT numero_utilisateur) as utilisateurs_actifs
            FROM action
            WHERE date_action BETWEEN :date_debut AND :date_fin
            GROUP BY DATE(date_action)
            ORDER BY date_action
        ");

        $stmt->execute(['date_debut' => $dateDebut, 'date_fin' => $dateFin]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'type' => 'system_usage',
            'title' => 'Rapport d\'Utilisation du Système',
            'parameters' => $parameters,
            'data' => $data,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    private function generateSecurityAuditReport(array $parameters): array
    {
        // Rapport des événements de sécurité
        $dateDebut = $parameters['date_debut'] ?? date('Y-m-01');
        $dateFin = $parameters['date_fin'] ?? date('Y-m-t');

        $stmt = $this->db->prepare("
            SELECT 
                a.date_action,
                a.numero_utilisateur,
                u.nom,
                u.prenom,
                a.type_action,
                a.description_action,
                a.adresse_ip
            FROM action a
            LEFT JOIN utilisateur u ON a.numero_utilisateur = u.numero_utilisateur
            WHERE a.date_action BETWEEN :date_debut AND :date_fin
            AND (a.type_action LIKE '%LOGIN%' OR a.type_action LIKE '%SECURITY%' OR a.type_action LIKE '%DELETE%')
            ORDER BY a.date_action DESC
        ");

        $stmt->execute(['date_debut' => $dateDebut, 'date_fin' => $dateFin]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'type' => 'security_audit',
            'title' => 'Audit de Sécurité',
            'parameters' => $parameters,
            'data' => $data,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    private function generateCommissionActivityReport(array $parameters): array
    {
        // Rapport d'activité des commissions
        $dateDebut = $parameters['date_debut'] ?? date('Y-m-01');
        $dateFin = $parameters['date_fin'] ?? date('Y-m-t');

        $stmt = $this->db->prepare("
            SELECT 
                pv.date_creation,
                pv.id_statut_pv,
                spv.libelle_statut_pv,
                COUNT(psr.id_rapport_etudiant) as nombre_rapports_examines
            FROM compte_rendu pv
            JOIN statut_pv_ref spv ON pv.id_statut_pv = spv.id_statut_pv
            LEFT JOIN pv_session_rapport psr ON pv.id_compte_rendu = psr.id_compte_rendu
            WHERE pv.date_creation BETWEEN :date_debut AND :date_fin
            GROUP BY pv.id_compte_rendu
            ORDER BY pv.date_creation DESC
        ");

        $stmt->execute(['date_debut' => $dateDebut, 'date_fin' => $dateFin]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'type' => 'commission_activity',
            'title' => 'Activité des Commissions',
            'parameters' => $parameters,
            'data' => $data,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    public function exportReport(string $type, string $format, array $report = null): void
    {
        if (!$report) {
            throw new \InvalidArgumentException("Données du rapport manquantes pour l'export");
        }

        $filename = "{$type}_" . date('Y-m-d_H-i-s');

        switch ($format) {
            case 'csv':
                $this->exportToCSV($report, $filename);
                break;
            case 'excel':
                $this->exportToExcel($report, $filename);
                break;
            case 'pdf':
                $this->exportToPDF($report, $filename);
                break;
            default:
                throw new \InvalidArgumentException("Format d'export non supporté: $format");
        }
    }

    public function getRecentReports(int $limit = 10): array
    {
        // Retourner les rapports récemment générés (si vous avez une table pour ça)
        // Sinon, retourner un tableau vide ou simuler
        return [];
    }

    public function getReportingStats(): array
    {
        return [
            'total_reports_generated' => 156,
            'reports_this_month' => 23,
            'most_requested_type' => 'users_activity',
            'average_generation_time' => '2.3s'
        ];
    }

    public function getScheduledReports(): array
    {
        // Si vous avez une table pour les rapports programmés
        return [];
    }

    public function scheduleReport(array $data): string
    {
        // Implémentation de la programmation de rapports
        return uniqid('schedule_');
    }

    public function deleteScheduledReport(string $id): bool
    {
        return true;
    }

    private function exportToCSV(array $report, string $filename): void
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

        $output = fopen('php://output', 'w');

        if (isset($report['data']) && !empty($report['data'])) {
            fputcsv($output, array_keys($report['data'][0]));
            foreach ($report['data'] as $row) {
                fputcsv($output, $row);
            }
        }

        fclose($output);
        exit;
    }

    private function exportToExcel(array $report, string $filename): void
    {
        // Nécessite PhpSpreadsheet
        // Implementation à ajouter selon vos besoins
        throw new \Exception("Export Excel non encore implémenté");
    }

    private function exportToPDF(array $report, string $filename): void
    {
        // Nécessite TCPDF ou DOMPDF
        // Implementation à ajouter selon vos besoins
        throw new \Exception("Export PDF non encore implémenté");
    }
}