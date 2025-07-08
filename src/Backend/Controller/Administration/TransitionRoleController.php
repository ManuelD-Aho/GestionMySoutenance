<?php
// Emplacement: src/Backend/Controller/Administration/TransitionRoleController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Delegation\ServiceDelegationInterface;
use App\Backend\Service\Utilisateur\ServiceUtilisateurInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;
use Exception;

class TransitionRoleController extends BaseController
{
    private ServiceDelegationInterface $delegationService;
    private ServiceUtilisateurInterface $utilisateurService;

    public function __construct(
        ServiceDelegationInterface $delegationService,
        ServiceUtilisateurInterface $utilisateurService,
        ServiceSecuriteInterface $securiteService,
        ServiceSupervisionInterface $supervisionService,
        FormValidator $validator
    ) {
        parent::__construct($securiteService, $supervisionService, $validator);
        $this->delegationService = $delegationService;
        $this->utilisateurService = $utilisateurService;
    }

    public function index(): void
    {
        $this->requirePermission('TRAIT_ADMIN_TRANSITION_ROLE_DELEGATIONS_ACCES');
        try {
            $this->render('Administration/transition/index', [
                'title' => 'Gestion des Délégations',
                'delegations' => $this->delegationService->getAllDelegations(),
                'utilisateurs' => $this->utilisateurService->listerUtilisateursComplets(),
                'traitements' => $this->securiteService->getAllTraitements(),
                'csrf_token' => $this->generateCsrfToken('delegation_form')
            ]);
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect('/admin/dashboard');
        }
    }

    public function handleAction(): void
    {
        $this->requirePermission('TRAIT_ADMIN_TRANSITION_ROLE_DELEGATIONS_GERER');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('delegation_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/transition-role');
            return;
        }

        $action = $_POST['action'] ?? '';
        $id = $_POST['id_delegation'] ?? null;
        $data = $this->getPostData();

        try {
            switch ($action) {
                case 'create':
                    $this->delegationService->createDelegation($data['id_delegant'], $data['id_delegue'], $data['id_traitement'], $data['date_debut'], $data['date_fin']);
                    $this->addFlashMessage('success', 'Délégation créée.');
                    break;
                case 'revoke':
                    $this->delegationService->revoquerDelegation($id);
                    $this->addFlashMessage('success', 'Délégation révoquée.');
                    break;
                default:
                    throw new Exception("Action non valide.");
            }
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        }
        $this->redirect('/admin/transition-role');
    }
}