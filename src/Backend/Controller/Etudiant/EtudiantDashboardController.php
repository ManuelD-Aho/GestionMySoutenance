<?php
// src/Backend/Controller/Etudiant/EtudiantDashboardController.php

namespace App\Backend\Controller\Etudiant;

use App\Backend\Controller\BaseController;
use App\Backend\Service\WorkflowSoutenance\ServiceWorkflowSoutenanceInterface;
use App\Backend\Service\ParcoursAcademique\ServiceParcoursAcademiqueInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface; // Ajout de la dépendance
use App\Backend\Service\Supervision\ServiceSupervisionInterface; // Ajout de la dépendance
use Exception;

/**
 * Gère l'affichage du tableau de bord principal de l'étudiant.
 */
class EtudiantDashboardController extends BaseController
{
    private ServiceWorkflowSoutenanceInterface $serviceWorkflow;
    private ServiceParcoursAcademiqueInterface $parcoursAcademiqueService;

    public function __construct(
        ServiceWorkflowSoutenanceInterface $serviceWorkflow,
        ServiceParcoursAcademiqueInterface $parcoursAcademiqueService,
        ServiceSecuriteInterface $securiteService, // Injecté pour BaseController
        ServiceSupervisionInterface $supervisionService // Injecté pour BaseController
    ) {
        parent::__construct($securiteService, $supervisionService);
        $this->serviceWorkflow = $serviceWorkflow;
        $this->parcoursAcademiqueService = $parcoursAcademiqueService;
    }

    /**
     * Affiche le tableau de bord avec le statut du rapport et les alertes.
     */
    public function index(): void
    {
        // 1. Permission d'accès
        $this->requirePermission('TRAIT_ETUDIANT_DASHBOARD_ACCEDER');
        $user = $this->securiteService->getUtilisateurConnecte();

        try {
            // 2. Récupérer le rapport de l'année académique active
            $rapportActif = $this->serviceWorkflow->lireRapportPourAnneeActive($user['numero_utilisateur']);

            // 3. Obtenir les données structurées pour le "stepper"
            $workflowSteps = $this->serviceWorkflow->getWorkflowStepsForRapport($rapportActif ? $rapportActif['id_rapport_etudiant'] : null);

            // 4. Vérifier l'éligibilité pour afficher les alertes
            $eligibilite = $this->parcoursAcademiqueService->estEtudiantEligibleSoumission($user['numero_utilisateur']);

            $this->render('Etudiant/dashboard_etudiant', [
                'title' => 'Mon Tableau de Bord',
                'rapportActif' => $rapportActif,
                'workflowSteps' => $workflowSteps,
                'estEligible' => $eligibilite
            ]);

        } catch (Exception $e) {
            // 5. Gestion des erreurs
            error_log("Erreur EtudiantDashboardController::index : " . $e->getMessage());
            $this->renderError(500, "Une erreur est survenue lors du chargement de votre tableau de bord.");
        }
    }
}