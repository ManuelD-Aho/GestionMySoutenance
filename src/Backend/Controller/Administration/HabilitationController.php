<?php
// Emplacement: src/Backend/Controller/Administration/HabilitationController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;
use Exception;

class HabilitationController extends BaseController
{
    public function __construct(
        ServiceSecuriteInterface $securiteService,
        ServiceSupervisionInterface $supervisionService,
        FormValidator $validator
    ) {
        parent::__construct($securiteService, $supervisionService, $validator);
    }

    public function index(): void
    {
        $this->requirePermission('TRAIT_ADMIN_HABILITATIONS_ACCES');
        try {
            $this->render('Administration/gestion_habilitations', [
                'title' => 'Gestion des Habilitations',
                'groupes' => $this->securiteService->getAllGroupes(),
                'traitements' => $this->securiteService->getAllTraitements(),
                'rattachements' => $this->securiteService->getAllRattachements(),
                'csrf_token' => $this->generateCsrfToken('habilitations_form')
            ]);
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect('/admin/dashboard');
        }
    }

    public function updateRattachements(): void
    {
        $this->requirePermission('TRAIT_ADMIN_HABILITATIONS_RATTACHEMENTS_ACCES');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('habilitations_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/habilitations');
            return;
        }

        try {
            $rattachements = $_POST['rattachements'] ?? [];
            $this->securiteService->updateRattachements($rattachements);
            $this->addFlashMessage('success', 'Permissions mises à jour.');
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        }
        $this->redirect('/admin/habilitations');
    }
}