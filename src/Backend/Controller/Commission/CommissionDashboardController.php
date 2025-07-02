<?php
// src/Backend/Controller/Commission/CommissionDashboardController.php

namespace App\Backend\Controller\Commission;

use App\Backend\Controller\BaseController;
use App\Backend\Service\WorkflowSoutenance\ServiceWorkflowSoutenanceInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use Exception;

/**
 * Gère l'affichage du tableau de bord pour les membres de la commission.
 */
class CommissionDashboardController extends BaseController
{
    private ServiceWorkflowSoutenanceInterface $serviceWorkflow;

    public function __construct(
        ServiceWorkflowSoutenanceInterface $serviceWorkflow,
        ServiceSecuriteInterface $securiteService,
        ServiceSupervisionInterface $supervisionService
    ) {
        parent::__construct($securiteService, $supervisionService);
        $this->serviceWorkflow = $serviceWorkflow;
    }

    /**
     * Affiche le tableau de bord de la commission.
     * Liste les rapports en attente de vote et les PV en attente d'approbation pour l'utilisateur connecté.
     */
    public function index(): void
    {
        $this->requirePermission('TRAIT_COMMISSION_DASHBOARD_ACCEDER');

        $user = $this->securiteService->getUtilisateurConnecte();
        if (!$user) {
            // Redirection déjà gérée par requirePermission, mais sécurité supplémentaire
            $this->redirect('/login');
            return; // Suppression de l'instruction inaccessible
        }

        try {
            $rapportsAVoter = $this->serviceWorkflow->listerRapports(['votant' => $user['numero_utilisateur'], 'statut' => 'RAP_EN_COMMISSION']);
            $pvsAApprouver = $this->serviceWorkflow->listerPvAApprouver($user['numero_utilisateur']);

            $this->render('Commission/dashboard_commission', [
                'title' => 'Tableau de Bord Commission',
                'rapportsAVoter' => $rapportsAVoter,
                'pvsAApprouver' => $pvsAApprouver,
                'user' => $user
            ]);

        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Une erreur est survenue, le tableau de bord ne peut pas être chargé : ' . $e->getMessage());
            error_log("Erreur CommissionDashboardController::index : " . $e->getMessage());
            $this->renderError(500, "Impossible de charger les données du tableau de bord.");
        }
    }
}
