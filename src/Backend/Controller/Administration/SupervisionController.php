<?php
// src/Backend/Controller/Administration/SupervisionController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Util\FormValidator;

class SupervisionController extends BaseController
{
    public function __construct(
        ServiceSecuriteInterface $serviceSecurite,
        ServiceSupervisionInterface $serviceSupervision,
        FormValidator $formValidator
    ) {
        parent::__construct($serviceSecurite, $serviceSupervision, $formValidator);
    }

    /**
     * Affiche les journaux d'audit avec pagination.
     */
    public function showLogs(): void
    {
        $this->checkPermission('ADMIN_LOGS_READ');

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 25;
        $offset = ($page - 1) * $limit;

        try {
            $logs = $this->serviceSupervision->consulterJournaux([], $limit, $offset);
            $this->render('Administration/supervision.php', [
                'title' => 'Journaux d\'Audit',
                'logs' => $logs,
                'page' => $page,
                'limit' => $limit
            ]);
        } catch (\Exception $e) {
            $this->serviceSupervision->enregistrerAction('SYSTEM', 'LOGS_VIEW_ERROR', null, null, ['error' => $e->getMessage()]);
            $this->render('errors/500.php', ['error_message' => "Impossible de charger les journaux d'audit."]);
        }
    }

    /**
     * Affiche l'état de la file d'attente des tâches asynchrones.
     */
    public function showQueue(): void
    {
        $this->checkPermission('ADMIN_QUEUE_READ');

        try {
// Cette méthode devrait être implémentée dans le ServiceSupervision
// Pour l'exemple, nous simulons la récupération depuis la base.
            $queueModel = new \App\Backend\Model\GenericModel($this->serviceSupervision->getDb(), 'queue_jobs', 'id');
            $jobs = $queueModel->trouverTout();

            $this->render('Administration/supervision_queue.php', [
                'title' => 'File d\'attente des Tâches',
                'jobs' => $jobs
            ]);
        } catch (\Exception $e) {
            $this->serviceSupervision->enregistrerAction('SYSTEM', 'QUEUE_VIEW_ERROR', null, null, ['error' => $e->getMessage()]);
            $this->render('errors/500.php', ['error_message' => "Impossible de charger l'état de la file d'attente."]);
        }
    }
}
