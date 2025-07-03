<?php
// src/Backend/Controller/PersonnelAdministratif/PersonnelDashboardController.php

namespace App\Backend\Controller\PersonnelAdministratif;

use App\Backend\Controller\BaseController;
use App\Backend\Service\WorkflowSoutenance\ServiceWorkflowSoutenanceInterface;
use App\Backend\Service\Utilisateur\ServiceUtilisateurInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface; // Ajout de la dépendance
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator; // Assurez-vous que cette ligne est présente// Ajout de la dépendance
use Exception;

class PersonnelDashboardController extends BaseController
{
    private ServiceWorkflowSoutenanceInterface $serviceWorkflow;
    private ServiceUtilisateurInterface $serviceUtilisateur;

    public function __construct(
        ServiceWorkflowSoutenanceInterface $serviceWorkflow,
        ServiceUtilisateurInterface $serviceUtilisateur,
        ServiceSecuriteInterface $securiteService, // Injecté pour BaseController
        ServiceSupervisionInterface $supervisionService, // Injecté pour BaseController
        FormValidator $validator // Ajout du FormValidator ici
    ) {
        parent::__construct($securiteService, $supervisionService, $validator);
        $this->serviceWorkflow = $serviceWorkflow;
        $this->serviceUtilisateur = $serviceUtilisateur;
    }

    /**
     * Affiche le tableau de bord pour le personnel administratif.
     * Le contenu est adapté en fonction du rôle (Agent de conformité ou RS).
     */
    public function index(): void
    {
        // 1. Permission d'accès commune
        $this->requirePermission('TRAIT_PERS_ADMIN_DASHBOARD_ACCEDER');
        $user = $this->securiteService->getUtilisateurConnecte();

        $data = ['title' => 'Tableau de Bord Administratif'];

        try {
            // Logique adaptative en fonction du groupe de l'utilisateur
            if ($user['id_groupe_utilisateur'] === 'GRP_AGENT_CONFORMITE') {
                // 2. Pour l'agent de conformité : rapports soumis
                $data['rapportsEnAttente'] = $this->serviceWorkflow->listerRapports(['id_statut_rapport' => 'RAP_SOUMIS']);
            }
            elseif ($user['id_groupe_utilisateur'] === 'GRP_RS') {
                // 3. Pour le RS : étudiants sans compte utilisateur
                $data['etudiantsSansCompte'] = $this->serviceUtilisateur->listerEntitesSansCompte('etudiant');
                // 4. Pour le RS : réclamations ouvertes
                $data['reclamationsOuvertes'] = $this->serviceWorkflow->listerReclamations(['id_statut_reclamation' => 'RECLA_OUVERTE']);
                // Ajoutez ici d'autres données pour le RS si nécessaire (ex: stages à valider)
            }

            $this->render('PersonnelAdministratif/dashboard_personnel', $data);

        } catch (Exception $e) {
            // 5. Gestion des erreurs
            error_log("Erreur PersonnelDashboardController::index : " . $e->getMessage());
            $this->renderError(500, "Une erreur est survenue lors du chargement du tableau de bord.");
        }
    }
}