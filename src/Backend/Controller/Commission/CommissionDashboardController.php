<?php
// src/Backend/Controller/Commission/CommissionDashboardController.php

namespace App\Backend\Controller\Commission;

use App\Backend\Controller\BaseController;
use App\Backend\Service\WorkflowSoutenance\ServiceWorkflowSoutenanceInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface; // Ajout de la dépendance
use App\Backend\Service\Supervision\ServiceSupervisionInterface; // Ajout de la dépendance
use Exception;

/**
 * Gère l'affichage du tableau de bord pour les membres de la commission.
 */
class CommissionDashboardController extends BaseController
{
    private ServiceWorkflowSoutenanceInterface $serviceWorkflow;

    public function __construct(
        ServiceWorkflowSoutenanceInterface $serviceWorkflow,
        ServiceSecuriteInterface $securiteService, // Injecté pour BaseController
        ServiceSupervisionInterface $supervisionService // Injecté pour BaseController
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
        // 1. Permission d'accès au tableau de bord
        $this->requirePermission('TRAIT_COMMISSION_DASHBOARD_ACCEDER');

        $user = $this->securiteService->getUtilisateurConnecte();
        if (!$user) {
            $this->redirect('/login'); // Redirection déjà gérée par requirePermission, mais sécurité supplémentaire
            return;
        }

        try {
            // 2. Récupérer tous les rapports des sessions où l'utilisateur est membre
            // Le tri pour savoir si l'on a déjà voté ou non se fera côté vue pour plus de simplicité.
            // La méthode listerRapports doit être adaptée pour filtrer par votant et statut
            $rapportsAVoter = $this->serviceWorkflow->listerRapports(['votant' => $user['numero_utilisateur'], 'statut' => 'RAP_EN_COMMISSION']);

            // 3. Récupérer les PV avec le statut 'PV_ATTENTE_APPROBATION' où l'approbation de l'utilisateur est manquante.
            $pvsAApprouver = $this->serviceWorkflow->listerPvAApprouver($user['numero_utilisateur']);

            $this->render('Commission/dashboard_commission', [
                'title' => 'Tableau de Bord Commission',
                'rapportsAVoter' => $rapportsAVoter,
                'pvsAApprouver' => $pvsAApprouver,
                'user' => $user
            ]);

        } catch (Exception $e) {
            // 4. Gestion des erreurs
            $this->addFlashMessage('error', 'Une erreur est survenue, le tableau de bord ne peut pas être chargé : ' . $e->getMessage());
            error_log("Erreur CommissionDashboardController::index : " . $e->getMessage());
            $this->renderError(500, "Impossible de charger les données du tableau de bord.");
        }
    }
}