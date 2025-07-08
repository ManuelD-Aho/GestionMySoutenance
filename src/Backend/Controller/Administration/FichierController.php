<?php
// Emplacement: src/Backend/Controller/Administration/FichierController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Fichier\ServiceFichierInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;
use Exception;

class FichierController extends BaseController
{
    private ServiceFichierInterface $fichierService;

    public function __construct(
        ServiceFichierInterface $fichierService,
        ServiceSecuriteInterface $securiteService,
        ServiceSupervisionInterface $supervisionService,
        FormValidator $validator
    ) {
        parent::__construct($securiteService, $supervisionService, $validator);
        $this->fichierService = $fichierService;
    }

    public function index(): void
    {
        $this->requirePermission('TRAIT_ADMIN_FICHIERS_LISTER_ACCES');
        try {
            $filters = $this->getGetData();
            $page = max(1, (int)($filters['page'] ?? 1));
            $result = $this->fichierService->getAllFiles($filters, $page);

            $this->render('Administration/fichiers/index', [
                'title' => 'Gestion des Fichiers',
                'files' => $result['files'],
                'pagination' => $result['pagination'],
                'stats' => $this->fichierService->getFileStats(),
                'filters' => $filters,
                'csrf_token' => $this->generateCsrfToken('file_action_form')
            ]);
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect('/admin/dashboard');
        }
    }

    public function upload(): void
    {
        $this->requirePermission('TRAIT_ADMIN_FICHIERS_UPLOAD_ACCES');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('upload_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/fichiers');
            return;
        }

        $fileData = $this->getFileData('fichier');
        if (!$fileData || $fileData['error'] !== UPLOAD_ERR_OK) {
            $this->addFlashMessage('error', 'Erreur de téléversement ou aucun fichier sélectionné.');
            $this->redirect('/admin/fichiers');
            return;
        }

        try {
            $metadata = ['description' => $_POST['description'] ?? ''];
            $this->fichierService->uploadFile($fileData, $metadata);
            $this->addFlashMessage('success', 'Fichier téléversé avec succès.');
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        }
        $this->redirect('/admin/fichiers');
    }

    public function download(string $id): void
    {
        $this->requirePermission('TRAIT_ADMIN_FICHIERS_LISTER_ACCES');
        try {
            $this->fichierService->downloadFile($id);
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur de téléchargement: ' . $e->getMessage());
            $this->redirect('/admin/fichiers');
        }
    }

    public function delete(string $id): void
    {
        $this->requirePermission('TRAIT_ADMIN_FICHIERS_SUPPRIMER');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('file_action_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/fichiers');
            return;
        }

        try {
            $this->fichierService->deleteFile($id);
            $this->addFlashMessage('success', 'Fichier supprimé.');
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        }
        $this->redirect('/admin/fichiers');
    }
}