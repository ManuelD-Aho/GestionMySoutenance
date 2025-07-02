<?php
// src/Backend/Controller/Administration/SupervisionController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Config\Container;
use App\Backend\Service\Systeme\ServiceSystemeInterface;

class SupervisionController extends BaseController
{
    private ServiceSystemeInterface $serviceSysteme;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->serviceSysteme = $container->get(ServiceSystemeInterface::class);
    }

    public function showAuditLogs(): void
    {
        $this->requirePermission('TRAIT_ADMIN_SUPERVISION_AUDIT_VIEW');
        $logs = $this->supervisionService->consulterJournaux($_GET);
        $this->render('Administration/supervision_audit.php', [
            'title' => 'Journaux d\'Audit',
            'logs' => $logs
        ]);
    }

    // ... (Le reste du fichier est correct)
}