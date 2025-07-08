<?php
// Emplacement: src/Backend/Controller/Administration/NotificationConfigurationController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Communication\ServiceCommunicationInterface;
use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;
use Exception;

class NotificationConfigurationController extends BaseController
{
    private ServiceCommunicationInterface $communicationService;
    private ServiceSystemeInterface $systemeService;

    public function __construct(
        ServiceCommunicationInterface $communicationService,
        ServiceSystemeInterface $systemeService,
        ServiceSecuriteInterface $securiteService,
        ServiceSupervisionInterface $supervisionService,
        FormValidator $validator
    ) {
        parent::__construct($securiteService, $supervisionService, $validator);
        $this->communicationService = $communicationService;
        $this->systemeService = $systemeService;
    }

    public function index(): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_NOTIFS_ACCES');
        try {
            $this->render('Administration/config/notifications', [
                'title' => 'Configuration des Notifications',
                'templates' => $this->communicationService->listerModelesNotification(),
                'matrice' => $this->communicationService->listerReglesMatrice(),
                'actions' => $this->systemeService->gererReferentiel('list', 'action'),
                'groupes' => $this->systemeService->gererReferentiel('list', 'groupe_utilisateur'),
                'csrf_token' => $this->generateCsrfToken('notifs_form')
            ]);
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect('/admin/configuration');
        }
    }

    public function handleAction(): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_NOTIFS_GERER');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('notifs_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/config/notifications');
            return;
        }

        $action = $_POST['action'] ?? '';
        try {
            switch ($action) {
                case 'update_rule':
                    $this->communicationService->mettreAJourRegleMatrice($_POST['id_regle'], $_POST['canal'], isset($_POST['est_active']));
                    $this->addFlashMessage('success', 'Règle mise à jour.');
                    break;
                case 'update_template':
                    $this->communicationService->mettreAJourModeleNotification($_POST['id'], $_POST['libelle'], $_POST['contenu']);
                    $this->addFlashMessage('success', 'Modèle mis à jour.');
                    break;
                default:
                    throw new Exception("Action non valide.");
            }
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        }
        $this->redirect('/admin/config/notifications');
    }
}