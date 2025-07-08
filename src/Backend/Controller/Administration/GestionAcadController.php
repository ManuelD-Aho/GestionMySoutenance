<?php
// Emplacement: src/Backend/Controller/Administration/GestionAcadController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\ParcoursAcademique\ServiceParcoursAcademiqueInterface;
use App\Backend\Service\Utilisateur\ServiceUtilisateurInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;
use Exception;

class GestionAcadController extends BaseController
{
    private ServiceParcoursAcademiqueInterface $parcoursService;
    private ServiceUtilisateurInterface $utilisateurService;

    public function __construct(
        ServiceParcoursAcademiqueInterface $parcoursService,
        ServiceUtilisateurInterface $utilisateurService,
        ServiceSecuriteInterface $securiteService,
        ServiceSupervisionInterface $supervisionService,
        FormValidator $validator
    ) {
        parent::__construct($securiteService, $supervisionService, $validator);
        $this->parcoursService = $parcoursService;
        $this->utilisateurService = $utilisateurService;
    }

    public function index(): void
    {
        $this->requirePermission('TRAIT_ADMIN_GESTION_ACAD_ACCES');
        $this->render('Administration/gestion_academique', [
            'title' => 'Gestion Académique'
        ]);
    }

    public function handleInscription(): void
    {
        $this->requirePermission('TRAIT_ADMIN_GESTION_ACAD_INSCRIPTIONS_ACCES');
        if (!$this->isPostRequest()) {
            $this->redirect('/admin/gestion-acad');
            return;
        }
        // Logique pour créer/modifier une inscription via $this->parcoursService
        $this->addFlashMessage('success', 'Opération sur inscription effectuée.');
        $this->redirect('/admin/gestion-acad');
    }

    public function handleNote(): void
    {
        $this->requirePermission('TRAIT_ADMIN_GESTION_ACAD_NOTES_ACCES');
        if (!$this->isPostRequest()) {
            $this->redirect('/admin/gestion-acad');
            return;
        }
        // Logique pour saisir/modifier une note via $this->parcoursService
        $this->addFlashMessage('success', 'Opération sur note effectuée.');
        $this->redirect('/admin/gestion-acad');
    }
}