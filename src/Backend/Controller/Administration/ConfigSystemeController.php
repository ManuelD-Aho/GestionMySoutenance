<?php
// Emplacement: src/Backend/Controller/Administration/ConfigSystemeController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Backend\Service\Document\ServiceDocumentInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;
use Exception;

class ConfigSystemeController extends BaseController
{
    private ServiceSystemeInterface $systemeService;
    private ServiceDocumentInterface $documentService;

    public function __construct(
        ServiceSystemeInterface $systemeService,
        ServiceDocumentInterface $documentService,
        ServiceSecuriteInterface $securiteService,
        ServiceSupervisionInterface $supervisionService,
        FormValidator $validator
    ) {
        parent::__construct($securiteService, $supervisionService, $validator);
        $this->systemeService = $systemeService;
        $this->documentService = $documentService;
    }

    public function index(): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_ACCEDER');
        try {
            $this->render('Administration/gestion_configuration', [
                'title' => 'Configuration du Système',
                'system_parameters' => $this->systemeService->getAllParametres(),
                'document_models' => $this->documentService->listerModelesDocument(),
                'csrf_token_params' => $this->generateCsrfToken('params_form'),
                'csrf_token_docs' => $this->generateCsrfToken('docs_form'),
            ]);
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur de chargement de la page de configuration : ' . $e->getMessage());
            $this->redirect('/admin/dashboard');
        }
    }

    public function handleSystemParameters(): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_PARAMETRES_GERER');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('params_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/configuration');
            return;
        }

        try {
            $data = $this->getPostData();
            unset($data['csrf_token']);
            $this->systemeService->setParametres($data);
            $this->addFlashMessage('success', 'Paramètres système mis à jour.');
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        }
        $this->redirect('/admin/configuration');
    }

    public function handleDocumentModelAction(): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_MODELES_DOC_GERER');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('docs_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/configuration#docs-tab');
            return;
        }

        $action = $_POST['action'] ?? '';
        $id = $_POST['id_modele'] ?? null;
        try {
            switch ($action) {
                case 'import':
                    $file = $this->getFileData('word_file');
                    $this->documentService->importerModeleDocumentWord($file);
                    $this->addFlashMessage('success', 'Modèle importé.');
                    break;
                case 'update':
                    $this->documentService->mettreAJourModeleDocument($id, $_POST['nom_modele'], $_POST['contenu_html']);
                    $this->addFlashMessage('success', 'Modèle mis à jour.');
                    break;
                case 'delete':
                    $this->documentService->supprimerModeleDocument($id);
                    $this->addFlashMessage('success', 'Modèle supprimé.');
                    break;
            }
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        }
        $this->redirect('/admin/configuration#docs-tab');
    }
}