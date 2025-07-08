<?php
// Emplacement: src/Backend/Controller/Administration/AdminDashboardController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Util\FormValidator;
use Exception;

class AdminDashboardController extends BaseController
{
    private ServiceSystemeInterface $systemeService;

    public function __construct(
        ServiceSupervisionInterface $supervisionService,
        ServiceSystemeInterface $systemeService,
        ServiceSecuriteInterface $securiteService,
        FormValidator $validator
    ) {
        parent::__construct($securiteService, $supervisionService, $validator);
        $this->systemeService = $systemeService;
    }

    public function index(): void
    {
        $this->requirePermission('TRAIT_ACCES_DASHBOARD_ADMIN');

        try {
            $stats = $this->getCachedDashboardStats();
            $alerts = $this->generateSystemAlerts($stats);

            $data = [
                'title' => 'Tableau de Bord Administrateur',
                'stats' => $stats,
                'alerts' => $alerts,
                'shortcuts' => $this->getAdminShortcuts(),
                'system_info' => [
                    'version' => $this->systemeService->getParametre('APP_VERSION', '1.0.0'),
                    'environment' => $this->systemeService->getParametre('APP_ENV', 'production'),
                ]
            ];

            $this->render('Administration/dashboard_admin', $data);

        } catch (Exception $e) {
            error_log("Erreur AdminDashboard: " . $e->getMessage());
            $this->addFlashMessage('error', 'Impossible de charger le tableau de bord.');
            $this->redirect('/dashboard');
        }
    }

    private function getCachedDashboardStats(): array
    {
        $cacheKey = 'admin_dashboard_stats';
        $cacheDuration = 300; // 5 minutes

        if (isset($_SESSION[$cacheKey]) && (time() - $_SESSION[$cacheKey]['timestamp']) < $cacheDuration) {
            return $_SESSION[$cacheKey]['data'];
        }

        $stats = $this->supervisionService->genererStatistiquesDashboardAdmin();
        $_SESSION[$cacheKey] = ['timestamp' => time(), 'data' => $stats];
        return $stats;
    }

    private function generateSystemAlerts(array $stats): array
    {
        $alerts = [];
        if (($stats['utilisateurs']['bloque'] ?? 0) > 0) {
            $alerts[] = ['type' => 'warning', 'message' => "{$stats['utilisateurs']['bloque']} utilisateur(s) bloqué(s).", 'action' => '/admin/utilisateurs?statut_compte=bloque'];
        }
        if (($stats['queue']['failed'] ?? 0) > 0) {
            $alerts[] = ['type' => 'error', 'message' => "{$stats['queue']['failed']} tâche(s) en échec dans la file d'attente.", 'action' => '/admin/supervision#queue-tab'];
        }
        return $alerts;
    }

    private function getAdminShortcuts(): array
    {
        return [
            ['title' => 'Gestion Utilisateurs', 'icon' => 'fas fa-users', 'url' => '/admin/utilisateurs'],
            ['title' => 'Configuration', 'icon' => 'fas fa-cogs', 'url' => '/admin/configuration'],
            ['title' => 'Supervision', 'icon' => 'fas fa-chart-line', 'url' => '/admin/supervision'],
            ['title' => 'Reporting', 'icon' => 'fas fa-file-chart-pie', 'url' => '/admin/reporting'],
        ];
    }
}