<?php
// src/Backend/Controller/Administration/ConfigurationController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Config\Container;
use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Backend\Service\Document\ServiceDocumentInterface;
use App\Backend\Service\Communication\ServiceCommunicationInterface;

use App\Backend\Exception\{ElementNonTrouveException, OperationImpossibleException, ValidationException, DoublonException};

class ConfigurationController extends BaseController
{
    private ServiceSystemeInterface $systemeService;
    private ServiceDocumentInterface $documentService;
    private ServiceCommunicationInterface $communicationService;// Injecter ServiceSecurite

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->systemeService = $container->get(ServiceSystemeInterface::class);
        $this->documentService = $container->get(ServiceDocumentInterface::class);
        $this->communicationService = $container->get(ServiceCommunicationInterface::class);
    }


    public function showConfigurationPage(): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_ACCEDER');

        try {
            $systemParameters = $this->systemeService->getAllParametres();
            $academicYears = $this->systemeService->listerAnneesAcademiques();
            $activeYear = $this->systemeService->getAnneeAcademiqueActive();

            $referentials = [
                'action' => $this->systemeService->gererReferentiel('list', 'action'),
                'critere_conformite_ref' => $this->systemeService->gererReferentiel('list', 'critere_conformite_ref'),
                'decision_passage_ref' => $this->systemeService->gererReferentiel('list', 'decision_passage_ref'),
                'decision_validation_pv_ref' => $this->systemeService->gererReferentiel('list', 'decision_validation_pv_ref'),
                'decision_vote_ref' => $this->systemeService->gererReferentiel('list', 'decision_vote_ref'),
                'ecue' => $this->systemeService->gererReferentiel('list', 'ecue'),
                'entreprise' => $this->systemeService->gererReferentiel('list', 'entreprise'),
                'fonction' => $this->systemeService->gererReferentiel('list', 'fonction'),
                'grade' => $this->systemeService->gererReferentiel('list', 'grade'),
                'groupe_utilisateur' => $this->systemeService->gererReferentiel('list', 'groupe_utilisateur'),
                'niveau_acces_donne' => $this->systemeService->gererReferentiel('list', 'niveau_acces_donne'),
                'niveau_etude' => $this->systemeService->gererReferentiel('list', 'niveau_etude'),
                'specialite' => $this->systemeService->gererReferentiel('list', 'specialite'),
                'statut_conformite_ref' => $this->systemeService->gererReferentiel('list', 'statut_conformite_ref'),
                'statut_jury' => $this->systemeService->gererReferentiel('list', 'statut_jury'),
                'statut_paiement_ref' => $this->systemeService->gererReferentiel('list', 'statut_paiement_ref'),
                'statut_penalite_ref' => $this->systemeService->gererReferentiel('list', 'statut_penalite_ref'),
                'statut_pv_ref' => $this->systemeService->gererReferentiel('list', 'statut_pv_ref'),
                'statut_rapport_ref' => $this->systemeService->gererReferentiel('list', 'statut_rapport_ref'),
                'statut_reclamation_ref' => $this->systemeService->gererReferentiel('list', 'statut_reclamation_ref'),
                'traitement' => $this->systemeService->gererReferentiel('list', 'traitement'),
                'type_document_ref' => $this->systemeService->gererReferentiel('list', 'type_document_ref'),
                'type_utilisateur' => $this->systemeService->gererReferentiel('list', 'type_utilisateur'),
                'ue' => $this->systemeService->gererReferentiel('list', 'ue'),
                // Tables de liaison pour les référentiels
                'acquerir' => $this->systemeService->gererReferentiel('list', 'acquerir'),
                'attribuer' => $this->systemeService->gererReferentiel('list', 'attribuer'),
                'occuper' => $this->systemeService->gererReferentiel('list', 'occuper'),
                'rapport_modele_assignation' => $this->systemeService->gererReferentiel('list', 'rapport_modele_assignation'),
                'rattacher' => $this->systemeService->gererReferentiel('list', 'rattacher'),
                'pv_session_rapport' => $this->systemeService->gererReferentiel('list', 'pv_session_rapport'),
                'session_rapport' => $this->systemeService->gererReferentiel('list', 'session_rapport'),
                'validation_pv' => $this->systemeService->gererReferentiel('list', 'validation_pv'),
                'vote_commission' => $this->systemeService->gererReferentiel('list', 'vote_commission'),
            ];

            $documentModels = $this->documentService->listerModelesDocument();
            $notificationTemplates = $this->communicationService->listerModelesNotification();
            $notificationRules = $this->communicationService->listerReglesMatrice();

            $data = [
                'title' => 'Configuration Système',
                'system_parameters' => $systemParameters,
                'academic_years' => $academicYears,
                'active_year' => $activeYear,
                'referentials' => $referentials,
                'document_models' => $documentModels,
                'notification_templates' => $notificationTemplates,
                'notification_rules' => $notificationRules,
                'csrf_token_params' => $this->generateCsrfToken('system_params_form'),
                'csrf_token_academic_years' => $this->generateCsrfToken('academic_years_form'),
                'csrf_token_referentials' => $this->generateCsrfToken('referentials_form'),
                'csrf_token_document_models' => $this->generateCsrfToken('document_models_form'),
                'csrf_token_notifications' => $this->generateCsrfToken('notifications_form'),
                'csrf_token_menu_order' => $this->generateCsrfToken('menu_order_form'),
            ];

            $this->render('Administration/gestion_referentiels', $data);

        } catch (ElementNonTrouveException $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->renderError(404, 'Ressource non trouvée.');
        } catch (OperationImpossibleException $e) {
            $this->addFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
            $this->renderError(500, 'Erreur interne du serveur.');
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
            $this->renderError(500, 'Erreur interne du serveur.');
        }
    }

    public function handleSystemParameters(): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_PARAMETRES_GERER');

        if (!$this->isPostRequest()) {
            $this->redirect('/admin/configuration');
        }

        $data = $this->getPostData();

        if (!$this->validateCsrfToken('system_params_form', $data['csrf_token_params'] ?? '')) {
            $this->redirect('/admin/configuration');
        }

        try {
            $paramsToUpdate = [];
            foreach ($data as $key => $value) {
                if (str_starts_with($key, 'param_')) {
                    $paramName = substr($key, 6);
                    $paramsToUpdate[$paramName] = $value;
                }
            }
            $this->systemeService->setParametres($paramsToUpdate);
            $this->addFlashMessage('success', 'Paramètres système mis à jour avec succès.');
        } catch (ValidationException $e) {
            $this->addFlashMessage('error', 'Erreur de validation: ' . implode(', ', $e->getErrors()));
        } catch (OperationImpossibleException $e) {
            $this->addFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect('/admin/configuration');
    }

    public function addAcademicYear(): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_ANNEES_GERER');

        if (!$this->isPostRequest()) {
            $this->redirect('/admin/configuration');
        }

        $data = $this->getPostData();

        if (!$this->validateCsrfToken('academic_years_form', $data['csrf_token_academic_years'] ?? '')) {
            $this->redirect('/admin/configuration');
        }

        try {
            $libelle = $data['libelle_annee_academique'] ?? '';
            $dateDebut = $data['date_debut'] ?? '';
            $dateFin = $data['date_fin'] ?? '';
            $estActive = isset($data['est_active']);

            $this->systemeService->creerAnneeAcademique($libelle, $dateDebut, $dateFin, $estActive);
            $this->addFlashMessage('success', 'Année académique ajoutée avec succès.');
        } catch (DoublonException $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        } catch (ValidationException $e) {
            $this->addFlashMessage('error', 'Erreur de validation: ' . implode(', ', $e->getErrors()));
        } catch (OperationImpossibleException $e) {
            $this->addFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect('/admin/configuration');
    }

    public function updateAcademicYear(string $id): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_ANNEES_GERER');

        if (!$this->isPostRequest()) {
            $this->redirect('/admin/configuration');
        }

        $data = $this->getPostData();

        if (!$this->validateCsrfToken('academic_years_form', $data['csrf_token_academic_years'] ?? '')) {
            $this->redirect('/admin/configuration');
        }

        try {
            $donnees = [
                'libelle_annee_academique' => $data['libelle_annee_academique'] ?? '',
                'date_debut' => $data['date_debut'] ?? '',
                'date_fin' => $data['date_fin'] ?? '',
                'est_active' => isset($data['est_active']) ? 1 : 0,
            ];
            $this->systemeService->mettreAJourAnneeAcademique($id, $donnees);
            $this->addFlashMessage('success', 'Année académique mise à jour avec succès.');
        } catch (ElementNonTrouveException $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        } catch (ValidationException $e) {
            $this->addFlashMessage('error', 'Erreur de validation: ' . implode(', ', $e->getErrors()));
        } catch (OperationImpossibleException $e) {
            $this->addFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect('/admin/configuration');
    }

    public function deleteAcademicYear(string $id): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_ANNEES_GERER');

        if (!$this->isPostRequest()) {
            $this->redirect('/admin/configuration');
        }

        $data = $this->getPostData(); // Pour récupérer le CSRF token

        if (!$this->validateCsrfToken('academic_years_form', $data['csrf_token_academic_years'] ?? '')) {
            $this->redirect('/admin/configuration');
        }

        try {
            $this->systemeService->supprimerAnneeAcademique($id);
            $this->addFlashMessage('success', 'Année académique supprimée avec succès.');
        } catch (OperationImpossibleException $e) {
            $this->addFlashMessage('error', 'Suppression impossible: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect('/admin/configuration');
    }

    public function setActiveAcademicYear(string $id): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_ANNEES_GERER');

        if (!$this->isPostRequest()) {
            $this->redirect('/admin/configuration');
        }

        $data = $this->getPostData(); // Pour récupérer le CSRF token

        if (!$this->validateCsrfToken('academic_years_form', $data['csrf_token_academic_years'] ?? '')) {
            $this->redirect('/admin/configuration');
        }

        try {
            $this->systemeService->setAnneeAcademiqueActive($id);
            $this->addFlashMessage('success', 'Année académique active définie avec succès.');
        } catch (ElementNonTrouveException $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        } catch (OperationImpossibleException $e) {
            $this->addFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect('/admin/configuration');
    }

    public function handleReferential(string $entityName, ?string $id = null): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_REFERENTIELS_GERER');

        if (!$this->isPostRequest()) {
            $this->redirect('/admin/configuration');
        }

        $data = $this->getPostData();

        if (!$this->validateCsrfToken('referentials_form', $data['csrf_token_referentials'] ?? '')) {
            $this->redirect('/admin/configuration');
        }

        $action = $data['action'] ?? '';
        unset($data['action'], $data['csrf_token_referentials']); // Nettoyer les données pour le service

        try {
            switch ($action) {
                case 'create':
                    $this->systemeService->gererReferentiel('create', $entityName, null, $data);
                    $this->addFlashMessage('success', 'Entrée de référentiel ajoutée avec succès.');
                    break;
                case 'update':
                    if (!$id) throw new ValidationException("ID manquant pour la mise à jour.");
                    $this->systemeService->gererReferentiel('update', $entityName, $id, $data);
                    $this->addFlashMessage('success', 'Entrée de référentiel mise à jour avec succès.');
                    break;
                case 'delete':
                    if (!$id) throw new ValidationException("ID manquant pour la suppression.");
                    $this->systemeService->gererReferentiel('delete', $entityName, $id);
                    $this->addFlashMessage('success', 'Entrée de référentiel supprimée avec succès.');
                    break;
                default:
                    throw new ValidationException("Action non reconnue pour le référentiel.");
            }
        } catch (DoublonException $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        } catch (ValidationException $e) {
            $this->addFlashMessage('error', 'Erreur de validation: ' . $e->getMessage());
        } catch (ElementNonTrouveException $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        } catch (OperationImpossibleException $e) {
            $this->addFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect('/admin/configuration#referentials-tab');
    }

    public function handleDocumentModel(?string $id = null): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_MODELES_DOC_GERER');

        if (!$this->isPostRequest()) {
            $this->redirect('/admin/configuration');
        }

        $data = $this->getPostData();
        $fileData = $this->getFileData('word_file');

        if (!$this->validateCsrfToken('document_models_form', $data['csrf_token_document_models'] ?? '')) {
            $this->redirect('/admin/configuration');
        }

        $action = $data['action'] ?? '';
        unset($data['action'], $data['csrf_token_document_models']);

        try {
            switch ($action) {
                case 'create':
                    if ($fileData && $fileData['error'] === UPLOAD_ERR_OK) {
                        $this->documentService->importerModeleDocumentWord($fileData);
                        $this->addFlashMessage('success', 'Modèle de document Word importé et créé avec succès.');
                    } else {
                        $this->documentService->creerModeleDocument($data['nom_modele'] ?? '', $data['contenu_html'] ?? '');
                        $this->addFlashMessage('success', 'Modèle de document créé avec succès.');
                    }
                    break;
                case 'update':
                    if (!$id) throw new ValidationException("ID manquant pour la mise à jour.");
                    $this->documentService->mettreAJourModeleDocument($id, $data['nom_modele'] ?? '', $data['contenu_html'] ?? '');
                    $this->addFlashMessage('success', 'Modèle de document mis à jour avec succès.');
                    break;
                case 'delete':
                    if (!$id) throw new ValidationException("ID manquant pour la suppression.");
                    $this->documentService->supprimerModeleDocument($id);
                    $this->addFlashMessage('success', 'Modèle de document supprimé avec succès.');
                    break;
                default:
                    throw new ValidationException("Action non reconnue pour le modèle de document.");
            }
        } catch (ValidationException $e) {
            $this->addFlashMessage('error', 'Erreur de validation: ' . $e->getMessage());
        } catch (ElementNonTrouveException $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        } catch (OperationImpossibleException $e) {
            $this->addFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect('/admin/configuration#document-models-tab');
    }

    public function handleNotificationSettings(): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_NOTIFS_GERER');

        if (!$this->isPostRequest()) {
            $this->redirect('/admin/configuration');
        }

        $data = $this->getPostData();

        if (!$this->validateCsrfToken('notifications_form', $data['csrf_token_notifications'] ?? '')) {
            $this->redirect('/admin/configuration');
        }

        $type = $data['type'] ?? '';
        unset($data['type'], $data['csrf_token_notifications']);

        try {
            switch ($type) {
                case 'template_update':
                    $this->communicationService->mettreAJourModeleNotification($data['id'] ?? '', $data['libelle'] ?? '', $data['contenu'] ?? '');
                    $this->addFlashMessage('success', 'Modèle de notification mis à jour avec succès.');
                    break;
                case 'rule_update':
                    $this->communicationService->mettreAJourRegleMatrice($data['id'] ?? '', $data['canal'] ?? '', isset($data['est_active']));
                    $this->addFlashMessage('success', 'Règle de matrice de notification mise à jour avec succès.');
                    break;
                default:
                    throw new ValidationException("Type d'action non reconnu pour les notifications.");
            }
        } catch (ValidationException $e) {
            $this->addFlashMessage('error', 'Erreur de validation: ' . $e->getMessage());
        } catch (ElementNonTrouveException $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        } catch (OperationImpossibleException $e) {
            $this->addFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect('/admin/configuration#notifications-tab');
    }

    public function handleMenuOrder(): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_MENUS_GERER');

        if (!$this->isPostRequest()) {
            $this->redirect('/admin/configuration');
        }

        $data = $this->getPostData();

        if (!$this->validateCsrfToken('menu_order_form', $data['csrf_token_menu_order'] ?? '')) {
            $this->redirect('/admin/configuration');
        }

        try {
            // Récupère la chaîne JSON et la décode en tableau PHP
            $menuStructure = json_decode($data['menu_structure'] ?? '[]', true);

            if (!is_array($menuStructure) || empty($menuStructure)) {
                throw new ValidationException("Structure de menu invalide ou vide.");
            }

            // Appel au service pour mettre à jour la base de données
            $this->securiteService->updateMenuStructure($menuStructure);
            $this->addFlashMessage('success', 'Ordre des menus mis à jour avec succès.');
        } catch (ValidationException $e) {
            $this->addFlashMessage('error', 'Erreur de validation: ' . $e->getMessage());
        } catch (OperationImpossibleException $e) {
            $this->addFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect('/admin/configuration#menus-tab');
    }
}