<?php
// Emplacement: src/Backend/Controller/Administration/QueueController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Queue\ServiceQueueInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;
use Exception;

class QueueController extends BaseController
{
    private ServiceQueueInterface $queueService;

    public function __construct(
        ServiceQueueInterface $queueService,
        ServiceSecuriteInterface $securiteService,
        ServiceSupervisionInterface $supervisionService,
        FormValidator $validator
    ) {
        parent::__construct($securiteService, $supervisionService, $validator);
        $this->queueService = $queueService;
    }

    public function index(): void
    {
        $this->requirePermission('TRAIT_ADMIN_SUPERVISION_QUEUE_ACCES');
        try {
            $this->render('Administration/queue/index', [
                'title' => 'Gestion de la File d\'Attente',
                'status' => $this->queueService->getQueueStatus(),
                'stats' => $this->queueService->getQueueStats(),
                'recent_jobs' => $this->queueService->getRecentJobs(20),
                'csrf_token' => $this->generateCsrfToken('queue_action_form')
            ]);
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect('/admin/supervision');
        }
    }

    public function handleAction(): void
    {
        $this->requirePermission('TRAIT_ADMIN_SUPERVISION_QUEUE_GERER');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('queue_action_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/supervision/queue');
            return;
        }

        $action = $_POST['action'] ?? '';
        $id = $_POST['id'] ?? null;

        try {
            switch ($action) {
                case 'process':
                    $result = $this->queueService->processQueue((int)($_POST['limit'] ?? 10));
                    $this->addFlashMessage('success', "{$result['processed']} job(s) traité(s), {$result['failed']} en échec.");
                    break;
                case 'clear':
                    $result = $this->queueService->clearQueue($_POST['type'] ?? 'completed');
                    $this->addFlashMessage('success', "{$result['cleared']} job(s) supprimé(s).");
                    break;
                case 'retry':
                    $this->queueService->retryJob($id);
                    $this->addFlashMessage('success', "Job {$id} relancé.");
                    break;
                case 'cancel':
                    $this->queueService->cancelJob($id);
                    $this->addFlashMessage('success', "Job {$id} annulé.");
                    break;
                default:
                    throw new Exception("Action non valide.");
            }
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        }
        $this->redirect('/admin/supervision/queue');
    }
}