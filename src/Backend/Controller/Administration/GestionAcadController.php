<?php

declare(strict_types=1);

namespace App\Backend\Controller\Administration;

use App\Config\Container;
use App\Backend\Controller\BaseController;
use App\Backend\Service\Interface\InscriptionServiceInterface;
use App\Backend\Service\Interface\NotationServiceInterface;
use App\Backend\Service\Interface\PersonnelAcademiqueServiceInterface;
use App\Backend\Service\Interface\StageServiceInterface;
use App\Backend\Service\Interface\CursusServiceInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\DoublonException;

class GestionAcadController extends BaseController
{
    private InscriptionServiceInterface $inscriptionService;
    private NotationServiceInterface $notationService;
    private PersonnelAcademiqueServiceInterface $personnelAcadService;
    private StageServiceInterface $stageService;
    private CursusServiceInterface $cursusService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->inscriptionService = $container->get(InscriptionServiceInterface::class);
        $this->notationService = $container->get(NotationServiceInterface::class);
        $this->personnelAcadService = $container->get(PersonnelAcademiqueServiceInterface::class);
        $this->stageService = $container->get(StageServiceInterface::class);
        $this->cursusService = $container->get(CursusServiceInterface::class);
    }

    public function index(): void
    {
        $this->checkPermission('TRAIT_ADMIN_GESTION_ACAD_ACCEDER');
        $this->render('Administration/GestionAcad/index', [
            'page_title' => 'Gestion Académique'
        ]);
    }

    public function listInscriptions(): void
    {
        $this->checkPermission('TRAIT_ADMIN_GESTION_ACAD_INSCRIPTION_LISTER');
        $inscriptions = $this->inscriptionService->listerInscriptions();
        $this->render('Administration/GestionAcad/liste_inscriptions', [
            'page_title' => 'Liste des Inscriptions',
            'inscriptions' => $inscriptions
        ]);
    }

    public function createInscription(): void
    {
        $this->checkPermission('TRAIT_ADMIN_GESTION_ACAD_INSCRIPTION_CREER');
        // Implémentation future
        $this->redirect('/dashboard/admin/gestion-acad/inscriptions');
    }

    public function editInscription(string $numeroCarteEtudiant, string $idNiveauEtude, string $idAnneeAcademique): void
    {
        $this->checkPermission('TRAIT_ADMIN_GESTION_ACAD_INSCRIPTION_MODIFIER');
        // Implémentation future
        $this->redirect('/dashboard/admin/gestion-acad/inscriptions');
    }

    public function deleteInscription(string $numeroCarteEtudiant, string $idNiveauEtude, string $idAnneeAcademique): void
    {
        $this->checkPermission('TRAIT_ADMIN_GESTION_ACAD_INSCRIPTION_SUPPRIMER');
        // Implémentation future
        $this->redirect('/dashboard/admin/gestion-acad/inscriptions');
    }

    public function listNotes(): void
    {
        $this->checkPermission('TRAIT_ADMIN_GESTION_ACAD_NOTE_LISTER');
        // Implémentation future
        $this->redirect('/dashboard/admin/gestion-acad');
    }

    public function handleNoteForm(string $numeroCarteEtudiant = null, string $idEcue = null): void
    {
        // Implémentation future
        $this->redirect('/dashboard/admin/gestion-acad');
    }

    public function deleteNote(string $numeroCarteEtudiant, string $idEcue): void
    {
        // Implémentation future
        $this->redirect('/dashboard/admin/gestion-acad');
    }

    public function listStages(): void
    {
        $this->checkPermission('TRAIT_ADMIN_GESTION_ACAD_STAGE_LISTER');
        // Implémentation future
        $this->redirect('/dashboard/admin/gestion-acad');
    }

    public function createStage(): void
    {
        // Implémentation future
        $this->redirect('/dashboard/admin/gestion-acad');
    }

    public function editStage(string $idEntreprise, string $numeroCarteEtudiant): void
    {
        // Implémentation future
        $this->redirect('/dashboard/admin/gestion-acad');
    }

    public function deleteStage(string $idEntreprise, string $numeroCarteEtudiant): void
    {
        // Implémentation future
        $this->redirect('/dashboard/admin/gestion-acad');
    }

    public function manageEnseignantCarrieres(): void
    {
        $this->checkPermission('TRAIT_ADMIN_GESTION_ACAD_CARRIERE_GERER');
        // Implémentation future
        $this->redirect('/dashboard/admin/gestion-acad');
    }

    public function addEnseignantGrade(): void
    {
        // Implémentation future
        $this->redirect('/dashboard/admin/gestion-acad');
    }

    public function addEnseignantFonction(): void
    {
        // Implémentation future
        $this->redirect('/dashboard/admin/gestion-acad');
    }

    public function addEnseignantSpecialite(): void
    {
        // Implémentation future
        $this->redirect('/dashboard/admin/gestion-acad');
    }

    public function listUes(): void
    {
        // Implémentation future
        $this->redirect('/dashboard/admin/gestion-acad');
    }

    public function createUe(): void
    {
        // Implémentation future
        $this->redirect('/dashboard/admin/gestion-acad');
    }

    public function listEcues(): void
    {
        // Implémentation future
        $this->redirect('/dashboard/admin/gestion-acad');
    }

    public function createEcue(): void
    {
        // Implémentation future
        $this->redirect('/dashboard/admin/gestion-acad');
    }

    public function linkEcueToUe(string $idEcue, string $idUe): void
    {
        // Implémentation future
        $this->redirect('/dashboard/admin/gestion-acad');
    }
}