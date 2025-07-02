<?php
// src/Backend/Controller/Administration/AdminDashboardController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Util\FormValidator;
use Exception;

/**
 * Gère l'affichage du tableau de bord principal de l'administrateur.
 * Fournit une vue d'ensemble de l'état du système avec des statistiques et des alertes.
 */
class AdminDashboardController extends BaseController
{
    // Suppression de la déclaration de propriété $supervisionService
    // car elle est déjà disponible via BaseController::$supervisionService
    private ServiceSystemeInterface $systemeService;

    public function __construct(
        ServiceSupervisionInterface $supervisionService, // Injecté pour BaseController
        ServiceSystemeInterface $systemeService,
        ServiceSecuriteInterface $securiteService // Injecté pour BaseController
    ) {
        parent::__construct($securiteService, $supervisionService,);
        $this->systemeService = $systemeService;
    }

    public function index(): void
    {
        $this->requirePermission('TRAIT_ADMIN_DASHBOARD_ACCEDER');

        try {
            $stats = null;
            $cacheKey = 'admin_dashboard_stats';
            $cacheDuration = 300; // 5 minutes

            if (isset($_SESSION[$cacheKey]) && (time() - $_SESSION[$cacheKey]['timestamp']) < $cacheDuration) {
                $stats = $_SESSION[$cacheKey]['data'];
            } else {
                $stats = $this->supervisionService->genererStatistiquesDashboardAdmin();
                $_SESSION[$cacheKey] = ['timestamp' => time(), 'data' => $stats];
            }

            $failedJobsThreshold = (int) $this->systemeService->getParametre('ALERT_THRESHOLD_FAILED_JOBS', 5);
            $alerts = [];
            if (isset($stats['queue']['failed']) && $stats['queue']['failed'] > $failedJobsThreshold) {
                $alerts[] = ['type' => 'error', 'message' => "Attention : {$stats['queue']['failed']} tâches asynchrones ont échoué, ce qui dépasse le seuil de {$failedJobsThreshold}."];
            }

            $data = [
                'title' => 'Tableau de Bord Administrateur',
                'stats' => $stats,
                'alerts' => $alerts,
            ];
            $this->render('Administration/dashboard_admin', $data);

        } catch (Exception $e) {
            error_log("Erreur inattendue dans AdminDashboardController::index: " . $e->getMessage());
            $this->renderError(500, 'Impossible de charger le tableau de bord administrateur.');
        }
    }
}