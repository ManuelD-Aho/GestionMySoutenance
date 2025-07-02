<?php
// src/Backend/Controller/Etudiant/RapportController.php

namespace App\Backend\Controller\Etudiant;

use App\Backend\Controller\BaseController;
use App\Backend\Service\WorkflowSoutenance\ServiceWorkflowSoutenanceInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface; // Ajout de la dépendance
use App\Backend\Service\Supervision\ServiceSupervisionInterface; // Ajout de la dépendance
use App\Backend\Util\FormValidator;
use Exception;

/**
 * Gère la création, la rédaction, la sauvegarde et la soumission du rapport de l'étudiant.
 */
class RapportController extends BaseController
{
    private ServiceWorkflowSoutenanceInterface $serviceWorkflow;
    private FormValidator $validator;

    public function __construct(
        ServiceWorkflowSoutenanceInterface $serviceWorkflow,
        FormValidator $validator,
        ServiceSecuriteInterface $securiteService, // Injecté pour BaseController
        ServiceSupervisionInterface $supervisionService // Injecté pour BaseController
    ) {
        parent::__construct($securiteService, $supervisionService);
        $this->serviceWorkflow = $serviceWorkflow;
        $this->validator = $validator;
    }

    /**
     * Point d'entrée pour la gestion du rapport.
     * Redirige vers le choix du modèle si aucun rapport n'existe, sinon vers l'éditeur.
     */
    public function edit(): void // Renommée de showChoiceOrRedirect
    {
        $this->requirePermission('TRAIT_ETUDIANT_RAPPORT_SUIVRE');
        $user = $this->securiteService->getUtilisateurConnecte();
        $rapport = $this->serviceWorkflow->lireRapportPourAnneeActive($user['numero_utilisateur']);

        if ($rapport) {
            $this->redirect('/etudiant/rapport/redaction/' . $rapport['id_rapport_etudiant']);
        } else {
            // 12. Affiche la page de choix du modèle
            $modeles = $this->serviceWorkflow->listerModelesRapportDisponibles();
            $this->render('Etudiant/choix_modele', [
                'title' => 'Choisir un Modèle de Rapport',
                'modeles' => $modeles,
                'csrf_token' => $this->generateCsrfToken('choix_modele_form')
            ]);
        }
    }

    /**
     * Crée un rapport à partir d'un modèle choisi et redirige vers l'éditeur.
     */
    public function create(): void // Renommée de handleCreateFromTemplate
    {
        $this->requirePermission('TRAIT_ETUDIANT_RAPPORT_SOUMETTRE');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('choix_modele_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/etudiant/rapport/redaction'); // Redirection vers la page de choix
            return;
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
            $this->redirect('/etudiant/rapport/redaction'); // Redirection vers la page de choix
        }
    }

    /**
     * Affiche le formulaire de rédaction/visualisation du rapport.
     */
    public function show(string $idRapport): void // Renommée de showRapportForm
    {
        // 11. Permission d'accès
        $this->requirePermission('TRAIT_ETUDIANT_RAPPORT_SUIVRE');
        $user = $this->securiteService->getUtilisateurConnecte();
        $rapport = $this->serviceWorkflow->lireRapportComplet($idRapport);

        if (!$rapport || $rapport['numero_carte_etudiant'] !== $user['numero_utilisateur']) {
            $this->renderError(403, "Vous n'êtes pas autorisé à accéder à ce rapport.");
            return;
        }

        // 16. Verrouillage du rapport si son statut n'est pas Brouillon ou En Correction.
        $isLocked = !in_array($rapport['id_statut_rapport'], ['RAP_BROUILLON', 'RAP_CORRECT']);

        $this->render('Etudiant/redaction_rapport', [
            'title' => 'Mon Rapport',
            'rapport' => $rapport,
            'isLocked' => $isLocked,
            'csrf_token_save' => $this->generateCsrfToken('save_rapport_form'),
            'csrf_token_submit' => $this->generateCsrfToken('submit_rapport_form'),
            'csrf_token_submit_corrections' => $this->generateCsrfToken('submit_corrections_form') // Pour le formulaire de corrections
        ]);
    }

    /**
     * Sauvegarde le brouillon du rapport (appel AJAX).
     */
    public function save(string $idRapport): void // Renommée de saveRapport
    {
        $this->requirePermission('TRAIT_ETUDIANT_RAPPORT_SUIVRE');
        if (!$this->isPostRequest()) {
            $this->jsonResponse(['success' => false, 'message' => 'Méthode non autorisée.'], 405);
            return;
        }

        try {
            $user = $this->securiteService->getUtilisateurConnecte();
            $data = json_decode(file_get_contents('php://input'), true);

            // 13. Le service sauvegarde et met à jour le timestamp.
            $this->serviceWorkflow->creerOuMettreAJourBrouillon($user['numero_utilisateur'], $data['metadonnees'], $data['sections']);

            // 19. Réponse JSON
            $this->jsonResponse(['success' => true, 'message' => 'Brouillon sauvegardé.']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Soumet le rapport final pour validation (appel AJAX).
     */
    public function submit(): void // Renommée de submitRapport
    {
        // 14. Permission de soumission
        $this->requirePermission('TRAIT_ETUDIANT_RAPPORT_SOUMETTRE');
        if (!$this->isPostRequest()) {
            $this->jsonResponse(['success' => false, 'message' => 'Méthode non autorisée.'], 405);
            return;
        }

        $data = $this->getPostData();
        $idRapport = $data['id_rapport'] ?? '';

        if (!$this->validateCsrfToken('submit_rapport_form', $data['csrf_token'] ?? '')) {
            $this->jsonResponse(['success' => false, 'message' => 'Jeton de sécurité invalide.'], 403);
            return;
        }

        try {
            $user = $this->securiteService->getUtilisateurConnecte();
            // 15. Le service gère la vérification d'éligibilité.
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
        // 17. Méthode dédiée pour les corrections.
        $this->requirePermission('TRAIT_ETUDIANT_RAPPORT_SOUMETTRE');
        if (!$this->isPostRequest()) {
            $this->jsonResponse(['success' => false, 'message' => 'Méthode non autorisée.'], 405);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true); // Récupérer les données JSON
        $noteExplicative = $data['note_explicative'] ?? '';
        $sections = $data['sections'] ?? [];

        if (!$this->validateCsrfToken('submit_corrections_form', $data['csrf_token'] ?? '')) {
            $this->jsonResponse(['success' => false, 'message' => 'Jeton de sécurité invalide.'], 403);
            return;
        }

        // 18. Validation de la note explicative
        if (empty($noteExplicative)) {
            $this->jsonResponse(['success' => false, 'message' => 'Une note expliquant les corrections est obligatoire.'], 422);
            return;
        }

        try {
            $user = $this->securiteService->getUtilisateurConnecte();
            // Le service gère la logique de mise à jour du contenu et du statut
            $this->serviceWorkflow->soumettreCorrections($idRapport, $user['numero_utilisateur'], $sections, $noteExplicative);
            $this->jsonResponse(['success' => true, 'message' => 'Corrections soumises avec succès !', 'redirect' => '/etudiant/dashboard']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}