<?php

declare(strict_types=1);

namespace App\Backend\Controller\Administration;

use App\Config\Container;
use App\Backend\Controller\BaseController;
use App\Backend\Service\Interface\FichierServiceInterface;
use App\Backend\Exception\ValidationException;

class FichierController extends BaseController
{
    private FichierServiceInterface $fichierService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->fichierService = $container->get(FichierServiceInterface::class);
    }

    public function index(): void
    {
        $this->checkPermission('TRAIT_ADMIN_FICHIERS_LISTER');
        $this->render('Administration/Fichier/list_files', [
            'page_title' => 'Gestion des Fichiers',
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function upload(): void
    {
        $this->checkPermission('TRAIT_ADMIN_FICHIERS_UPLOADER');

        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->addFlashMessage('error', 'Jeton de sécurité invalide.');
            $this->redirect('/dashboard/admin/files');
            return;
        }

        if (empty($_FILES['file_upload'])) {
            $this->addFlashMessage('error', 'Aucun fichier sélectionné.');
            $this->redirect('/dashboard/admin/files');
            return;
        }

        try {
            $destination = $_POST['destination'] ?? 'divers';
            $this->fichierService->uploader($_FILES['file_upload'], $destination);
            $this->addFlashMessage('success', 'Fichier téléversé avec succès.');
        } catch (ValidationException $e) {
            $this->addFlashMessage('error', 'Erreur de validation: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'Une erreur est survenue lors du téléversement.');
        }

        $this->redirect('/dashboard/admin/files');
    }

    public function delete(string $idFichier): void
    {
        $this->checkPermission('TRAIT_ADMIN_FICHIERS_SUPPRIMER');
        // Implémentation future
        $this->redirect('/dashboard/admin/files');
    }
}