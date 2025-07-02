<?php
// src/Backend/Controller/Commission/WorkflowCommissionController.php

namespace App\Backend\Controller\Commission;

use App\Backend\Controller\BaseController;
use App\Backend\Service\WorkflowSoutenance\ServiceWorkflowSoutenanceInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface; // Ajout de la dépendance
use App\Backend\Service\Supervision\ServiceSupervisionInterface; // Ajout de la dépendance
use App\Backend\Util\FormValidator;
use Exception;

/**
 * Orchestre tout le workflow de la commission : gestion des sessions, votes, et PV.
 */
class WorkflowCommissionController extends BaseController
{
    private ServiceWorkflowSoutenanceInterface $serviceWorkflow;
    private FormValidator $validator;

    public function __construct(
        ServiceWorkflowSoutenanceInterface $serviceWorkflow,
        FormValidator $validator,
        ServiceSecuriteInterface $securiteService, // Injecté pour BaseController
        ServiceSupervisionInterface $supervisionService // Injecté pour BaseController
    ) {
        parent::__construct($securiteService, $supervisionService);
        $this->serviceWorkflow = $serviceWorkflow;
        $this->validator = $validator;
    }

    /**
     * Affiche la liste de toutes les sessions de validation.
     */
    public function index(): void // Renommée de listSessions
    {
        // 5. Accessible par tous les membres de la commission.
        $this->requirePermission('TRAIT_COMMISSION_DASHBOARD_ACCEDER'); // Permission générique d'accès au module
        $sessions = $this->serviceWorkflow->listerSessionsPourCommission();
        $this->render('Commission/workflow_commission', [
            'title' => 'Gestion des Sessions de Validation',
            'sessions' => $sessions,
            'csrf_token_session_form' => $this->generateCsrfToken('session_form')
        ]);
    }

    /**
     * Traite la création d'une nouvelle session de validation.
     */
    public function create(): void // Renommée de createSession
    {
        // 5. Seul le Président peut créer une session.
        $this->requirePermission('TRAIT_COMMISSION_SESSION_CREER'); // Permission spécifique au Président

        if (!$this->isPostRequest() || !$this->validateCsrfToken('session_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/commission/workflow');
            return;
        }

        // 6. Validation des champs du DDL.
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
            return;
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
    public function vote(): void // Renommée de submitVote
    {
        $this->requirePermission('TRAIT_COMMISSION_VALIDATION_RAPPORT_VOTER');

        if (!$this->isPostRequest() || !$this->validateCsrfToken('vote_form', $_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['success' => false, 'message' => 'Requête invalide.'], 403);
            return;
        }

        $data = $this->getPostData();
        $idRapport = $data['id_rapport'] ?? '';
        $decision = $data['decision'] ?? '';
        $commentaire = $data['commentaire'] ?? '';
        $idSession = $data['id_session'] ?? '';

        // 12 & 13. Commentaire requis si vote non positif
        if ($decision !== 'VOTE_APPROUVE' && empty($commentaire)) {
            $this->jsonResponse(['success' => false, 'message' => 'Un commentaire est requis pour cette décision.'], 422);
            return;
        }

        try {
            $user = $this->securiteService->getUtilisateurConnecte();

            // 11. Le service vérifiera si l'utilisateur a déjà voté.
            // 13. Le service incrémentera le tour de vote si nécessaire.
            // 14 & 15. La logique de finalisation est dans le service.
            $this->serviceWorkflow->enregistrerVote($idRapport, $idSession, $user['numero_utilisateur'], $decision, $commentaire);

            // 20. Format de réponse JSON
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
        // 16. Accessible à tout membre de la commission.
        $this->requirePermission('TRAIT_COMMISSION_DASHBOARD_ACCEDER'); // Permission générique d'accès au module

        try {
            $user = $this->securiteService->getUtilisateurConnecte();
            // Le service vérifiera si un rédacteur n'est pas déjà assigné (logique du premier qui clique).
            // 17. Le service pré-remplira les données.
            $idPv = $this->serviceWorkflow->initierRedactionPv($idSession, $user['numero_utilisateur']);
            $this->addFlashMessage('success', "Vous êtes maintenant le rédacteur du PV {$idPv}.");
            // Redirection vers l'éditeur de PV (à créer).
            $this->redirect("/commission/pv/edit/{$idPv}");
        } catch (Exception $e) {
            $this->addFlashMessage('error', $e->getMessage());
            $this->redirect("/commission/workflow"); // Redirection vers la liste des sessions
        }
    }

    /**
     * Traite l'approbation d'un PV par un membre.
     */
    public function approuverPv(string $idCompteRendu): void
    {
        $this->requirePermission('TRAIT_COMMISSION_DASHBOARD_ACCEDER'); // Permission générique d'accès au module
        try {
            $user = $this->securiteService->getUtilisateurConnecte();
            // 18. Le service gère la logique d'approbation et de finalisation.
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
        $this->requirePermission('TRAIT_COMMISSION_SESSION_GERER'); // Permission du président

        if (!$this->isPostRequest() || !$this->validateCsrfToken('force_pv_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/commission/dashboard');
            return;
        }

        $justification = $_POST['justification'] ?? '';
        if(empty($justification)){
            $this->addFlashMessage('error', 'Une justification est obligatoire pour forcer la validation.');
            $this->redirect('/commission/dashboard');
            return;
        }

        try {
            $user = $this->securiteService->getUtilisateurConnecte();
            // 19. Le service enregistrera la justification dans l'audit.
            $this->serviceWorkflow->forcerValidationPv($idCompteRendu, $user['numero_utilisateur'], $justification);
            $this->addFlashMessage('success', 'Le PV a été validé par substitution.');
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur : ' . $e->getMessage());
        }
        $this->redirect('/commission/dashboard');
    }
}