<?php
// src/Backend/Controller/Etudiant/RapportController.php

namespace App\Backend\Controller\Etudiant;

use App\Backend\Controller\BaseController;
use App\Backend\Service\WorkflowSoutenance\ServiceWorkflowSoutenanceInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator; // Garder l'import pour le constructeur
use Exception;

/**
 * Gère la création, la rédaction, la sauvegarde et la soumission du rapport de l'étudiant.
 */
class RapportController extends BaseController
{
    private ServiceWorkflowSoutenanceInterface $serviceWorkflow;
    // Suppression de la déclaration de propriété $validator
    // car elle est déjà disponible via BaseController::$validator (si BaseController l'injecte)
    // Ou si FormValidator est utilisé directement dans les méthodes de cette classe, il faut la déclarer ici.
    // Pour l'instant, je suppose qu'il est utilisé via BaseController.

    public function __construct(
        ServiceWorkflowSoutenanceInterface $serviceWorkflow,
        FormValidator $validator, // Injecté pour BaseController
        ServiceSecuriteInterface $securiteService, // Injecté pour BaseController
        ServiceSupervisionInterface $supervisionService // Injecté pour BaseController
    ) {
        parent::__construct($securiteService, $supervisionService);
        $this->serviceWorkflow = $serviceWorkflow;
        // Pas besoin de réassigner $this->validator ici si BaseController le fait
    }

    /**
     * Point d'entrée pour la gestion du rapport.
     * Redirige vers le choix du modèle si aucun rapport n'existe, sinon vers l'éditeur.
     */
    public function edit(): void
    {
        $this->requirePermission('TRAIT_ETUDIANT_RAPPORT_SUIVRE');
        $user = $this->securiteService->getUtilisateurConnecte();
        try {
            $rapport = $this->serviceWorkflow->lireRapportPourAnneeActive($user['numero_utilisateur']);

            if ($rapport) {
                $this->redirect('/etudiant/rapport/redaction/' . $rapport['id_rapport_etudiant']);
                return; // Suppression de l'instruction inaccessible
            } else {
                $modeles = $this->serviceWorkflow->listerModelesRapportDisponibles();
                $this->render('Etudiant/choix_modele', [
                    'title' => 'Choisir un Modèle de Rapport',
                    'modeles' => $modeles,
                    'csrf_token' => $this->generateCsrfToken('choix_modele_form')
                ]);
            }
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur lors du chargement de la page de rédaction : ' . $e->getMessage());
            $this->redirect('/etudiant/dashboard'); // Redirection vers le dashboard en cas d'erreur
        }
    }

    /**
     * Crée un rapport à partir d'un modèle choisi et redirige vers l'éditeur.
     */
    public function create(): void
    {
        $this->requirePermission('TRAIT_ETUDIANT_RAPPORT_SOUMETTRE');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('choix_modele_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/etudiant/rapport/redaction');
            return; // Suppression de l'instruction inaccessible
        }

        try {
            $user = $this->securiteService->getUtilisateurConnecte();
            $idModele = $_POST['id_modele'] ?? null;
            if (empty($idModele)) {
                throw new Exception("Aucun modèle sélectionné.");
            }
            $idRapport = $this->serviceWorkflow->creerRapportDepuisModele($user['numero_utilisateur'], $idModele);
            $this->redirect('/etudiant/rapport/redaction/' . $idRapport);
        } catch (Exception $e) {
            $this->addFlashMessage('error', "Impossible d'initialiser le rapport : " . $e->getMessage());
            $this->redirect('/etudiant/rapport/redaction');
            return; // Suppression de l'instruction inaccessible
        }
    }

    /**
     * Affiche le formulaire de rédaction/visualisation du rapport.
     */
    public function show(string $idRapport): void
    {
        $this->requirePermission('TRAIT_ETUDIANT_RAPPORT_SUIVRE');
        $user = $this->securiteService->getUtilisateurConnecte();
        try {
            $rapport = $this->serviceWorkflow->lireRapportComplet($idRapport);

            if (!$rapport || $rapport['numero_carte_etudiant'] !== $user['numero_utilisateur']) {
                $this->renderError(403, "Vous n'êtes pas autorisé à accéder à ce rapport.");
                return; // Suppression de l'instruction inaccessible
            }

            $isLocked = !in_array($rapport['id_statut_rapport'], ['RAP_BROUILLON', 'RAP_CORRECT']);

            $this->render('Etudiant/redaction_rapport', [
                'title' => 'Mon Rapport',
                'rapport' => $rapport,
                'isLocked' => $isLocked,
                'csrf_token_save' => $this->generateCsrfToken('save_rapport_form'),
                'csrf_token_submit' => $this->generateCsrfToken('submit_rapport_form'),
                'csrf_token_submit_corrections' => $this->generateCsrfToken('submit_corrections_form')
            ]);
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur lors du chargement du rapport : ' . $e->getMessage());
            $this->redirect('/etudiant/dashboard'); // Redirection vers le dashboard en cas d'erreur
        }
    }

    /**
     * Sauvegarde le brouillon du rapport (appel AJAX).
     */
    public function save(string $idRapport): void
    {
        $this->requirePermission('TRAIT_ETUDIANT_RAPPORT_SUIVRE');
        if (!$this->isPostRequest()) {
            $this->jsonResponse(['success' => false, 'message' => 'Méthode non autorisée.'], 405);
            return; // Suppression de l'instruction inaccessible
        }

        try {
            $user = $this->securiteService->getUtilisateurConnecte();
            $data = json_decode(file_get_contents('php://input'), true);

            $this->serviceWorkflow->creerOuMettreAJourBrouillon($user['numero_utilisateur'], $data['metadonnees'], $data['sections']);

            $this->jsonResponse(['success' => true, 'message' => 'Brouillon sauvegardé.']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Soumet le rapport final pour validation (appel AJAX).
     */
    public function submit(): void
    {
        $this->requirePermission('TRAIT_ETUDIANT_RAPPORT_SOUMETTRE');
        if (!$this->isPostRequest()) {
            $this->jsonResponse(['success' => false, 'message' => 'Méthode non autorisée.'], 405);
            return; // Suppression de l'instruction inaccessible
        }

        $data = $this->getPostData();
        $idRapport = $data['id_rapport'] ?? '';

        if (!$this->validateCsrfToken('submit_rapport_form', $data['csrf_token'] ?? '')) {
            $this->jsonResponse(['success' => false, 'message' => 'Jeton de sécurité invalide.'], 403);
            return; // Suppression de l'instruction inaccessible
        }

        try {
            $user = $this->securiteService->getUtilisateurConnecte();
            $this->serviceWorkflow->soumettreRapport($idRapport, $user['numero_utilisateur']);
            $this->jsonResponse(['success' => true, 'message' => 'Rapport soumis avec succès !', 'redirect' => '/etudiant/dashboard']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Soumet les corrections demandées pour un rapport (appel AJAX).
     */
    public function submitCorrections(string $idRapport): void
    {
        $this->requirePermission('TRAIT_ETUDIANT_RAPPORT_SOUMETTRE');
        if (!$this->isPostRequest()) {
            $this->jsonResponse(['success' => false, 'message' => 'Méthode non autorisée.'], 405);
            return; // Suppression de l'instruction inaccessible
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $noteExplicative = $data['note_explicative'] ?? '';
        $sections = $data['sections'] ?? [];

        if (!$this->validateCsrfToken('submit_corrections_form', $data['csrf_token'] ?? '')) {
            $this->jsonResponse(['success' => false, 'message' => 'Jeton de sécurité invalide.'], 403);
            return; // Suppression de l'instruction inaccessible
        }

        if (empty($noteExplicative)) {
            $this->jsonResponse(['success' => false, 'message' => 'Une note expliquant les corrections est obligatoire.'], 422);
            return; // Suppression de l'instruction inaccessible
        }

        try {
            $user = $this->securiteService->getUtilisateurConnecte();
            $this->serviceWorkflow->soumettreCorrections($idRapport, $user['numero_utilisateur'], $sections, $noteExplicative);
            $this->jsonResponse(['success' => true, 'message' => 'Corrections soumises avec succès !', 'redirect' => '/etudiant/dashboard']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}