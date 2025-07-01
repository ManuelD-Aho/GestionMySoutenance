<?php
// src/Backend/Controller/Administration/AdminDashboardController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Config\Container;

class AdminDashboardController extends BaseController
{
    private ServiceSupervisionInterface $serviceSupervision;

    public function __construct(
        Container $container,
        ServiceSecuriteInterface $serviceSecurite,
        ServiceSupervisionInterface $serviceSupervision
    ) {
        parent::__construct($container, $serviceSecurite);
        $this->serviceSupervision = $serviceSupervision;
    }

    /**
     * Affiche le tableau de bord principal de l'administrateur.
     * Récupère et affiche les statistiques clés de la plateforme.
     */
    public function index(): void
    {
        $this->checkPermission('TRAIT_ADMIN_DASHBOARD_ACCEDER');

        try {
            $stats = $this->serviceSupervision->genererStatistiquesDashboardAdmin();
            $this->render('Administration/dashboard_admin.php', [
                'title' => 'Tableau de Bord Administrateur',
                'stats' => $stats,
                'flash' => $this->getFlashMessages()
            ]);
        } catch (\Exception $e) {
            error_log("Erreur Dashboard Admin: " . $e->getMessage());
            $this->setFlash('error', "Impossible de charger les statistiques du tableau de bord.");
            $this->render('Administration/dashboard_admin.php', [
                'title' => 'Tableau de Bord Administrateur',
                'stats' => [],
                'flash' => $this->getFlashMessages()
            ]);
        }
    }
}