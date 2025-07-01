<?php
// src/Backend/Controller/Etudiant/EtudiantDashboardController.php

namespace App\Backend\Controller\Etudiant;

use App\Backend\Controller\BaseController;
use App\Backend\Service\WorkflowSoutenance\ServiceWorkflowSoutenanceInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;

class EtudiantDashboardController extends BaseController
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
     * Affiche le tableau de bord de l'étudiant.
     */
    public function index(): void
    {
        $this->checkPermission('TRAIT_ETUDIANT_DASHBOARD_ACCEDER');
        $user = $this->serviceSecurite->getUtilisateurConnecte();

        try {
            $rapports = $this->serviceWorkflow->listerRapports(['numero_carte_etudiant' => $user['numero_utilisateur']]);
            $rapportActif = !empty($rapports) ? $rapports[0] : null;

            $this->render('Etudiant/dashboard_etudiant.php', [
                'title' => 'Mon Tableau de Bord',
                'rapportActif' => $rapportActif
            ]);
        } catch (\Exception $e) {
            $this->serviceSupervision->enregistrerAction($user['numero_utilisateur'], 'DASHBOARD_ETUDIANT_ERROR', null, null, ['error' => $e->getMessage()]);
            $this->render('errors/500.php', ['error_message' => "Impossible de charger les données de votre tableau de bord."]);
        }
    }
}