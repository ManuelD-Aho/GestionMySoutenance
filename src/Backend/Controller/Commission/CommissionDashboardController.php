<?php
// src/Backend/Controller/Commission/CommissionDashboardController.php

namespace App\Backend\Controller\Commission;

use App\Backend\Controller\BaseController;
use App\Backend\Service\WorkflowSoutenance\ServiceWorkflowSoutenanceInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;

class CommissionDashboardController extends BaseController
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

    /**
     * Affiche le tableau de bord pour un membre de la commission.
     */
    public function index(): void
    {
        $this->checkPermission('TRAIT_COMMISSION_DASHBOARD_ACCEDER');
        $user = $this->serviceSecurite->getUtilisateurConnecte();

        try {
// Récupérer les rapports en attente de vote pour cet utilisateur
            $rapportsAVoter = $this->serviceWorkflow->listerRapports(['statut' => 'en_commission', 'votant' => $user['numero_utilisateur']]);
// Récupérer les PV en attente d'approbation
            $pvsAApprouver = $this->serviceWorkflow->listerPvAApprouver($user['numero_utilisateur']);

            $this->render('Commission/dashboard_commission.php', [
                'title' => 'Tableau de Bord Commission',
                'rapportsAVoter' => $rapportsAVoter,
                'pvsAApprouver' => $pvsAApprouver
            ]);
        } catch (\Exception $e) {
            $this->serviceSupervision->enregistrerAction($user['numero_utilisateur'], 'DASHBOARD_COMMISSION_ERROR', null, null, ['error' => $e->getMessage()]);
            $this->render('errors/500.php', ['error_message' => "Impossible de charger les données du tableau de bord."]);
        }
    }
}
