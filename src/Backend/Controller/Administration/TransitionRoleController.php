<?php

declare(strict_types=1);

namespace App\Backend\Controller\Administration;

use App\Config\Container;
use App\Backend\Controller\BaseController;
use App\Backend\Service\Interface\TransitionRoleServiceInterface;

class TransitionRoleController extends BaseController
{
    private TransitionRoleServiceInterface $transitionService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->transitionService = $container->get(TransitionRoleServiceInterface::class);
    }

    public function index(): void
    {
        $this->checkPermission('TRAIT_ADMIN_TRANSITION_ROLE_ACCEDER');
        $this->render('Administration/TransitionRole/index', [
            'page_title' => 'Transitions de Rôles & Délégations'
        ]);
    }

    public function detectOrphanTasks(string $idUser): void
    {
        $this->checkPermission('TRAIT_ADMIN_TRANSITION_ROLE_DETECTER');
        // Implémentation future
        $this->redirect('/dashboard/admin/transition-roles');
    }

    public function reassignTask(string $idTask): void
    {
        $this->checkPermission('TRAIT_ADMIN_TRANSITION_ROLE_REASSIGNER');
        // Implémentation future
        $this->redirect('/dashboard/admin/transition-roles');
    }

    public function listDelegations(): void
    {
        $this->checkPermission('TRAIT_ADMIN_DELEGATION_LISTER');
        // Implémentation future
        $this->redirect('/dashboard/admin/transition-roles');
    }

    public function createDelegation(): void
    {
        $this->checkPermission('TRAIT_ADMIN_DELEGATION_CREER');
        // Implémentation future
        $this->redirect('/dashboard/admin/transition-roles');
    }

    public function cancelDelegation(string $idDelegation): void
    {
        $this->checkPermission('TRAIT_ADMIN_DELEGATION_REVOQUER');
        // Implémentation future
        $this->redirect('/dashboard/admin/transition-roles');
    }
}