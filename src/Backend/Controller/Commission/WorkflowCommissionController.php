<?php

namespace App\Backend\Controller\Commission;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Service\WorkflowSoutenance\ServiceWorkflowSoutenanceInterface;
use App\Backend\Service\Commission\ServiceCommissionInterface;
use App\Backend\Util\FormValidator;
use App\Backend\Exception\PermissionException;
use App\Backend\Exception\ValidationException;
use App\Backend\Exception\ElementNonTrouveException;

class WorkflowCommissionController extends BaseController
{
    private ServiceWorkflowSoutenanceInterface $workflowService;
    private ServiceCommissionInterface $commissionService;

    public function __construct(
        ServiceAuthentication $authService,
        ServicePermissions $permissionService,
        FormValidator $validator,
        ServiceWorkflowSoutenanceInterface $workflowService,
        ServiceCommissionInterface $commissionService
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->workflowService = $workflowService;
        $this->commissionService = $commissionService;
    }

    /**
     * Affiche la liste des workflows en cours
     */
    public function listerWorkflows(): void
    {
        $this->requireLogin();
        $this->requirePermission('GESTION_WORKFLOW_SOUTENANCE');

        try {
            $page = (int)$this->get('page', 1);
            $filtres = [
                'etat_workflow' => $this->get('etat'),
                'annee_academique' => $this->get('annee'),
                'etudiant' => $this->get('etudiant')
            ];

            $workflows = $this->workflowService->listerWorkflows($filtres, $page, 20);
            $user = $this->getCurrentUser();

            require_once ROOT_PATH . '/src/Frontend/views/commission/workflow/liste.php';

        } catch (\Exception $e) {
            $this->handleError($e, 'Erreur lors de la récupération des workflows');
        }
    }

    /**
     * Affiche les détails d'un workflow
     */
    public function voirWorkflow(): void
    {
        $this->requireLogin();
        $this->requirePermission('CONSULTATION_WORKFLOW_SOUTENANCE');

        try {
            $idWorkflow = $this->get('id');
            if (empty($idWorkflow)) {
                throw new ValidationException("ID du workflow requis");
            }

            $workflow = $this->workflowService->obtenirEtatWorkflow($idWorkflow);
            $user = $this->getCurrentUser();

            require_once ROOT_PATH . '/src/Frontend/views/commission/workflow/details.php';

        } catch (ElementNonTrouveException $e) {
            $this->handleError($e, 'Workflow non trouvé', 404);
        } catch (\Exception $e) {
            $this->handleError($e, 'Erreur lors de la récupération du workflow');
        }
    }

    /**
     * Programme une session de validation
     */
    public function programmerSession(): void
    {
        $this->requireLogin();
        $this->requirePermission('PROGRAMMATION_SESSION_VALIDATION');

        if ($this->request() === 'POST') {
            $this->traiterProgrammationSession();
        } else {
            $this->afficherFormulaireProgrammation();
        }
    }

    private function traiterProgrammationSession(): void
    {
        try {
            if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
                throw new ValidationException("Token CSRF invalide");
            }

            $donneesSession = [
                'libelle_session' => $this->post('libelle_session'),
                'date_debut_session' => $this->post('date_debut'),
                'date_fin_prevue' => $this->post('date_fin'),
                'numero_president_commission' => $this->post('president')
            ];

            $idsRapports = $this->post('rapports', []);
            if (empty($idsRapports)) {
                throw new ValidationException("Au moins un rapport doit être sélectionné");
            }

            // Validation
            $this->validator->validate($donneesSession, [
                'libelle_session' => ['required' => true, 'max_length' => 200],
                'date_debut_session' => ['required' => true, 'type' => 'date'],
                'date_fin_prevue' => ['required' => true, 'type' => 'date']
            ]);

            $idSession = $this->workflowService->programmerSessionValidation($donneesSession, $idsRapports);

            $this->setFlashMessage('success', 'Session de validation programmée avec succès');
            $this->redirect('/commission/workflow/session/' . $idSession);

        } catch (ValidationException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->afficherFormulaireProgrammation();
        } catch (\Exception $e) {
            $this->handleError($e, 'Erreur lors de la programmation de la session');
        }
    }

    private function afficherFormulaireProgrammation(): void
    {
        try {
            // Récupérer les rapports prêts pour la session
            $rapports = $this->obtenirRapportsPretsSession();
            $presidentsDisponibles = $this->obtenirPresidentsDisponibles();
            $user = $this->getCurrentUser();

            require_once ROOT_PATH . '/src/Frontend/views/commission/workflow/programmer-session.php';

        } catch (\Exception $e) {
            $this->handleError($e, 'Erreur lors de l\'affichage du formulaire');
        }
    }

    /**
     * Affecte un jury à un rapport
     */
    public function affecterJury(): void
    {
        $this->requireLogin();
        $this->requirePermission('AFFECTATION_JURY');

        if ($this->request() === 'POST') {
            $this->traiterAffectationJury();
        } else {
            $this->afficherFormulaireAffectation();
        }
    }

    private function traiterAffectationJury(): void
    {
        try {
            if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
                throw new ValidationException("Token CSRF invalide");
            }

            $idRapport = $this->post('id_rapport');
            $membresJury = $this->post('membres_jury', []);

            if (empty($idRapport)) {
                throw new ValidationException("ID du rapport requis");
            }

            if (count($membresJury) < 3) {
                throw new ValidationException("Au moins 3 membres de jury sont requis");
            }

            // Valider que chaque membre a un rôle
            foreach ($membresJury as $membre) {
                if (empty($membre['numero_enseignant']) || empty($membre['role'])) {
                    throw new ValidationException("Chaque membre doit avoir un enseignant et un rôle définis");
                }
            }

            $result = $this->workflowService->affecterJury($idRapport, $membresJury);

            if ($result) {
                $this->setFlashMessage('success', 'Jury affecté avec succès');
                $this->redirect('/commission/workflow/voir?id=' . $idRapport);
            } else {
                throw new \Exception("Erreur lors de l'affectation du jury");
            }

        } catch (ValidationException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->afficherFormulaireAffectation();
        } catch (\Exception $e) {
            $this->handleError($e, 'Erreur lors de l\'affectation du jury');
        }
    }

    private function afficherFormulaireAffectation(): void
    {
        try {
            $idRapport = $this->get('id_rapport');
            if (empty($idRapport)) {
                throw new ValidationException("ID du rapport requis");
            }

            $workflow = $this->workflowService->obtenirEtatWorkflow($idRapport);
            $enseignantsDisponibles = $this->obtenirEnseignantsDisponibles();
            $rolesJury = ['PRESIDENT', 'RAPPORTEUR', 'EXAMINATEUR'];
            $user = $this->getCurrentUser();

            require_once ROOT_PATH . '/src/Frontend/views/commission/workflow/affecter-jury.php';

        } catch (\Exception $e) {
            $this->handleError($e, 'Erreur lors de l\'affichage du formulaire');
        }
    }

    /**
     * Finalise la décision de commission
     */
    public function finaliserDecision(): void
    {
        $this->requireLogin();
        $this->requirePermission('FINALISATION_DECISION_COMMISSION');

        if ($this->request() === 'POST') {
            $this->traiterFinalisationDecision();
        } else {
            $this->afficherFormulaireDecision();
        }
    }

    private function traiterFinalisationDecision(): void
    {
        try {
            if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
                throw new ValidationException("Token CSRF invalide");
            }

            $idRapport = $this->post('id_rapport');
            if (empty($idRapport)) {
                throw new ValidationException("ID du rapport requis");
            }

            $result = $this->workflowService->finaliserDecisionCommission($idRapport);

            if ($result) {
                $this->setFlashMessage('success', 'Décision de commission finalisée avec succès');
                $this->redirect('/commission/workflow/voir?id=' . $idRapport);
            } else {
                throw new \Exception("Erreur lors de la finalisation de la décision");
            }

        } catch (ValidationException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->afficherFormulaireDecision();
        } catch (\Exception $e) {
            $this->handleError($e, 'Erreur lors de la finalisation de la décision');
        }
    }

    private function afficherFormulaireDecision(): void
    {
        try {
            $idRapport = $this->get('id_rapport');
            if (empty($idRapport)) {
                throw new ValidationException("ID du rapport requis");
            }

            $workflow = $this->workflowService->obtenirEtatWorkflow($idRapport);
            $user = $this->getCurrentUser();

            require_once ROOT_PATH . '/src/Frontend/views/commission/workflow/finaliser-decision.php';

        } catch (\Exception $e) {
            $this->handleError($e, 'Erreur lors de l\'affichage du formulaire');
        }
    }

    /**
     * Génère les documents officiels
     */
    public function genererDocuments(): void
    {
        $this->requireLogin();
        $this->requirePermission('GENERATION_DOCUMENTS_OFFICIELS');

        try {
            if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
                throw new ValidationException("Token CSRF invalide");
            }

            $idRapport = $this->post('id_rapport');
            $typesDocuments = $this->post('types_documents', []);

            if (empty($idRapport)) {
                throw new ValidationException("ID du rapport requis");
            }

            if (empty($typesDocuments)) {
                throw new ValidationException("Au moins un type de document doit être sélectionné");
            }

            $documentsGeneres = $this->workflowService->genererDocumentsOfficiels($idRapport, $typesDocuments);

            $this->setFlashMessage('success', 'Documents générés avec succès');
            $this->redirect('/commission/workflow/voir?id=' . $idRapport);

        } catch (ValidationException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/commission/workflow');
        } catch (\Exception $e) {
            $this->handleError($e, 'Erreur lors de la génération des documents');
        }
    }

    /**
     * Suspend ou annule un workflow
     */
    public function gererSuspensionAnnulation(): void
    {
        $this->requireLogin();
        $this->requirePermission('GESTION_SUSPENSION_WORKFLOW');

        try {
            if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
                throw new ValidationException("Token CSRF invalide");
            }

            $idWorkflow = $this->post('id_workflow');
            $action = $this->post('action');
            $raison = $this->post('raison');

            if (empty($idWorkflow) || empty($action) || empty($raison)) {
                throw new ValidationException("Tous les champs sont requis");
            }

            if (!in_array($action, ['SUSPENDRE', 'ANNULER'])) {
                throw new ValidationException("Action non valide");
            }

            $result = $this->workflowService->gererSuspensionAnnulation($idWorkflow, $action, $raison);

            if ($result) {
                $this->setFlashMessage('success', 'Workflow ' . strtolower($action) . ' avec succès');
                $this->redirect('/commission/workflow/voir?id=' . $idWorkflow);
            } else {
                throw new \Exception("Erreur lors de l'action sur le workflow");
            }

        } catch (ValidationException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/commission/workflow');
        } catch (\Exception $e) {
            $this->handleError($e, 'Erreur lors de l\'action sur le workflow');
        }
    }

    // Méthodes utilitaires privées

    private function obtenirRapportsPretsSession(): array
    {
        // Récupérer les rapports avec état CONFORMITE_VALIDEE ou JURY_AFFECTE
        return $this->workflowService->listerWorkflows([
            'etat_workflow' => ['CONFORMITE_VALIDEE', 'JURY_AFFECTE']
        ], 1, 100)['workflows'];
    }

    private function obtenirPresidentsDisponibles(): array
    {
        // Simulation - récupérer les enseignants pouvant être présidents
        return [];
    }

    private function obtenirEnseignantsDisponibles(): array
    {
        // Simulation - récupérer les enseignants disponibles pour jury
        return [];
    }

    private function handleError(\Exception $e, string $message, int $code = 500): void
    {
        $this->setFlashMessage('error', $message);
        
        if ($e instanceof ElementNonTrouveException) {
            http_response_code(404);
        } elseif ($e instanceof PermissionException) {
            http_response_code(403);
        } else {
            http_response_code($code);
        }

        $this->redirect('/commission/workflow');
    }

    private function setFlashMessage(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
    }
}