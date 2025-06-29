<?php

declare(strict_types=1);

namespace App\Backend\Controller\Administration;

use App\Config\Container;
use App\Backend\Controller\BaseController;
use App\Backend\Service\Interface\NotificationConfigurationServiceInterface;

class NotificationConfigurationController extends BaseController
{
    private NotificationConfigurationServiceInterface $notifConfigService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->notifConfigService = $container->get(NotificationConfigurationServiceInterface::class);
    }

    public function index(): void
    {
        $this->checkPermission('TRAIT_ADMIN_NOTIF_CONFIG_ACCEDER');
        $regles = $this->notifConfigService->listerRegles();
        $this->render('Administration/ConfigSysteme/notification_configuration', [
            'page_title' => 'Configuration des Notifications',
            'regles' => $regles,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function updateMatrix(): void
    {
        $this->checkPermission('TRAIT_ADMIN_NOTIF_CONFIG_MODIFIER');
        // Implémentation future
        $this->redirect('/dashboard/admin/config/notifications-config');
    }
}