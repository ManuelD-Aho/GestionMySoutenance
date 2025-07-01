<?php
// src/Backend/Controller/PersonnelAdministratif/PersonnelDashboardController.php

namespace App\Backend\Controller\PersonnelAdministratif;

use App\Backend\Controller\BaseController;
use App\Backend\Service\WorkflowSoutenance\ServiceWorkflowSoutenanceInterface;
use App\Backend\Service\Utilisateur\ServiceUtilisateurInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;

class PersonnelDashboardController extends BaseController
{
    private ServiceWorkflowSoutenanceInterface $serviceWorkflow;
    private ServiceUtilisateurInterface $serviceUtilisateur;

    public function __construct(
        ServiceSecuriteInterface $serviceSecurite,
        ServiceSupervisionInterface $serviceSupervision,
        FormValidator $formValidator,
        ServiceWorkflowSoutenanceInterface $serviceWorkflow,
        ServiceUtilisateurInterface $serviceUtilisateur
    ) {
        parent::__construct($serviceSecurite, $serviceSupervision, $formValidator);
        $this->serviceWorkflow = $serviceWorkflow;
        $this->serviceUtilisateur = $serviceUtilisateur;
    }

    /**
     * Affiche le tableau de bord pour le personnel administratif.
     * Le contenu est adapté en fonction du rôle (Agent de conformité ou RS).
     */
    public function index(): void
    {
        $this->checkPermission('TRAIT_PERS_ADMIN_DASHBOARD_ACCEDER');
        $user = $this->serviceSecurite->getUtilisateurConnecte();
        $data = ['title' => 'Tableau de Bord Administratif'];

        try {
            if ($user['id_groupe_utilisateur'] === 'GRP_AGENT_CONFORMITE') {
                $data['rapportsEnAttente'] = $this->serviceWorkflow->listerRapports(['id_statut_rapport' => 'RAP_SOUMIS']);
            } elseif ($user['id_groupe_utilisateur'] === 'GRP_RS') {
                $data['etudiantsAActiver'] = $this->serviceUtilisateur->listerUtilisateursComplets(['statut_compte' => 'en_attente_activation']);
            }
            $this->render('PersonnelAdministratif/dashboard_personnel.php', $data);
        } catch (\Exception $e) {
            $this->serviceSupervision->enregistrerAction($user['numero_utilisateur'], 'DASHBOARD_PERSONNEL_ERROR', null, null, ['error' => $e->getMessage()]);
            $this->render('errors/500.php', ['error_message' => "Impossible de charger les données du tableau de bord."]);
        }
    }
}
