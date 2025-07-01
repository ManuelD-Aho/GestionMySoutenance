<?php
// src/Backend/Controller/Administration/SupervisionController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Config\Container;

class SupervisionController extends BaseController
{
    private ServiceSupervisionInterface $serviceSupervision;
    private ServiceSystemeInterface $serviceSysteme;

    public function __construct(
        Container $container,
        ServiceSecuriteInterface $serviceSecurite,
        ServiceSupervisionInterface $serviceSupervision,
        ServiceSystemeInterface $serviceSysteme
    ) {
        parent::__construct($container, $serviceSecurite);
        $this->serviceSupervision = $serviceSupervision;
        $this->serviceSysteme = $serviceSysteme;
    }

    /**
     * Affiche les journaux d'audit avec filtres et pagination.
     */
    public function showAuditLogs(): void
    {
        $this->checkPermission('TRAIT_ADMIN_SUPERVISION_AUDIT_VIEW');
        // ... Logique de filtres et pagination ...
        $logs = $this->serviceSupervision->consulterJournaux($_GET);
        $this->render('Administration/supervision_audit.php', [
            'title' => 'Journaux d\'Audit',
            'logs' => $logs
        ]);
    }

    /**
     * Affiche les journaux d'erreurs du serveur.
     */
    public function showErrorLogs(): void
    {
        $this->checkPermission('TRAIT_ADMIN_SUPERVISION_ERRORS_VIEW');
        $logPath = $this->serviceSysteme->getParametre('PHP_ERROR_LOG_PATH');
        $logContent = $this->serviceSupervision->consulterJournauxErreurs($logPath);
        $this->render('Administration/supervision_errors.php', [
            'title' => 'Journaux d\'Erreurs',
            'logContent' => $logContent
        ]);
    }

    /**
     * Affiche l'état de la file d'attente des tâches asynchrones.
     */
    public function showQueueStatus(): void
    {
        $this->checkPermission('TRAIT_ADMIN_SUPERVISION_QUEUE_VIEW');
        $jobs = $this->serviceSupervision->listerTachesAsynchrones();
        $this->render('Administration/supervision_queue.php', [
            'title' => 'File d\'attente des Tâches',
            'jobs' => $jobs
        ]);
    }

    /**
     * Gère une action sur une tâche de la file d'attente (relancer, supprimer).
     */
    public function manageQueueTask(string $id, string $action): void
    {
        $this->checkPermission('TRAIT_ADMIN_SUPERVISION_QUEUE_MANAGE');
        $this->serviceSupervision->gererTacheAsynchrone($id, $action);
        $this->redirect('/admin/supervision/queue');
    }
}