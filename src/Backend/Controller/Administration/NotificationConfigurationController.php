<?php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;

// Assurez-vous que ce contrôleur hérite de BaseController
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Service\NotificationConfiguration\ServiceNotificationConfiguration; // Importez le service

class NotificationConfigurationController extends BaseController // Hériter de BaseController
{
    private ServiceNotificationConfiguration $notificationConfigurationService;

    public function __construct(
        ServiceAuthentication            $authService,
        ServicePermissions               $permissionService,
        FormValidator                    $validator,
        ServiceNotificationConfiguration $notificationConfigurationService // Injection du service de configuration de notifications
    )
    {
        parent::__construct($authService, $permissionService, $validator); // Appel au constructeur parent
        $this->notificationConfigurationService = $notificationConfigurationService;
    }

    /**
     * Affiche la page de configuration des règles de notification.
     */
    public function index(): void
    {
        $this->requireLogin(); // Exiger que l'utilisateur soit connecté
        // requirePermission('TRAIT_ADMIN_NOTIF_CONFIG_ACCEDER'); // Exemple de permission requise

        try {
            // Vous pouvez récupérer des données via $this->notificationConfigurationService ici
            // $notificationRules = $this->notificationConfigurationService->getNotificationRules();

            $data = [
                'page_title' => 'Configuration des Notifications',
                // 'notification_rules' => $notificationRules,
            ];
            $this->render('Administration/ConfigSysteme/notifications_config', $data); // Assurez-vous que cette vue existe
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement de la configuration des notifications: " . $e->getMessage());
            error_log("NotificationConfigurationController::index error: " . $e->getMessage());
            $this->redirect('/dashboard/admin/config'); // Rediriger en cas d'erreur
        }
    }

    // Ajoutez ici d'autres méthodes comme updateMatrix(), etc., si définies dans vos routes
    // public function updateMatrix(): void { ... }
}