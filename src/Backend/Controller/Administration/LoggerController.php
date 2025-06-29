<?php

declare(strict_types=1);

namespace App\Backend\Controller\Administration;

use App\Config\Container;
use App\Backend\Controller\BaseController;
use App\Backend\Service\Interface\LoggerServiceInterface;

class LoggerController extends BaseController
{
    private LoggerServiceInterface $loggerService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->loggerService = $container->get(LoggerServiceInterface::class);
    }

    public function index(): void
    {
        $this->checkPermission('TRAIT_ADMIN_LOGS_VOIR');
        $logs = $this->loggerService->queryLogs(['limit' => 200]);
        $this->render('Administration/Supervision/logs', [
            'page_title' => 'Journaux Applicatifs',
            'logs' => $logs,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function clearLogs(): void
    {
        $this->checkPermission('TRAIT_ADMIN_LOGS_PURGER');
        // Implémentation future
        $this->redirect('/dashboard/admin/supervision/logs');
    }
}