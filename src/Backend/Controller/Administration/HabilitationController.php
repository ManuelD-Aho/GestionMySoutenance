<?php

declare(strict_types=1);

namespace App\Backend\Controller\Administration;

use App\Config\Container;
use App\Backend\Controller\BaseController;
use App\Backend\Service\Interface\AdministrationRBACServiceInterface;
use App\Backend\Util\FormValidator;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;

class HabilitationController extends BaseController
{
    private AdministrationRBACServiceInterface $rbacService;
    private FormValidator $validator;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->rbacService = $container->get(AdministrationRBACServiceInterface::class);
        $this->validator = $container->get(FormValidator::class);
    }

    public function index(): void
    {
        $this->checkPermission('TRAIT_ADMIN_HABILITATIONS_ACCEDER');
        $this->render('Administration/Habilitations/index', [
            'page_title' => 'Gestion des Habilitations'
        ]);
    }

    public function listGroupes(): void
    {
        $this->checkPermission('TRAIT_ADMIN_HABILITATIONS_GROUPE_LISTER');
        // Implémentation future
        $this->redirect('/dashboard/admin/habilitations');
    }

    public function createGroupe(): void
    {
        $this->checkPermission('TRAIT_ADMIN_HABILITATIONS_GROUPE_CREER');
        // Implémentation future
        $this->redirect('/dashboard/admin/habilitations');
    }

    public function editGroupe(string $id): void
    {
        $this->checkPermission('TRAIT_ADMIN_HABILITATIONS_GROUPE_MODIFIER');
        // Implémentation future
        $this->redirect('/dashboard/admin/habilitations');
    }

    public function deleteGroupe(string $id): void
    {
        $this->checkPermission('TRAIT_ADMIN_HABILITATIONS_GROUPE_SUPPRIMER');
        // Implémentation future
        $this->redirect('/dashboard/admin/habilitations');
    }

    public function manageRattachements(string $idGroupe): void
    {
        $this->checkPermission('TRAIT_ADMIN_HABILITATIONS_RATTACHEMENT_GERER');
        // Implémentation future
        $this->redirect('/dashboard/admin/habilitations');
    }

    public function updateRattachements(string $idGroupe): void
    {
        $this->checkPermission('TRAIT_ADMIN_HABILITATIONS_RATTACHEMENT_GERER');
        // Implémentation future
        $this->redirect('/dashboard/admin/habilitations');
    }
}