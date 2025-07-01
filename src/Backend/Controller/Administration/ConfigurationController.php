<?php
// src/Backend/Controller/Administration/ConfigurationController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;

class ConfigurationController extends BaseController
{
    private ServiceSystemeInterface $serviceSysteme;

    public function __construct(
        ServiceSecuriteInterface $serviceSecurite,
        ServiceSupervisionInterface $serviceSupervision,
        FormValidator $formValidator,
        ServiceSystemeInterface $serviceSysteme
    ) {
        parent::__construct($serviceSecurite, $serviceSupervision, $formValidator);
        $this->serviceSysteme = $serviceSysteme;
    }

    /**
     * Affiche le formulaire de configuration des paramètres système.
     */
    public function showConfigForm(): void
    {
        $this->checkPermission('ADMIN_CONFIG_READ');

        try {
            $parametres = $this->serviceSysteme->getAllParametres();
            $this->render('Administration/gestion_parametres.php', [
                'title' => 'Configuration du Système',
                'parametres' => $parametres
            ]);
        } catch (\Exception $e) {
            $this->serviceSupervision->enregistrerAction('SYSTEM', 'CONFIG_FORM_ERROR', null, null, ['error' => $e->getMessage()]);
            $this->render('errors/500.php', ['error_message' => "Impossible de charger les paramètres de configuration."]);
        }
    }

    /**
     * Traite la soumission du formulaire de configuration et enregistre les paramètres.
     */
    public function saveConfig(): void
    {
        $this->checkPermission('ADMIN_CONFIG_UPDATE');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['success' => false, 'message' => 'Requête invalide ou expirée.'], 403);
            return;
        }

        try {
// Exclure le token CSRF des données à sauvegarder
            $parametresToSave = $_POST;
            unset($parametresToSave['csrf_token']);

            $this->serviceSysteme->setParametres($parametresToSave);

            $_SESSION['success'] = 'Les paramètres ont été mis à jour avec succès.';
            $this->redirect('/admin/config');
        } catch (\Exception $e) {
            $this->serviceSupervision->enregistrerAction($_SESSION['user_id'], 'CONFIG_SAVE_ERROR', null, null, ['error' => $e->getMessage()]);
            $_SESSION['error'] = 'Une erreur est survenue lors de la sauvegarde des paramètres.';
            $this->redirect('/admin/config');
        }
    }
}
