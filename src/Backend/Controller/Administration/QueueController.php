<?php

declare(strict_types=1);

namespace App\Backend\Controller\Administration;

use App\Config\Container;
use App\Backend\Controller\BaseController;
use App\Backend\Service\Interface\QueueServiceInterface;

class QueueController extends BaseController
{
    private QueueServiceInterface $queueService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->queueService = $container->get(QueueServiceInterface::class);
    }

    public function index(): void
    {
        $this->checkPermission('TRAIT_ADMIN_QUEUE_VOIR');
        $stats = $this->queueService->getStatistiquesQueue();
        $this->render('Administration/Supervision/queue', [
            'page_title' => 'Gestion de la File d\'Attente',
            'stats' => $stats,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function processNextJob(): void
    {
        $this->checkPermission('TRAIT_ADMIN_QUEUE_TRAITER');
        // Implémentation future
        $this->redirect('/dashboard/admin/supervision/queue');
    }
}