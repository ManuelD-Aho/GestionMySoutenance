<?php
// Emplacement: src/Backend/Controller/Administration/ReferentialController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;
use Exception;

class ReferentialController extends BaseController
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
        $this->requirePermission('TRAIT_ADMIN_REFERENTIELS_LISTER_ACCES');
        try {
            $this->render('Administration/referentiels/index', [
                'title' => 'Gestion des Référentiels',
                'referentiels' => $this->getReferentialList()
            ]);
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect('/admin/configuration');
        }
    }

    public function listItems(string $entityName): void
    {
        $this->requirePermission('TRAIT_ADMIN_REFERENTIELS_CRUD_ACCES');
        try {
            $this->render('Administration/referentiels/list_items', [
                'title' => 'Détails du Référentiel: ' . ucfirst(str_replace('_', ' ', $entityName)),
                'entityName' => $entityName,
                'items' => $this->systemeService->gererReferentiel('list', $entityName),
                'csrf_token' => $this->generateCsrfToken('ref_form')
            ]);
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect('/admin/referentiels');
        }
    }

    public function handleAction(): void
    {
        $this->requirePermission('TRAIT_ADMIN_REFERENTIELS_CRUD_ACCES');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('ref_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/referentiels');
            return;
        }

        $action = $_POST['action'] ?? '';
        $entityName = $_POST['entity_name'] ?? '';
        $id = $_POST['id'] ?? null;
        $data = $this->getPostData();
        unset($data['action'], $data['entity_name'], $data['id'], $data['csrf_token']);

        try {
            $this->systemeService->gererReferentiel($action, $entityName, $id, $data);
            $this->addFlashMessage('success', "Opération '{$action}' réussie sur le référentiel '{$entityName}'.");
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        }
        $this->redirect('/admin/referentiels/' . $entityName);
    }

    private function getReferentialList(): array
    {
        // Cette liste pourrait être stockée en configuration
        $referentialKeys = [
            'grade', 'fonction', 'specialite', 'niveau_etude', 'statut_rapport_ref',
            'statut_pv_ref', 'statut_paiement_ref', 'decision_vote_ref', 'statut_conformite_ref',
            'statut_reclamation_ref', 'type_document_ref', 'statut_jury', 'action', 'groupe_utilisateur',
            'critere_conformite_ref', 'decision_passage_ref', 'decision_validation_pv_ref',
            'niveau_acces_donne', 'type_utilisateur', 'ue', 'ecue', 'entreprise'
        ];
        $list = [];
        foreach ($referentialKeys as $key) {
            $list[$key] = ucwords(str_replace(['_', ' ref'], [' ', ''], $key));
        }
        asort($list);
        return $list;
    }
}