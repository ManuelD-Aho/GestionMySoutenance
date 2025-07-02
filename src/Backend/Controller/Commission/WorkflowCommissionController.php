<?php
// src/Backend/Controller/Commission/WorkflowCommissionController.php

namespace App\Backend\Controller\Commission;

use App\Backend\Controller\BaseController;
use App\Backend\Service\WorkflowSoutenance\ServiceWorkflowSoutenanceInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;
use Exception;

/**
 * Orchestre tout le workflow de la commission : gestion des sessions, votes, et PV.
 */
class WorkflowCommissionController extends BaseController
{
    private ServiceWorkflowSoutenanceInterface $serviceWorkflow;
    // Suppression de la déclaration de propriété $validator
    // car elle est déjà disponible via BaseController::$validator (si BaseController l'injecte)

    public function __construct(
        ServiceWorkflowSoutenanceInterface $serviceWorkflow,
        FormValidator $validator, // Injecté pour BaseController
        ServiceSecuriteInterface $securiteService,
        ServiceSupervisionInterface $supervisionService
    ) {
        parent::__construct($securiteService, $supervisionService);
        $this->serviceWorkflow = $serviceWorkflow;
        // Pas besoin de réassigner $this->validator ici si BaseController le fait
    }

    /**
     * Affiche la liste de toutes les sessions de validation.
     */
    public function index(): void
    {
        $this->requirePermission('TRAIT_COMMISSION_DASHBOARD_ACCEDER');
        try {
            $sessions = $this->serviceWorkflow->listerSessionsPourCommission();
            $this->render('Commission/workflow_commission', [
                'title' => 'Gestion des Sessions de Validation',
                'sessions' => $sessions,
                'csrf_token_session_form' => $this->generateCsrfToken('session_form')
            ]);
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur lors du chargement des sessions : ' . $e->getMessage());
            $this->redirect('/commission/dashboard'); // Redirection vers le dashboard en cas d'erreur
        }
    }

    /**
     * Traite la création d'une nouvelle session de validation.
     */
    public function create(): void
    {
        $this->requirePermission('TRAIT_COMMISSION_SESSION_CREER');

        if (!$this->isPostRequest() || !$this->validateCsrfToken('session_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/commission/workflow');
            return; // Suppression de l'instruction inaccessible
        }

        $rules = [
            'nom_session' => 'required|max:255',
            'date_debut_session' => 'required',
            'date_fin_prevue' => 'required',
            'mode_session' => 'required|in:presentiel,en_ligne,hybride',
            'nombre_votants_requis' => 'required|numeric'
        ];
        if (!$this->validator->validate($_POST, $rules)) {
            $this->addFlashMessage('error', 'Formulaire invalide : ' . implode(', ', $this->validator->getErrors()));
            $this->redirect('/commission/workflow');
            return; // Suppression de l'instruction inaccessible
        }

        try {
            $user = $this->securiteService->getUtilisateurConnecte();
            $idSession = $this->serviceWorkflow->creerSession($user['numero_utilisateur'], $_POST);
            $this->addFlashMessage('success', "Session '{$_POST['nom_session']}' créée avec l'ID {$idSession}.");
        } catch (Exception $e) {
            $this->addFlashMessage('error', "Erreur lors de la création de la session : " . $e->getMessage());
        }
        $this->redirect('/commission/workflow');
    }

    /**
     * Traite le vote d'un membre de la commission pour un rapport.
     * Conçu pour être appelé via AJAX.
     */
    public function vote(): void
    {
        $this->requirePermission('TRAIT_COMMISSION_VALIDATION_RAPPORT_VOTER');

        if (!$this->isPostRequest() || !$this->validateCsrfToken('vote_form', $_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['success' => false, 'message' => 'Requête invalide.'], 403);
            return; // Suppression de l'instruction inaccessible
        }

        $data = $this->getPostData();
        $idRapport = $data['id_rapport'] ?? '';
        $decision = $data['decision'] ?? '';
        $commentaire = $data['commentaire'] ?? '';
        $idSession = $data['id_session'] ?? '';

        if ($decision !== 'VOTE_APPROUVE' && empty($commentaire)) {
            $this->jsonResponse(['success' => false, 'message' => 'Un commentaire est requis pour cette décision.'], 422);
            return; // Suppression de l'instruction inaccessible
        }

        try {
            $user = $this->securiteService->getUtilisateurConnecte();
            $this->serviceWorkflow->enregistrerVote($idRapport, $idSession, $user['numero_utilisateur'], $decision, $commentaire);

            $this->jsonResponse(['success' => true, 'message' => 'Vote enregistré avec succès.']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Traite l'initiation de la rédaction d'un PV.
     */
    public function initierPv(string $idSession): void
    {
        $this->requirePermission('TRAIT_COMMISSION_DASHBOARD_ACCEDER');

        try {
            $user = $this->securiteService->getUtilisateurConnecte();
            $idPv = $this->serviceWorkflow->initierRedactionPv($idSession, $user['numero_utilisateur']);
            $this->addFlashMessage('success', "Vous êtes maintenant le rédacteur du PV {$idPv}.");
            $this->redirect("/commission/pv/edit/{$idPv}");
        } catch (Exception $e) {
            $this->addFlashMessage('error', $e->getMessage());
            $this->redirect("/commission/workflow");
            return; // Suppression de l'instruction inaccessible
        }
    }

    /**
     * Traite l'approbation d'un PV par un membre.
     */
    public function approuverPv(string $idCompteRendu): void
    {
        $this->requirePermission('TRAIT_COMMISSION_DASHBOARD_ACCEDER');
        try {
            $user = $this->securiteService->getUtilisateurConnecte();
            $this->serviceWorkflow->approuverPv($idCompteRendu, $user['numero_utilisateur']);
            $this->addFlashMessage('success', 'PV approuvé avec succès.');
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur lors de l\'approbation : ' . $e->getMessage());
        }
        $this->redirect('/commission/dashboard');
    }

    /**
     * Traite la validation forcée d'un PV par le président.
     */
    public function forcerValidationPv(string $idCompteRendu): void
    {
        $this->requirePermission('TRAIT_COMMISSION_SESSION_GERER');

        if (!$this->isPostRequest() || !$this->validateCsrfToken('force_pv_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/commission/dashboard');
            return; // Suppression de l'instruction inaccessible
        }

        $justification = $_POST['justification'] ?? '';
        if(empty($justification)){
            $this->addFlashMessage('error', 'Une justification est obligatoire pour forcer la validation.');
            $this->redirect('/commission/dashboard');
            return; // Suppression de l'instruction inaccessible
        }

        try {
            $user = $this->securiteService->getUtilisateurConnecte();
            $this->serviceWorkflow->forcerValidationPv($idCompteRendu, $user['numero_utilisateur'], $justification);
            $this->addFlashMessage('success', 'Le PV a été validé par substitution.');
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur : ' . $e->getMessage());
        }
        $this->redirect('/commission/dashboard');
    }
}