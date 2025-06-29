<?php

declare(strict_types=1);

namespace App\Backend\Controller\Administration;

use App\Config\Container;
use App\Backend\Controller\BaseController;
use App\Backend\Service\Interface\SupervisionAdminServiceInterface;
use App\Backend\Service\Interface\AuditServiceInterface;

class SupervisionController extends BaseController
{
    private SupervisionAdminServiceInterface $supervisionService;
    private AuditServiceInterface $auditService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->supervisionService = $container->get(SupervisionAdminServiceInterface::class);
        $this->auditService = $container->get(AuditServiceInterface::class);
    }

    public function index(): void
    {
        $this->checkPermission('TRAIT_ADMIN_SUPERVISION_ACCEDER');
        $this->render('Administration/Supervision/index', [
            'page_title' => 'Supervision du Système'
        ]);
    }

    public function showAuditLogs(): void
    {
        $this->checkPermission('TRAIT_ADMIN_SUPERVISION_JOURNAUX_AUDIT_VOIR');
        $logs = $this->auditService->listerLogs(100);
        $this->render('Administration/Supervision/journaux_audit', [
            'page_title' => 'Journaux d\'Audit',
            'logs' => $logs
        ]);
    }

    public function showWorkflowTraces(): void
    {
        $this->checkPermission('TRAIT_ADMIN_SUPERVISION_SUIVI_WORKFLOWS_VOIR');
        // Implémentation future
        $this->redirect('/dashboard/admin/supervision');
    }

    public function showMaintenanceTools(): void
    {
        $this->checkPermission('TRAIT_ADMIN_SUPERVISION_MAINTENANCE_ACCEDER');
        // Implémentation future
        $this->redirect('/dashboard/admin/supervision');
    }

    public function archivePv(): void
    {
        $this->checkPermission('TRAIT_ADMIN_SUPERVISION_ARCHIVAGE_PV');
        // Implémentation future
        $this->redirect('/dashboard/admin/supervision');
    }
}