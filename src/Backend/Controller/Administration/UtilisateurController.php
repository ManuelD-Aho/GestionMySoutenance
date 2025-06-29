<?php

declare(strict_types=1);

namespace App\Backend\Controller\Administration;

use App\Config\Container;
use App\Backend\Controller\BaseController;
use App\Backend\Service\Interface\CompteUtilisateurServiceInterface;
use App\Backend\Service\Interface\PermissionsServiceInterface;
use App\Backend\Util\FormValidator;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\MotDePasseInvalideException;

class UtilisateurController extends BaseController
{
    private CompteUtilisateurServiceInterface $compteUtilisateurService;
    private PermissionsServiceInterface $permissionsService;
    private FormValidator $validator;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->compteUtilisateurService = $container->get(CompteUtilisateurServiceInterface::class);
        $this->permissionsService = $container->get(PermissionsServiceInterface::class);
        $this->validator = $container->get(FormValidator::class);
    }

    public function index(): void
    {
        $this->checkPermission('TRAIT_ADMIN_GERER_UTILISATEURS_LISTER');
        $utilisateurs = $this->compteUtilisateurService->listerComptes();
        $this->render('Administration/Utilisateurs/liste_utilisateurs', [
            'page_title' => 'Gestion des Utilisateurs',
            'utilisateurs' => $utilisateurs
        ]);
    }

    public function create(string $type): void
    {
        $this->checkPermission('TRAIT_ADMIN_GERER_UTILISATEURS_CREER');
        // Implémentation future
        $this->redirect('/dashboard/admin/utilisateurs');
    }

    public function edit(string $id): void
    {
        $this->checkPermission('TRAIT_ADMIN_GERER_UTILISATEURS_MODIFIER');
        // Implémentation future
        $this->redirect('/dashboard/admin/utilisateurs');
    }

    public function delete(string $id): void
    {
        $this->checkPermission('TRAIT_ADMIN_GERER_UTILISATEURS_SUPPRIMER');
        // Implémentation future
        $this->redirect('/dashboard/admin/utilisateurs');
    }

    public function changeStatus(string $id): void
    {
        $this->checkPermission('TRAIT_ADMIN_GERER_UTILISATEURS_CHANGER_STATUT');
        // Implémentation future
        $this->redirect('/dashboard/admin/utilisateurs');
    }

    public function resetPassword(string $id): void
    {
        $this->checkPermission('TRAIT_ADMIN_GERER_UTILISATEURS_RESET_MDP');
        // Implémentation future
        $this->redirect('/dashboard/admin/utilisateurs');
    }

    public function importStudents(): void
    {
        $this->checkPermission('TRAIT_ADMIN_GERER_UTILISATEURS_IMPORTER');
        // Implémentation future
        $this->redirect('/dashboard/admin/utilisateurs');
    }
}