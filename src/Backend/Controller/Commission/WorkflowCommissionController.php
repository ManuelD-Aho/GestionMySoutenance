<?php
// src/Backend/Controller/Commission/WorkflowCommissionController.php

namespace App\Backend\Controller\Commission;

use App\Backend\Controller\BaseController;
use App\Backend\Service\WorkflowSoutenance\ServiceWorkflowSoutenanceInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;

class WorkflowCommissionController extends BaseController
{
    private ServiceWorkflowSoutenanceInterface $serviceWorkflow;

    public function __construct(
        ServiceSecuriteInterface $serviceSecurite,
        ServiceSupervisionInterface $serviceSupervision,
        FormValidator $formValidator,
        ServiceWorkflowSoutenanceInterface $serviceWorkflow
    ) {
        parent::__construct($serviceSecurite, $serviceSupervision, $formValidator);
        $this->serviceWorkflow = $serviceWorkflow;
    }

    public function listSessions(): void
    {
        $this->checkPermission('COMMISSION_SESSIONS_LIST');
        $sessions = $this->serviceWorkflow->listerSessionsPourCommission();
        $this->render('Commission/workflow_commission.php', [
            'title' => 'Gestion des Sessions de Validation',
            'sessions' => $sessions
        ]);
    }

    public function createSession(): void
    {
        $this->checkPermission('COMMISSION_SESSIONS_CREATE');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['success' => false, 'message' => 'Requête invalide.'], 403);
            return;
        }

        $user = $this->serviceSecurite->getUtilisateurConnecte();
        try {
            $idSession = $this->serviceWorkflow->creerSession($user['numero_utilisateur'], $_POST);
            $this->jsonResponse(['success' => true, 'message' => 'Session créée avec succès.', 'id' => $idSession]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function viewSession(string $id): void
    {
        $this->checkPermission('COMMISSION_SESSIONS_VIEW');
        $session = $this->serviceWorkflow->lireSessionComplete($id);
        if (!$session) {
            $this->render('errors/404.php');
            return;
        }
        $this->render('Commission/view_session.php', [
            'title' => 'Détails de la Session',
            'session' => $session
        ]);
    }

    public function submitVote(string $idSession, string $idRapport): void
    {
        $this->checkPermission('COMMISSION_VOTE');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['success' => false, 'message' => 'Requête invalide.'], 403);
            return;
        }

        $user = $this->serviceSecurite->getUtilisateurConnecte();
        try {
            $this->serviceWorkflow->enregistrerVote($idRapport, $user['numero_utilisateur'], $_POST['decision'], $_POST['commentaire'] ?? null);
            $this->jsonResponse(['success' => true, 'message' => 'Vote enregistré.']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
