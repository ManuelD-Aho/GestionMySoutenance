<?php

declare(strict_types=1);

namespace App\Backend\Controller\Administration;

use App\Config\Container;
use App\Backend\Controller\BaseController;
use App\Backend\Service\Interface\ReportingServiceInterface;

class ReportingController extends BaseController
{
    private ReportingServiceInterface $reportingService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->reportingService = $container->get(ReportingServiceInterface::class);
    }

    public function index(): void
    {
        $this->checkPermission('TRAIT_ADMIN_REPORTING_ACCEDER');
        $this->render('Administration/reporting_admin', [
            'page_title' => 'Rapports & Statistiques'
        ]);
    }

    public function filterReports(): void
    {
        $this->checkPermission('TRAIT_ADMIN_REPORTING_ACCEDER');
        // Implémentation future
        $this->redirect('/dashboard/admin/reporting');
    }
}