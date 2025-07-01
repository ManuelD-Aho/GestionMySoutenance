<?php
// src/Backend/Controller/PersonnelAdministratif/ScolariteController.php

namespace App\Backend\Controller\PersonnelAdministratif;

use App\Backend\Controller\BaseController;
use App\Backend\Service\WorkflowSoutenance\ServiceWorkflowSoutenanceInterface;
use App\Backend\Service\Utilisateur\ServiceUtilisateurInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;

class ScolariteController extends BaseController
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

    public function listConformiteQueue(): void
    {
        $this->checkPermission('TRAIT_PERS_ADMIN_CONFORMITE_LISTER');
        $rapports = $this->serviceWorkflow->listerRapports(['id_statut_rapport' => 'RAP_SOUMIS']);
        $this->render('PersonnelAdministratif/gestion_conformite.php', [
            'title' => 'File de Vérification de Conformité',
            'rapports' => $rapports
        ]);
    }

    public function showConformiteForm(string $id): void
    {
        $this->checkPermission('TRAIT_PERS_ADMIN_CONFORMITE_VERIFIER');
        $rapport = $this->serviceWorkflow->lireRapportComplet($id);
        if (!$rapport) {
            $this->render('errors/404.php');
            return;
        }
        $this->render('PersonnelAdministratif/form_conformite.php', [
            'title' => 'Vérification du Rapport',
            'rapport' => $rapport
        ]);
    }

    public function processConformite(string $id): void
    {
        $this->checkPermission('TRAIT_PERS_ADMIN_CONFORMITE_VERIFIER');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['success' => false, 'message' => 'Requête invalide.'], 403);
            return;
        }

        $user = $this->serviceSecurite->getUtilisateurConnecte();
        try {
            $estConforme = ($_POST['decision_conformite'] === 'conforme');
            $details = $_POST['checklist'] ?? [];
            $commentaire = $_POST['commentaire_general'] ?? null;
            $this->serviceWorkflow->traiterVerificationConformite($id, $user['numero_utilisateur'], $estConforme, $details, $commentaire);
            $this->jsonResponse(['success' => true, 'message' => 'Vérification enregistrée.', 'redirect' => '/personnel/conformite']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function listStudentRecords(): void
    {
        $this->checkPermission('TRAIT_PERS_ADMIN_SCOLARITE_ACCEDER');
        $etudiants = $this->serviceUtilisateur->listerUtilisateursComplets(['id_type_utilisateur' => 'TYPE_ETUD']);
        $this->render('PersonnelAdministratif/gestion_scolarite.php', [
            'title' => 'Gestion des Dossiers Étudiants',
            'etudiants' => $etudiants
        ]);
    }

    public function activateStudentAccount(): void
    {
        $this->checkPermission('PERS_ADMIN_ACTIVATE_ACCOUNT');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['success' => false, 'message' => 'Requête invalide.'], 403);
            return;
        }

        $numeroEtudiant = $_POST['numero_etudiant'];
        try {
// Le service doit vérifier les prérequis (paiement, stage) avant d'activer
            $this->serviceUtilisateur->activerComptePourEntite($numeroEtudiant, $_POST, true);
            $this->jsonResponse(['success' => true, 'message' => 'Compte étudiant activé avec succès.']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}