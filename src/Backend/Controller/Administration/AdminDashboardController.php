<?php
// src/Backend/Controller/Administration/AdminDashboardController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Config\Container;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Exception\PermissionException;

class AdminDashboardController extends BaseController
{
    // La propriété est déjà dans BaseController, pas besoin de la redéclarer ici.

    public function __construct(Container $container)
    {
        parent::__construct($container);
        // Le service de supervision est déjà initialisé dans le parent.
    }

    public function index(): void
    {
        $this->requirePermission('TRAIT_ADMIN_DASHBOARD_ACCEDER');

        try {
            $stats = $this->supervisionService->genererStatistiquesDashboardAdmin();
            $data = [
                'title' => 'Tableau de Bord Administrateur',
                'stats' => $stats,
            ];
            $this->render('Administration/dashboard_admin', $data);
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'Une erreur est survenue lors du chargement du tableau de bord : ' . $e->getMessage());
            error_log("Erreur inattendue dans AdminDashboardController::index: " . $e->getMessage());
            $this->renderError(500, 'Impossible de charger le tableau de bord.');
        }
    }
}