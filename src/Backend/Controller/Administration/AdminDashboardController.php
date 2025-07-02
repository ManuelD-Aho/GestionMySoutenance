<?php
// src/Backend/Controller/Administration/AdminDashboardController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface; // Ajout de la dépendance
use Exception;

/**
 * Gère l'affichage du tableau de bord principal de l'administrateur.
 * Fournit une vue d'ensemble de l'état du système avec des statistiques et des alertes.
 */
class AdminDashboardController extends BaseController
{
    private ServiceSupervisionInterface $supervisionService;
    private ServiceSystemeInterface $systemeService;

    public function __construct(
        ServiceSupervisionInterface $supervisionService,
        ServiceSystemeInterface $systemeService,
        ServiceSecuriteInterface $securiteService, // Injecté pour BaseController
        ServiceSupervisionInterface $baseSupervisionService // Injecté pour BaseController
    ) {
        parent::__construct($securiteService, $baseSupervisionService);
        $this->supervisionService = $supervisionService;
        $this->systemeService = $systemeService;
    }

    public function index(): void
    {
        // 1. Permission et rôle requis
        $this->requirePermission('TRAIT_ADMIN_DASHBOARD_ACCEDER');

        try {
            // 2. Logique de cache pour les statistiques
            // Une bonne pratique serait d'utiliser un vrai système de cache (Redis, Memcached, Fichiers).
            // Ici, nous simulons un cache simple basé sur la session avec une expiration de 5 minutes.
            $stats = null;
            $cacheKey = 'admin_dashboard_stats';
            $cacheDuration = 300; // 5 minutes

            if (isset($_SESSION[$cacheKey]) && (time() - $_SESSION[$cacheKey]['timestamp']) < $cacheDuration) {
                $stats = $_SESSION[$cacheKey]['data'];
            } else {
                $stats = $this->supervisionService->genererStatistiquesDashboardAdmin();
                $_SESSION[$cacheKey] = ['timestamp' => time(), 'data' => $stats];
            }

            // 3. Logique pour les alertes dynamiques
            $failedJobsThreshold = (int) $this->systemeService->getParametre('ALERT_THRESHOLD_FAILED_JOBS', 5);
            $alerts = [];
            if (isset($stats['queue']['failed']) && $stats['queue']['failed'] > $failedJobsThreshold) {
                $alerts[] = ['type' => 'error', 'message' => "Attention : {$stats['queue']['failed']} tâches asynchrones ont échoué, ce qui dépasse le seuil de {$failedJobsThreshold}."];
            }

            // 4. Les liens rapides sont codés en dur dans la vue selon votre réponse.

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