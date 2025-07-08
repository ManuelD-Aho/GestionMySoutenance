<?php
// Emplacement: src/Backend/Controller/Administration/LoggerController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Logger\ServiceLoggerInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;
use Exception;

class LoggerController extends BaseController
{
    private ServiceLoggerInterface $loggerService;

    public function __construct(
        ServiceLoggerInterface $loggerService,
        ServiceSecuriteInterface $securiteService,
        ServiceSupervisionInterface $supervisionService,
        FormValidator $validator
    ) {
        parent::__construct($securiteService, $supervisionService, $validator);
        $this->loggerService = $loggerService;
    }

    public function index(): void
    {
        $this->requirePermission('TRAIT_ADMIN_SUPERVISION_LOGS_ACCES');
        try {
            $this->render('Administration/logs/index', [
                'title' => 'Gestion des Logs Système',
                'log_files' => $this->loggerService->getLogFiles(),
                'stats' => $this->loggerService->getLogStats(),
                'recent_errors' => $this->loggerService->getRecentErrors(10),
                'csrf_token' => $this->generateCsrfToken('log_action_form')
            ]);
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect('/admin/supervision');
        }
    }

    public function viewLogFile(string $file): void
    {
        $this->requirePermission('TRAIT_ADMIN_SUPERVISION_LOGS_ACCES');
        try {
            $filters = $this->getGetData();
            $page = max(1, (int)($filters['page'] ?? 1));
            $result = $this->loggerService->getLogContent($file, $filters, $page);

            $this->render('Administration/logs/view', [
                'title' => "Log: $file",
                'file_name' => $file,
                'log_entries' => $result['entries'],
                'pagination' => $result['pagination'],
                'filters' => $filters,
                'file_info' => $this->loggerService->getLogFileInfo($file)
            ]);
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect('/admin/supervision/logs');
        }
    }

    public function clearLogFile(string $file): void
    {
        $this->requirePermission('TRAIT_ADMIN_SUPERVISION_MAINTENANCE_ACCES');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('log_action_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/supervision/logs');
            return;
        }

        try {
            $this->loggerService->clearLogFile($file);
            $this->addFlashMessage('success', "Fichier de log {$file} vidé.");
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        }
        $this->redirect('/admin/supervision/logs');
    }

    public function downloadLogFile(string $file): void
    {
        $this->requirePermission('TRAIT_ADMIN_SUPERVISION_LOGS_ACCES');
        try {
            $this->loggerService->downloadLogFile($file);
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect('/admin/supervision/logs');
        }
    }
}