<?php
// src/Backend/Controller/PersonnelAdministratif/ScolariteController.php

namespace App\Backend\Controller\PersonnelAdministratif;

use App\Backend\Controller\BaseController;
use App\Backend\Service\WorkflowSoutenance\ServiceWorkflowSoutenanceInterface;
use App\Backend\Service\Utilisateur\ServiceUtilisateurInterface;
use App\Backend\Service\ParcoursAcademique\ServiceParcoursAcademiqueInterface;
use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Backend\Service\Document\ServiceDocumentInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;
use Exception;

class ScolariteController extends BaseController
{
    private ServiceWorkflowSoutenanceInterface $serviceWorkflow;
    private ServiceUtilisateurInterface $serviceUtilisateur;
    private ServiceParcoursAcademiqueInterface $parcoursService;
    private ServiceSystemeInterface $systemeService;
    private ServiceDocumentInterface $documentService;

    public function __construct(
        ServiceWorkflowSoutenanceInterface $serviceWorkflow,
        ServiceUtilisateurInterface $serviceUtilisateur,
        ServiceParcoursAcademiqueInterface $parcoursService,
        ServiceSystemeInterface $systemeService,
        ServiceDocumentInterface $documentService,
        FormValidator $validator,
        ServiceSecuriteInterface $securiteService,
        ServiceSupervisionInterface $supervisionService
    ) {
        parent::__construct($securiteService, $supervisionService, $validator); // Passer $validator au parent
        $this->serviceWorkflow = $serviceWorkflow;
        $this->serviceUtilisateur = $serviceUtilisateur;
        $this->parcoursService = $parcoursService;
        $this->systemeService = $systemeService;
        $this->documentService = $documentService;
    }

    // ========== PARTIE AGENT DE CONFORMITÉ ==========

    public function conformiteQueue(): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_CONFORMITE_LISTER');
        try {
            $rapports = $this->serviceWorkflow->listerRapports(['id_statut_rapport' => 'RAP_SOUMIS']);
            $this->render('PersonnelAdministratif/gestion_conformite', [
                'title' => 'File de Vérification de Conformité',
                'rapports' => $rapports
            ]);
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur lors du chargement de la file de conformité : ' . $e->getMessage());
            $this->redirect('/personnel/dashboard');
            return;
        }
    }

    public function showConformite(string $idRapport): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_CONFORMITE_VERIFIER');
        try {
            $rapport = $this->serviceWorkflow->lireRapportComplet($idRapport);
            if (!$rapport) {
                throw new Exception("Rapport non trouvé.");
            }
            $checklist = $this->systemeService->gererReferentiel('list', 'critere_conformite_ref');

            $this->render('PersonnelAdministratif/form_conformite', [
                'title' => 'Vérification du Rapport ' . $idRapport,
                'rapport' => $rapport,
                'checklist' => $checklist,
                'csrf_token' => $this->generateCsrfToken('conformite_form')
            ]);
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur lors du chargement du formulaire de conformité : ' . $e->getMessage());
            $this->redirect('/personnel/conformite/queue');
            return;
        }
    }

    public function processConformite(string $idRapport): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_CONFORMITE_VERIFIER');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('conformite_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/personnel/conformite/queue');
            return;
        }

        $data = $this->getPostData();
        if (empty($data['commentaire_general'])) {
            $this->addFlashMessage('error', 'Un commentaire général est obligatoire pour toute décision.');
            $this->redirect("/personnel/conformite/verifier/{$idRapport}");
            return;
        }

        try {
            $user = $this->securiteService->getUtilisateurConnecte();
            $this->serviceWorkflow->traiterVerificationConformite($idRapport, $user['numero_utilisateur'], ($data['decision_conformite'] === 'conforme'), $data['checklist'] ?? [], $data['commentaire_general']);
            $this->addFlashMessage('success', 'La vérification de conformité a été enregistrée.');
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur lors du traitement de la conformité: ' . $e->getMessage());
        }
        $this->redirect('/personnel/conformite/queue');
    }

    // ========== PARTIE RESPONSABLE SCOLARITÉ (RS) ==========

    public function index(): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_SCOLARITE_ACCEDER');
        try {
            $etudiants = $this->serviceUtilisateur->listerUtilisateursComplets(['id_type_utilisateur' => 'TYPE_ETUD']);

            $this->render('PersonnelAdministratif/gestion_scolarite', [
                'title' => 'Gestion des Dossiers Étudiants',
                'etudiants' => $etudiants,
                'csrf_token_activate' => $this->generateCsrfToken('activate_account_form'),
                'csrf_token_inscription' => $this->generateCsrfToken('inscription_form'),
                'csrf_token_note' => $this->generateCsrfToken('note_form'),
                'csrf_token_stage' => $this->generateCsrfToken('stage_form'),
                'csrf_token_penalite' => $this->generateCsrfToken('penalite_form'),
                'csrf_token_reclamation' => $this->generateCsrfToken('reclamation_form'),
                'csrf_token_export' => $this->generateCsrfToken('export_form')
            ]);
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur lors du chargement des dossiers étudiants : ' . $e->getMessage());
            $this->redirect('/personnel/dashboard');
            return;
        }
    }

    public function showStudent(string $idEtudiant): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_SCOLARITE_ACCEDER');
        try {
            $data = [
                'profil' => $this->serviceUtilisateur->lireUtilisateurComplet($idEtudiant),
                'inscriptions' => $this->parcoursService->listerInscriptions(['numero_carte_etudiant' => $idEtudiant]),
                'notes' => $this->parcoursService->listerNotes(['numero_carte_etudiant' => $idEtudiant]),
                'stages' => $this->parcoursService->listerStages(['numero_carte_etudiant' => $idEtudiant]),
                'penalites' => $this->parcoursService->listerPenalites(['numero_carte_etudiant' => $idEtudiant]),
                'reclamations' => $this->serviceWorkflow->listerReclamations(['numero_carte_etudiant' => $idEtudiant])
            ];
            $this->render('PersonnelAdministratif/_student_details_panel', $data, false);
        } catch (Exception $e) {
            http_response_code(500);
            echo "Erreur lors du chargement des détails de l'étudiant : " . htmlspecialchars($e->getMessage());
        }
    }

    public function activateAccount(): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_SCOLARITE_ETUDIANT_GERER');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('activate_account_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/personnel/scolarite');
            return;
        }

        $data = $this->getPostData();
        $numeroEtudiant = $data['numero_etudiant'] ?? null;
        $login = $data['login_utilisateur'] ?? null;
        $email = $data['email_principal'] ?? null;
        $password = $data['mot_de_passe'] ?? null;

        if (empty($numeroEtudiant) || empty($login) || empty($email) || empty($password)) {
            $this->addFlashMessage('error', 'Tous les champs sont requis pour activer le compte.');
            $this->redirect('/personnel/scolarite');
            return;
        }

        try {
            $donneesCompte = [
                'login_utilisateur' => $login,
                'email_principal' => $email,
                'mot_de_passe' => $password,
                'id_groupe_utilisateur' => 'GRP_ETUDIANT',
                'id_niveau_acces_donne' => 'ACCES_PERSONNEL'
            ];
            $this->serviceUtilisateur->activerComptePourEntite($numeroEtudiant, $donneesCompte);
            $this->addFlashMessage('success', "Compte de l'étudiant {$numeroEtudiant} activé avec succès.");
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur lors de l\'activation du compte : ' . $e->getMessage());
        }
        $this->redirect('/personnel/scolarite');
    }

    public function handleInscriptionUpdate(): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_SCOLARITE_ETUDIANT_GERER');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('inscription_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/personnel/scolarite');
            return;
        }

        $data = $this->getPostData();
        try {
            $this->parcoursService->mettreAJourInscription($data['numero_etudiant'], $data['id_niveau'], $data['id_annee'], ['id_statut_paiement' => $data['statut']]);
            $this->addFlashMessage('success', 'Statut de paiement mis à jour.');
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        }
        $this->redirect('/personnel/scolarite');
    }

    public function handleNoteEntry(): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_SCOLARITE_ETUDIANT_GERER');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('note_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/personnel/scolarite');
            return;
        }

        try {
            $this->parcoursService->creerOuMettreAJourNote($this->getPostData());
            $this->addFlashMessage('success', 'Note enregistrée avec succès.');
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        }
        $this->redirect('/personnel/scolarite');
    }

    public function validerStage(string $numeroEtudiant, string $idEntreprise): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_SCOLARITE_ETUDIANT_GERER');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('stage_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/personnel/scolarite');
            return;
        }

        try {
            $this->parcoursService->validerStage($numeroEtudiant, $idEntreprise);
            $this->addFlashMessage('success', 'Stage validé.');
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        }
        $this->redirect('/personnel/scolarite');
    }

    public function regulariserPenalite(string $idPenalite): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_SCOLARITE_PENALITE_GERER');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('penalite_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/personnel/scolarite');
            return;
        }

        try {
            $user = $this->securiteService->getUtilisateurConnecte();
            $this->parcoursService->regulariserPenalite($idPenalite, $user['numero_utilisateur']);
            $this->addFlashMessage('success', 'Pénalité régularisée.');
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        }
        $this->redirect('/personnel/scolarite');
    }

    public function handleReponseReclamation(string $idReclamation): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_RECLAMATIONS_GERER');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('reclamation_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/personnel/scolarite');
            return;
        }

        $data = $this->getPostData();
        try {
            $user = $this->securiteService->getUtilisateurConnecte();
            $this->serviceWorkflow->repondreAReclamation($idReclamation, $data['reponse'], $user['numero_utilisateur']);
            $this->addFlashMessage('success', 'Réponse envoyée.');
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        }
        $this->redirect('/personnel/scolarite');
    }

    public function cloturerReclamation(string $idReclamation): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_RECLAMATIONS_GERER');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('reclamation_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/personnel/scolarite');
            return;
        }

        try {
            $this->serviceWorkflow->traiterReclamation($idReclamation, "Réclamation résolue et clôturée.", $_SESSION['user_id']);
            $this->addFlashMessage('success', 'Réclamation clôturée.');
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        }
        $this->redirect('/personnel/scolarite');
    }

    public function exportStudents(string $format): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_SCOLARITE_ACCEDER');

        try {
            $etudiants = $this->serviceUtilisateur->listerUtilisateursComplets(['id_type_utilisateur' => 'TYPE_ETUD']);
            $colonnes = ['numero_utilisateur' => 'Matricule', 'nom' => 'Nom', 'prenom' => 'Prénom', 'email_principal' => 'Email', 'statut_compte' => 'Statut'];

            if ($format === 'pdf') {
                $this->documentService->genererListePdf('Liste des Etudiants', $etudiants, $colonnes);
                $this->addFlashMessage('success', 'Liste des étudiants exportée en PDF.');
            } elseif ($format === 'csv') {
                $this->genererListeCsv('etudiants', $etudiants, $colonnes);
                $this->addFlashMessage('success', 'Liste des étudiants exportée en CSV.');
            } else {
                $this->renderError(400, 'Format d\'export non supporté.');
            }
        } catch (Exception $e) {
            $this->renderError(500, "Erreur lors de l'export : " . $e->getMessage());
        }
    }

    private function genererListeCsv(string $filename, array $data, array $columns): void
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Ymd_His') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fputcsv($output, array_values($columns));

        foreach ($data as $row) {
            $csvRow = [];
            foreach (array_keys($columns) as $key) {
                $csvRow[] = $row[$key] ?? '';
            }
            fputcsv($output, $csvRow);
        }
        fclose($output);
        exit();
    }
}