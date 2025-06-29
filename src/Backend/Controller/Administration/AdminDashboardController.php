<?php

declare(strict_types=1);

namespace App\Backend\Controller\Administration;

use App\Config\Container;
use App\Backend\Controller\BaseController;
use App\Backend\Service\Interface\ReportingServiceInterface;
use App\Backend\Service\Interface\SupervisionAdminServiceInterface;

class AdminDashboardController extends BaseController
{
    private ReportingServiceInterface $reportingService;
    private SupervisionAdminServiceInterface $supervisionService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->reportingService = $container->get(ReportingServiceInterface::class);
        $this->supervisionService = $container->get(SupervisionAdminServiceInterface::class);
    }

    public function index(): void
    {
        $this->checkPermission('TRAIT_ADMIN_DASHBOARD_ACCEDER');

        try {
            $statistiquesRapports = $this->reportingService->genererRapportTauxValidation([]);
            $statistiquesUtilisation = $this->reportingService->genererStatistiquesUtilisation('dernier_mois');
            $globalRapportsStats = $this->supervisionService->getStatistiquesSysteme();

            $this->render('Administration/dashboard_admin', [
                'page_title' => 'Tableau de Bord Administrateur',
                'statistiques_rapports' => $statistiquesRapports,
                'statistiques_utilisation' => $statistiquesUtilisation,
                'global_rapports_stats' => $globalRapportsStats
            ]);
        } catch (\Exception $e) {
            $this->addFlashMessage('error', "Erreur lors du chargement du tableau de bord: " . $e->getMessage());
            $this->redirect('/dashboard');
        }
    }
}