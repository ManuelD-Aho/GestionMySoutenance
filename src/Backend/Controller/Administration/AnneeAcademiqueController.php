<?php
// Emplacement: src/Backend/Controller/Administration/AnneeAcademiqueController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;
use Exception;

class AnneeAcademiqueController extends BaseController
{
    private ServiceSystemeInterface $systemeService;

    public function __construct(
        ServiceSystemeInterface $systemeService,
        ServiceSecuriteInterface $securiteService,
        ServiceSupervisionInterface $supervisionService,
        FormValidator $validator
    ) {
        parent::__construct($securiteService, $supervisionService, $validator);
        $this->systemeService = $systemeService;
    }

    public function index(): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_ANNEE_ACAD_ACCES');
        try {
            $this->render('Administration/gestion_annee_academique', [
                'title' => 'Gestion des Années Académiques',
                'annees' => $this->systemeService->listerAnneesAcademiques(),
                'csrf_token' => $this->generateCsrfToken('annee_form')
            ]);
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect('/admin/configuration');
        }
    }

    public function handleAction(): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_ANNEES_GERER');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('annee_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/config/annees-academiques');
            return;
        }

        $action = $_POST['action'] ?? '';
        $id = $_POST['id_annee_academique'] ?? null;
        $data = $this->getPostData();

        try {
            switch ($action) {
                case 'create':
                    $this->systemeService->creerAnneeAcademique($data['libelle_annee_academique'], $data['date_debut'], $data['date_fin'], isset($data['est_active']));
                    $this->addFlashMessage('success', 'Année académique créée.');
                    break;
                case 'update':
                    $this->systemeService->mettreAJourAnneeAcademique($id, $data);
                    $this->addFlashMessage('success', 'Année académique mise à jour.');
                    break;
                case 'delete':
                    $this->systemeService->supprimerAnneeAcademique($id);
                    $this->addFlashMessage('success', 'Année académique supprimée.');
                    break;
                case 'set_active':
                    $this->systemeService->setAnneeAcademiqueActive($id);
                    $this->addFlashMessage('success', "L'année {$id} est maintenant active.");
                    break;
                default:
                    throw new Exception("Action non valide.");
            }
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        }
        $this->redirect('/admin/config/annees-academiques');
    }
}