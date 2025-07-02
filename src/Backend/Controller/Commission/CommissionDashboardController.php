<?php
// src/Backend/Controller/Commission/CommissionDashboardController.php

namespace App\Backend\Controller\Commission;

use App\Backend\Controller\BaseController;
use App\Config\Container;
use App\Backend\Service\WorkflowSoutenance\ServiceWorkflowSoutenanceInterface;

class CommissionDashboardController extends BaseController
{
    private ServiceWorkflowSoutenanceInterface $serviceWorkflow;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->serviceWorkflow = $container->get(ServiceWorkflowSoutenanceInterface::class);
    }

    public function index(): void
    {
        $this->requirePermission('TRAIT_COMMISSION_DASHBOARD_ACCEDER');
        $user = $this->securiteService->getUtilisateurConnecte();

        try {
            $rapportsAVoter = $this->serviceWorkflow->listerRapports(['statut' => 'en_commission', 'votant' => $user['numero_utilisateur']]);
            $pvsAApprouver = $this->serviceWorkflow->listerPvAApprouver($user['numero_utilisateur']);

            $this->render('Commission/dashboard_commission', [
                'title' => 'Tableau de Bord Commission',
                'rapportsAVoter' => $rapportsAVoter,
                'pvsAApprouver' => $pvsAApprouver
            ]);
        } catch (\Exception $e) {
            $this->supervisionService->enregistrerAction($user['numero_utilisateur'], 'DASHBOARD_COMMISSION_ERROR', null, null, ['error' => $e->getMessage()]);
            $this->renderError(500, "Impossible de charger les données du tableau de bord.");
        }
    }
}