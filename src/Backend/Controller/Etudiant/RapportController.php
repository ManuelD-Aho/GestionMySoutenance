<?php
// src/Backend/Controller/Etudiant/RapportController.php

namespace App\Backend\Controller\Etudiant;

use App\Backend\Controller\BaseController;
use App\Backend\Service\WorkflowSoutenance\ServiceWorkflowSoutenanceInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;

class RapportController extends BaseController
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
     * Affiche le formulaire de rédaction/édition du rapport.
     */
    public function showRapportForm(): void
    {
        $this->checkPermission('TRAIT_ETUDIANT_RAPPORT_SUIVRE');
        $user = $this->serviceSecurite->getUtilisateurConnecte();
        $rapport = $this->serviceWorkflow->lireRapportCompletParEtudiant($user['numero_utilisateur']);
        $this->render('Etudiant/redaction_rapport.php', [
            'title' => 'Mon Rapport',
            'rapport' => $rapport
        ]);
    }

    /**
     * Sauvegarde le brouillon du rapport.
     */
    public function saveRapport(): void
    {
        $this->checkPermission('ETUDIANT_RAPPORT_EDIT');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['success' => false, 'message' => 'Requête invalide.'], 403);
            return;
        }

        $user = $this->serviceSecurite->getUtilisateurConnecte();
        try {
            $metadonnees = ['libelle_rapport_etudiant' => $_POST['titre'], 'theme' => $_POST['theme']];
            $sections = $_POST['sections'] ?? []; // Supposant que les sections sont envoyées sous forme de tableau
            $this->serviceWorkflow->creerOuMettreAJourBrouillon($user['numero_utilisateur'], $metadonnees, $sections);
            $this->jsonResponse(['success' => true, 'message' => 'Brouillon sauvegardé.']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Soumet le rapport pour validation.
     */
    public function submitRapport(): void
    {
        $this->checkPermission('TRAIT_ETUDIANT_RAPPORT_SOUMETTRE');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['success' => false, 'message' => 'Requête invalide.'], 403);
            return;
        }

        $user = $this->serviceSecurite->getUtilisateurConnecte();
        $idRapport = $_POST['id_rapport'];
        try {
            $this->serviceWorkflow->soumettreRapport($idRapport, $user['numero_utilisateur']);
            $this->jsonResponse(['success' => true, 'message' => 'Rapport soumis avec succès !', 'redirect' => '/etudiant/dashboard']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}