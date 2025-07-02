<?php
// src/Backend/Controller/Administration/ConfigurationController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Config\Container;
use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Backend\Service\Document\ServiceDocumentInterface;
use App\Backend\Service\Communication\ServiceCommunicationInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Exception\{OperationImpossibleException, ValidationException};
use Exception;

/**
 * Gère l'ensemble des configurations de l'application.
 * Ce contrôleur centralise toutes les actions de l'administrateur liées aux paramètres,
 * aux années académiques, aux référentiels, aux modèles de documents, aux notifications et aux menus.
 * Il est structuré avec des méthodes spécifiques pour chaque action pour une clarté maximale.
 */
class ConfigurationController extends BaseController
{
    private ServiceSystemeInterface $systemeService;
    private ServiceDocumentInterface $documentService;
    private ServiceCommunicationInterface $communicationService;
    private Container $container;

    public function __construct(
        ServiceSystemeInterface $systemeService,
        ServiceDocumentInterface $documentService,
        ServiceCommunicationInterface $communicationService,
        ServiceSecuriteInterface $securiteService,
        ServiceSupervisionInterface $supervisionService,
        Container $container
    ) {
        parent::__construct($securiteService, $supervisionService);
        $this->systemeService = $systemeService;
        $this->documentService = $documentService;
        $this->communicationService = $communicationService;
        $this->container = $container;
    }

    public function index(): void
    {
        $this->showConfigurationPage();
    }

    // ===================================================================
    // PARTIE 1 : AFFICHAGE & DONNÉES DYNAMIQUES (AJAX)
    // ===================================================================

    /**
     * Affiche la page principale de configuration avec tous ses onglets.
     * Charge les données nécessaires à l'affichage initial de l'interface.
     */
    private function showConfigurationPage(): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_ACCEDER');
        try {
            $data = [
                'title' => 'Configuration du Système',
                'system_parameters' => $this->systemeService->getAllParametres(),
                'academic_years' => $this->systemeService->listerAnneesAcademiques(),
                'referentials' => $this->getReferentialList(),
                'document_models' => $this->documentService->listerModelesDocument(),
                'notification_templates' => $this->communicationService->listerModelesNotification(),
                'notification_rules' => $this->communicationService->listerReglesMatrice(),
                'all_actions' => $this->systemeService->gererReferentiel('list', 'action'),
                'all_user_groups' => $this->systemeService->gererReferentiel('list', 'groupe_utilisateur'),
                'csrf_tokens' => [
                    'params' => $this->generateCsrfToken('params_form'),
                    'years' => $this->generateCsrfToken('years_form'),
                    'refs' => $this->generateCsrfToken('refs_form'),
                    'docs' => $this->generateCsrfToken('docs_form'),
                    'notifs' => $this->generateCsrfToken('notifs_form'),
                    'menus' => $this->generateCsrfToken('menus_form'),
                    'cache' => $this->generateCsrfToken('cache_form'),
                ]
            ];
            $this->render('Administration/gestion_configuration', $data);
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur de chargement de la page de configuration : ' . $e->getMessage());
            $this->redirect('/admin/dashboard');
            return; // Suppression de l'instruction inaccessible
        }
    }

    /**
     * Récupère le panneau de détails pour un référentiel (appel AJAX).
     * Permet une interface master-detail réactive.
     */
    public function getReferentialDetails(string $entityName): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_REFERENTIELS_GERER');
        try {
            $entries = $this->systemeService->gererReferentiel('list', $entityName);
            $this->render('Administration/_referential_details_panel', [
                'entityName' => $entityName,
                'entries' => $entries,
                'csrf_token_refs' => $this->generateCsrfToken('refs_form'),
            ], false);
        } catch (Exception $e) {
            http_response_code(500);
            echo "Erreur: " . htmlspecialchars($e->getMessage());
        }
    }

    // ===================================================================
    // PARTIE 2 : GESTIONNAIRES D'ACTIONS (POST)
    // ===================================================================

    /**
     * Traite la mise à jour des paramètres système.
     */
    public function handleSystemParameters(): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_PARAMETRES_GERER');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('params_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/configuration');
            return; // Suppression de l'instruction inaccessible
        }

        try {
            $data = $this->getPostData();
            unset($data['csrf_token']);
            $this->systemeService->setParametres($data);
            $this->addFlashMessage('success', 'Paramètres système mis à jour avec succès.');
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur lors de la mise à jour des paramètres : ' . $e->getMessage());
        }
        $this->redirect('/admin/configuration');
    }

    /**
     * Traite les actions CRUD sur les années académiques.
     */
    public function handleAcademicYearAction(): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_ANNEES_GERER');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('years_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/configuration#years-tab');
            return; // Suppression de l'instruction inaccessible
        }

        $data = $this->getPostData();
        $action = $data['action'] ?? '';
        $id = $data['id'] ?? null;

        try {
            switch ($action) {
                case 'create':
                    $this->systemeService->creerAnneeAcademique($data['libelle_annee_academique'], $data['date_debut'], $data['date_fin'], isset($data['est_active']));
                    $this->addFlashMessage('success', "L'année académique '{$data['libelle_annee_academique']}' a été créée.");
                    break;
                case 'update':
                    $this->systemeService->mettreAJourAnneeAcademique($id, $data);
                    $this->addFlashMessage('success', "L'année académique '{$id}' a été mise à jour.");
                    break;
                case 'delete':
                    $this->systemeService->supprimerAnneeAcademique($id);
                    $this->addFlashMessage('success', "L'année académique '{$id}' a été supprimée.");
                    break;
                case 'set_active':
                    $this->systemeService->setAnneeAcademiqueActive($id);
                    $this->addFlashMessage('success', "L'année académique '{$id}' est maintenant active.");
                    break;
                default:
                    $this->addFlashMessage('error', 'Action non reconnue pour les années académiques.');
                    break;
            }
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur sur les années académiques : ' . $e->getMessage());
        }
        $this->redirect('/admin/configuration#years-tab');
    }

    /**
     * Traite les actions CRUD sur un référentiel.
     */
    public function handleReferentialAction(): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_REFERENTIELS_GERER');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('refs_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/configuration#referentials-tab');
            return; // Suppression de l'instruction inaccessible
        }

        $data = $this->getPostData();
        $action = $data['action'] ?? '';
        $entityName = $data['entity_name'] ?? '';
        $id = $data['id'] ?? null;
        $libelle = $data['libelle'] ?? 'N/A';

        try {
            switch ($action) {
                case 'create':
                    $model = $this->container->getModelForTable($entityName);
                    $idKey = is_array($model->getClePrimaire()) ? $model->getClePrimaire()[0] : $model->getClePrimaire();
                    $data[$idKey] = $this->_generateIdFromLabel($entityName, $libelle);
                    $this->systemeService->gererReferentiel('create', $entityName, null, $data);
                    $this->addFlashMessage('success', "L'entrée '{$libelle}' a été ajoutée au référentiel '{$entityName}'.");
                    break;
                case 'update':
                    $this->systemeService->gererReferentiel('update', $entityName, $id, $data);
                    $this->addFlashMessage('success', "L'entrée '{$id}' a été mise à jour dans le référentiel '{$entityName}'.");
                    break;
                case 'delete':
                    $this->systemeService->gererReferentiel('delete', $entityName, $id);
                    $this->addFlashMessage('success', "L'entrée '{$id}' a été supprimée du référentiel '{$entityName}'.");
                    break;
                default:
                    $this->addFlashMessage('error', 'Action non reconnue pour les référentiels.');
                    break;
            }
        } catch (OperationImpossibleException $e) {
            $this->addFlashMessage('error', $e->getMessage());
        } catch (Exception $e) {
            $this->addFlashMessage('error', "Erreur sur le référentiel '{$entityName}' : " . $e->getMessage());
        }
        $this->redirect('/admin/configuration#referentials-tab');
    }

    /**
     * Gère les actions CRUD sur les modèles de documents.
     */
    public function handleDocumentModelAction(): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_MODELES_DOC_GERER');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('docs_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/configuration#docs-tab');
            return; // Suppression de l'instruction inaccessible
        }

        $action = $_POST['action'] ?? '';
        $id = $_POST['id_modele'] ?? null;
        try {
            switch ($action) {
                case 'import':
                    $file = $this->getFileData('word_file');
                    if (!$file || $file['error'] !== UPLOAD_ERR_OK) throw new ValidationException("Erreur de téléversement du fichier.");
                    $this->documentService->importerModeleDocumentWord($file);
                    $this->addFlashMessage('success', 'Modèle importé avec succès.');
                    break;
                case 'update':
                    $this->documentService->mettreAJourModeleDocument($id, $_POST['nom_modele'], $_POST['contenu_html']);
                    $this->addFlashMessage('success', "Le modèle '{$_POST['nom_modele']}' a été mis à jour.");
                    break;
                case 'delete':
                    $this->documentService->supprimerModeleDocument($id);
                    $this->addFlashMessage('success', "Le modèle '{$id}' a été supprimé.");
                    break;
                default:
                    $this->addFlashMessage('error', 'Action non reconnue pour les modèles de documents.');
                    break;
            }
        } catch (Exception $e) {
            $this->addFlashMessage('error', "Erreur lors de l'opération sur le modèle de document : " . $e->getMessage());
        }
        $this->redirect('/admin/configuration#docs-tab');
    }

    /**
     * Gère les actions sur les règles et modèles de notification.
     */
    public function handleNotificationAction(): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_NOTIFS_GERER');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('notifs_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/configuration#notifications-tab');
            return; // Suppression de l'instruction inaccessible
        }

        $data = $this->getPostData();
        $action = $data['action'] ?? '';

        try {
            switch ($action) {
                case 'update_rule':
                    $this->communicationService->mettreAJourRegleMatrice($data['id_regle'], $data['canal'], isset($data['est_active']));
                    $this->addFlashMessage('success', "La règle de notification '{$data['id_regle']}' a été mise à jour.");
                    break;
                case 'update_template':
                    $this->communicationService->mettreAJourModeleNotification($data['id'], $data['libelle'], $data['contenu']);
                    $this->addFlashMessage('success', "Le modèle de notification '{$data['id']}' a été mis à jour.");
                    break;
                default:
                    $this->addFlashMessage('error', 'Action non reconnue pour les notifications.');
                    break;
            }
        } catch (Exception $e) {
            $this->addFlashMessage('error', "Erreur sur les notifications : " . $e->getMessage());
        }
        $this->redirect('/admin/configuration#notifications-tab');
    }

    /**
     * Traite la mise à jour de la structure des menus.
     */
    public function handleMenuOrder(): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_MENUS_GERER');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('menus_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/configuration#menus-tab');
            return; // Suppression de l'instruction inaccessible
        }

        try {
            $menuStructure = json_decode($_POST['menu_structure'] ?? '[]', true);
            if (!is_array($menuStructure)) throw new ValidationException("Structure de menu invalide.");
            $this->systemeService->updateMenuStructure($menuStructure);
            $this->addFlashMessage('success', "La structure du menu a été sauvegardée.");
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur lors de la sauvegarde du menu : ' . $e->getMessage());
        }
        $this->redirect('/admin/configuration#menus-tab');
    }

    /**
     * Vide les caches de l'application.
     */
    public function clearCache(): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_ACCEDER');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('cache_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/configuration');
            return; // Suppression de l'instruction inaccessible
        }

        unset($_SESSION['admin_dashboard_stats']);
        $this->addFlashMessage('success', 'Les caches de l\'application ont été vidés.');
        $this->redirect('/admin/configuration');
    }

    // ===================================================================
    // PARTIE 3 : MÉTHODES PRIVÉES UTILITAIRES
    // ===================================================================

    private function _generateIdFromLabel(string $entityName, string $label): string
    {
        $prefix = strtoupper(substr($entityName, 0, 4));
        $cleanLabel = iconv('UTF-8', 'ASCII//TRANSLIT', $label);
        $cleanLabel = strtoupper(trim($cleanLabel));
        $slug = preg_replace('/[^A-Z0-9]+/', '_', $cleanLabel);
        return rtrim($prefix, '_') . '_' . trim($slug, '_');
    }

    private function getReferentialList(): array
    {
        $referentialKeys = [
            'grade', 'fonction', 'specialite', 'niveau_etude', 'statut_rapport_ref',
            'statut_pv_ref', 'statut_paiement_ref', 'decision_vote_ref', 'statut_conformite_ref',
            'statut_reclamation_ref', 'type_document_ref', 'statut_jury', 'action', 'groupe_utilisateur',
            'critere_conformite_ref', 'decision_passage_ref', 'decision_validation_pv_ref',
            'niveau_acces_donne', 'type_utilisateur', 'ue', 'ecue', 'entreprise'
        ];
        $list = [];
        foreach ($referentialKeys as $key) {
            $list[$key] = ucwords(str_replace(['_', ' ref'], [' ', ''], $key));
        }
        asort($list);
        return $list;
    }
}