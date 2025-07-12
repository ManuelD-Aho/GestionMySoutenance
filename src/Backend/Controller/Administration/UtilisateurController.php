<?php
// src/Backend/Controller/Administration/UtilisateurController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Utilisateur\ServiceUtilisateurInterface;
use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ValidationException;
use Exception;

class UtilisateurController extends BaseController
{
    private ServiceUtilisateurInterface $serviceUtilisateur;
    private ServiceSystemeInterface $serviceSysteme;

    public function __construct(
        ServiceUtilisateurInterface $serviceUtilisateur,
        ServiceSystemeInterface $serviceSysteme,
        FormValidator $validator,
        ServiceSecuriteInterface $securiteService,
        ServiceSupervisionInterface $supervisionService
    ) {
        parent::__construct($securiteService, $supervisionService, $validator);
        $this->serviceUtilisateur = $serviceUtilisateur;
        $this->serviceSysteme = $serviceSysteme;
    }

    /**
     * Affiche la liste paginée et filtrable de tous les utilisateurs.
     * Correspond au menu MENU_ADMIN_UTILISATEURS_LISTER
     */
    public function list(): void
    {
        // Utiliser la permission existante
        $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_LISTER_ACCES');
        error_log("DEBUG: Permission OK");

        try {
            $filters = $this->getGetData();
            $users = $this->serviceUtilisateur->listerUtilisateursComplets($filters);

            $groupes = $this->serviceSysteme->gererReferentiel('list', 'groupe_utilisateur');
            $statuts = ['actif', 'inactif', 'bloque', 'en_attente_validation', 'archive'];
            $types = $this->serviceSysteme->gererReferentiel('list', 'type_utilisateur');

            $this->render('Administration/Utilisateurs/liste_utilisateurs', [
                'title' => 'Gestion des Utilisateurs',
                'users' => $users,
                'groupes' => $groupes,
                'statuts' => $statuts,
                'types' => $types,
                'current_filters' => $filters,
                'csrf_token' => $this->generateCsrfToken('user_actions_form')
            ]);
        } catch (Exception $e) {
            error_log("ERROR UtilisateurController: " . $e->getMessage());
            error_log("ERROR UtilisateurController::list: " . $e->getMessage());
            error_log("ERROR Stack trace: " . $e->getTraceAsString());
            $this->addFlashMessage('error', "Erreur lors du chargement des utilisateurs : " . $e->getMessage());
            $this->redirect('/admin/dashboard');
        }
    }

    /**
     * Affiche le formulaire générique (correspond à MENU_ADMIN_UTILISATEURS_FORM_GENERIC)
     * URL: /admin/utilisateurs/form
     */
    public function showGenericForm(): void
    {
        $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_FORM_GENERIC_ACCES');

        if ($this->isPostRequest()) {
            $this->handleGenericCreate();
        } else {
            $this->showForm('generic');
        }
    }

    /**
     * Affiche le formulaire étudiant (correspond à MENU_ADMIN_UTILISATEURS_FORM_ETUDIANT)
     * URL: /admin/utilisateurs/etudiant/form
     */
    public function showEtudiantForm(): void
    {
        $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_FORM_ETUDIANT_ACCES');

        if ($this->isPostRequest()) {
            $this->handleEtudiantCreate();
        } else {
            $this->showForm('etudiant');
        }
    }

    /**
     * Affiche le formulaire enseignant (correspond à MENU_ADMIN_UTILISATEURS_FORM_ENSEIGNANT)
     * URL: /admin/utilisateurs/enseignant/form
     */
    public function showEnseignantForm(): void
    {
        $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_FORM_ENSEIGNANT_ACCES');

        if ($this->isPostRequest()) {
            $this->handleEnseignantCreate();
        } else {
            $this->showForm('enseignant');
        }
    }

    /**
     * Affiche le formulaire personnel (correspond à MENU_ADMIN_UTILISATEURS_FORM_PERSONNEL)
     * URL: /admin/utilisateurs/personnel/form
     */
    public function showPersonnelForm(): void
    {
        $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_FORM_PERSONNEL_ACCES');

        if ($this->isPostRequest()) {
            $this->handlePersonnelCreate();
        } else {
            $this->showForm('personnel');
        }
    }

    /**
     * Affiche l'import d'étudiants (correspond à MENU_ADMIN_UTILISATEURS_IMPORT_ETUDIANTS)
     * URL: /admin/utilisateurs/import-etudiants
     */
    public function importEtudiants(): void
    {
        $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_IMPORT_ETUDIANTS_ACCES');

        if ($this->isPostRequest()) {
            $this->handleImportEtudiants();
        } else {
            $this->showImportForm();
        }
    }

    /**
     * Méthode générique pour afficher les formulaires selon le type
     */
    private function showForm(string $type, ?array $user = null): void
    {
        $isEdit = !empty($user);

        try {
            // Données communes à tous les formulaires
            $data = [
                'title' => $this->getFormTitle($type, $isEdit),
                'type' => $type,
                'user' => $user,
                'groupes' => $this->serviceSysteme->gererReferentiel('list', 'groupe_utilisateur'),
                'types' => $this->serviceSysteme->gererReferentiel('list', 'type_utilisateur'),
                'niveauxAcces' => $this->serviceSysteme->gererReferentiel('list', 'niveau_acces_donne'),
                'action_url' => $this->getActionUrl($type, $user),
                'csrf_token' => $this->generateCsrfToken('user_form'),
                'form_errors' => $_SESSION['form_errors'] ?? [],
                'form_data' => $_SESSION['form_data'] ?? $user ?? []
            ];

            // Ajouter les données spécifiques selon le type
            $this->addTypeSpecificData($data, $type);

            // Déterminer la vue à utiliser
            $view = $this->getViewName($type);

            $this->render($view, $data);
            unset($_SESSION['form_errors'], $_SESSION['form_data']);

        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur lors du chargement du formulaire : ' . $e->getMessage());
            $this->redirect('/admin/utilisateurs');
        }
    }

    /**
     * Gestion de la création générique
     */
    private function handleGenericCreate(): void
    {
        $this->processUserCreation('generic');
    }

    /**
     * Gestion de la création étudiant
     */
    private function handleEtudiantCreate(): void
    {
        $this->processUserCreation('etudiant');
    }

    /**
     * Gestion de la création enseignant
     */
    private function handleEnseignantCreate(): void
    {
        $this->processUserCreation('enseignant');
    }

    /**
     * Gestion de la création personnel
     */
    private function handlePersonnelCreate(): void
    {
        $this->processUserCreation('personnel');
    }

    /**
     * Logique de traitement de création unifiée
     */
    private function processUserCreation(string $type): void
    {
        if (!$this->validateCsrfToken('user_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/utilisateurs');
            return;
        }

        $data = $this->getPostData();
        $rules = $this->getValidationRules($type);

        if (!$this->validator->validate($data, $rules)) {
            $_SESSION['form_errors'] = $this->validator->getErrors();
            $_SESSION['form_data'] = $data;
            $this->redirect($this->getFormUrl($type));
            return;
        }

        try {
            // Préparation des données selon le type
            $userData = $this->prepareUserData($data, $type);
            $profileData = $this->prepareProfileData($data, $type);

            // Création via le service
            $numeroUtilisateur = $this->serviceUtilisateur->creerUtilisateurComplet($userData, $profileData, $type);

            $this->addFlashMessage('success', "Utilisateur {$type} créé avec succès. ID: {$numeroUtilisateur}");
            $this->redirect('/admin/utilisateurs');

        } catch (DoublonException | OperationImpossibleException | ValidationException $e) {
            $this->addFlashMessage('error', 'Erreur de création : ' . $e->getMessage());
            $_SESSION['form_data'] = $data;
            $this->redirect($this->getFormUrl($type));
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur inattendue : ' . $e->getMessage());
            error_log("Erreur création utilisateur {$type}: " . $e->getMessage());
            $this->redirect($this->getFormUrl($type));
        }
    }

    /**
     * Modification d'utilisateur
     */
    public function edit(string $id): void
    {
        // Utiliser une permission de modification si elle existe, sinon utiliser création
        $this->requirePermission('TRAIT_ADMIN_GERER_UTILISATEURS_MODIFIER');

        if ($this->isPostRequest()) {
            $this->handleEdit($id);
        } else {
            $this->showEditForm($id);
        }
    }

    /**
     * Affichage du formulaire de modification
     */
    private function showEditForm(string $id): void
    {
        try {
            $user = $this->serviceUtilisateur->lireUtilisateurComplet($id);
            if (!$user) {
                throw new ElementNonTrouveException("Utilisateur introuvable.");
            }

            // Déterminer le type d'utilisateur pour afficher le bon formulaire
            $type = $this->getUserTypeFromUser($user);
            $this->showForm($type, $user);

        } catch (ElementNonTrouveException $e) {
            $this->addFlashMessage('error', $e->getMessage());
            $this->redirect('/admin/utilisateurs');
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur lors du chargement : ' . $e->getMessage());
            $this->redirect('/admin/utilisateurs');
        }
    }

    /**
     * Traitement de la modification
     */
    private function handleEdit(string $id): void
    {
        if (!$this->validateCsrfToken('user_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/utilisateurs');
            return;
        }

        $data = $this->getPostData();

        try {
            $user = $this->serviceUtilisateur->lireUtilisateurComplet($id);
            if (!$user) {
                throw new ElementNonTrouveException("Utilisateur introuvable.");
            }

            $type = $this->getUserTypeFromUser($user);
            $rules = $this->getValidationRules($type, true); // true = mode édition

            if (!$this->validator->validate($data, $rules)) {
                $_SESSION['form_errors'] = $this->validator->getErrors();
                $_SESSION['form_data'] = $data;
                $this->redirect("/admin/utilisateurs/{$id}/edit");
                return;
            }

            // Mise à jour
            $userData = $this->prepareUserData($data, $type, true);
            $profileData = $this->prepareProfileData($data, $type, true);

            $this->serviceUtilisateur->mettreAJourUtilisateur($id, $userData, $profileData);

            $this->addFlashMessage('success', "Utilisateur {$id} mis à jour avec succès.");
            $this->redirect('/admin/utilisateurs');

        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur de mise à jour : ' . $e->getMessage());
            error_log("Erreur modification utilisateur {$id}: " . $e->getMessage());
            $_SESSION['form_data'] = $data;
            $this->redirect("/admin/utilisateurs/{$id}/edit");
        }
    }

    /**
     * Suppression d'utilisateur
     */
    public function delete(string $id): void
    {
        $this->requirePermission('TRAIT_ADMIN_GERER_UTILISATEURS_SUPPRIMER');

        if (!$this->isPostRequest() || !$this->validateCsrfToken('user_actions_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/utilisateurs');
            return;
        }

        try {
            $this->serviceUtilisateur->supprimerUtilisateurEtEntite($id);
            $this->addFlashMessage('success', "L'utilisateur {$id} a été supprimé.");
        } catch (Exception $e) {
            $this->addFlashMessage('error', "Erreur lors de la suppression : " . $e->getMessage());
        }

        $this->redirect('/admin/utilisateurs');
    }

    /**
     * Actions diverses sur utilisateur
     */
    public function handleUserAction(string $id): void
    {
        if (!$this->isPostRequest() || !$this->validateCsrfToken('user_actions_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/utilisateurs');
            return;
        }

        $action = $_POST['action'] ?? '';

        try {
            switch ($action) {
                case 'change_status':
                    $this->requirePermission('TRAIT_ADMIN_GERER_UTILISATEURS_MODIFIER');
                    $this->serviceUtilisateur->changerStatutCompte($id, $_POST['status'] ?? '');
                    $this->addFlashMessage('success', "Statut de l'utilisateur {$id} modifié.");
                    break;

                case 'reset_password':
                    $this->requirePermission('TRAIT_ADMIN_GERER_UTILISATEURS_MODIFIER');
                    $this->serviceUtilisateur->reinitialiserMotDePasseAdmin($id);
                    $this->addFlashMessage('success', "Mot de passe réinitialisé pour {$id}. Un email a été envoyé.");
                    break;

                case 'impersonate':
                    $this->requirePermission('TRAIT_ADMIN_IMPERSONATE_USER');
                    $adminId = $this->securiteService->getUtilisateurConnecte()['numero_utilisateur'];
                    $this->securiteService->demarrerImpersonation($adminId, $id);
                    $this->addFlashMessage('info', "Vous impersonnalisez maintenant l'utilisateur {$id}.");
                    $this->redirect('/dashboard');
                    return;

                case 'delete':
                    $this->requirePermission('TRAIT_ADMIN_GERER_UTILISATEURS_SUPPRIMER');
                    $this->serviceUtilisateur->supprimerUtilisateurEtEntite($id);
                    $this->addFlashMessage('success', "L'utilisateur {$id} a été supprimé.");
                    break;

                default:
                    $this->addFlashMessage('error', 'Action non reconnue.');
                    break;
            }
        } catch (Exception $e) {
            $this->addFlashMessage('error', "Erreur lors de l'action : " . $e->getMessage());
            error_log("Erreur UtilisateurController::handleUserAction: " . $e->getMessage());
        }

        $this->redirect('/admin/utilisateurs');
    }

    // === MÉTHODES UTILITAIRES ===

    /**
     * Retourne le titre du formulaire selon le type et mode
     */
    private function getFormTitle(string $type, bool $isEdit): string
    {
        $typeLabels = [
            'generic' => 'Utilisateur Générique',
            'etudiant' => 'Étudiant',
            'enseignant' => 'Enseignant',
            'personnel' => 'Personnel'
        ];

        $prefix = $isEdit ? 'Modifier' : 'Créer';
        return "{$prefix} " . ($typeLabels[$type] ?? 'Utilisateur');
    }

    /**
     * Retourne l'URL d'action selon le type et utilisateur
     */
    private function getActionUrl(string $type, ?array $user): string
    {
        if ($user) {
            return "/admin/utilisateurs/{$user['numero_utilisateur']}/edit";
        }

        return match($type) {
            'etudiant' => '/admin/utilisateurs/etudiant/form',
            'enseignant' => '/admin/utilisateurs/enseignant/form',
            'personnel' => '/admin/utilisateurs/personnel/form',
            default => '/admin/utilisateurs/form'
        };
    }

    /**
     * Retourne l'URL du formulaire selon le type
     */
    private function getFormUrl(string $type): string
    {
        return match($type) {
            'etudiant' => '/admin/utilisateurs/etudiant/form',
            'enseignant' => '/admin/utilisateurs/enseignant/form',
            'personnel' => '/admin/utilisateurs/personnel/form',
            default => '/admin/utilisateurs/form'
        };
    }

    /**
     * Retourne le nom de la vue selon le type
     */
    private function getViewName(string $type): string
    {
        return match($type) {
            'etudiant' => 'Administration/Utilisateurs/form_etudiant',
            'enseignant' => 'Administration/Utilisateurs/form_enseignant',
            'personnel' => 'Administration/Utilisateurs/form_personnel',
            default => 'Administration/Utilisateurs/form_generic'
        };
    }

    /**
     * Détermine le type d'utilisateur à partir des données utilisateur
     */
    private function getUserTypeFromUser(array $user): string
    {
        return match($user['id_type_utilisateur'] ?? '') {
            'TYPE_ETUDIANT', 'TYPE_ETUD' => 'etudiant',
            'TYPE_ENSEIGNANT', 'TYPE_ENS' => 'enseignant',
            'TYPE_PERSONNEL_ADMIN', 'TYPE_PERS_ADMIN' => 'personnel',
            default => 'generic'
        };
    }

    /**
     * Ajoute les données spécifiques selon le type
     */
    private function addTypeSpecificData(array &$data, string $type): void
    {
        switch ($type) {
            case 'etudiant':
                $data['niveaux_etude'] = $this->serviceSysteme->gererReferentiel('list', 'niveau_etude');
                $data['annees_academiques'] = $this->serviceSysteme->gererReferentiel('list', 'annee_academique');
                break;

            case 'enseignant':
                $data['grades'] = $this->serviceSysteme->gererReferentiel('list', 'grade_enseignant');
                $data['specialites'] = $this->serviceSysteme->gererReferentiel('list', 'specialite');
                break;

            case 'personnel':
                $data['fonctions'] = $this->serviceSysteme->gererReferentiel('list', 'fonction_personnel');
                break;
        }
    }

    /**
     * Retourne les règles de validation selon le type
     */
    private function getValidationRules(string $type, bool $isEdit = false): array
    {
        $baseRules = [
            'nom' => 'required|max:100',
            'prenom' => 'required|max:100',
            'email_principal' => 'required|email|max:255',
            'id_groupe_utilisateur' => 'required',
            'id_niveau_acces_donne' => 'required'
        ];

        if (!$isEdit) {
            $baseRules['login_utilisateur'] = 'required|max:100';
            $baseRules['mot_de_passe'] = 'required|min:8';
        } else {
            $baseRules['mot_de_passe'] = 'min:8'; // Optionnel en modification
        }

        // Règles spécifiques selon le type
        switch ($type) {
            case 'etudiant':
                $baseRules['numero_etudiant'] = $isEdit ? 'max:50' : 'required|max:50';
                $baseRules['id_niveau_etude'] = 'required';
                break;

            case 'enseignant':
                $baseRules['numero_enseignant'] = $isEdit ? 'max:50' : 'required|max:50';
                $baseRules['id_grade'] = 'required';
                $baseRules['id_specialite'] = 'required';
                break;

            case 'personnel':
                $baseRules['numero_personnel'] = $isEdit ? 'max:50' : 'required|max:50';
                $baseRules['id_fonction'] = 'required';
                break;
        }

        return $baseRules;
    }

    /**
     * Prépare les données utilisateur selon le type
     */
    private function prepareUserData(array $data, string $type, bool $isEdit = false): array
    {
        $userData = [
            'login_utilisateur' => $data['login_utilisateur'] ?? '',
            'email_principal' => $data['email_principal'],
            'id_groupe_utilisateur' => $data['id_groupe_utilisateur'],
            'id_niveau_acces_donne' => $data['id_niveau_acces_donne'],
            'id_type_utilisateur' => $this->getTypeUtilisateurCode($type),
            'statut_compte' => $data['statut_compte'] ?? 'actif'
        ];

        if (!$isEdit && !empty($data['mot_de_passe'])) {
            $userData['mot_de_passe'] = $data['mot_de_passe'];
        } elseif ($isEdit && !empty($data['mot_de_passe'])) {
            $userData['mot_de_passe'] = $data['mot_de_passe'];
        }

        return $userData;
    }

    /**
     * Prépare les données de profil selon le type
     */
    private function prepareProfileData(array $data, string $type, bool $isEdit = false): array
    {
        $profileData = [
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'date_naissance' => $data['date_naissance'] ?? null,
            'telephone' => $data['telephone'] ?? null,
            'email_professionnel' => $data['email_professionnel'] ?? null
        ];

        // Données spécifiques selon le type
        switch ($type) {
            case 'etudiant':
                if (!$isEdit) $profileData['numero_etudiant'] = $data['numero_etudiant'];
                $profileData['id_niveau_etude'] = $data['id_niveau_etude'];
                $profileData['annee_academique'] = $data['annee_academique'] ?? null;
                break;

            case 'enseignant':
                if (!$isEdit) $profileData['numero_enseignant'] = $data['numero_enseignant'];
                $profileData['id_grade'] = $data['id_grade'];
                $profileData['id_specialite'] = $data['id_specialite'];
                $profileData['date_prise_fonction'] = $data['date_prise_fonction'] ?? null;
                break;

            case 'personnel':
                if (!$isEdit) $profileData['numero_personnel'] = $data['numero_personnel'];
                $profileData['id_fonction'] = $data['id_fonction'];
                $profileData['date_embauche'] = $data['date_embauche'] ?? null;
                break;
        }

        return $profileData;
    }

    /**
     * Retourne le code type utilisateur selon le type
     */
    private function getTypeUtilisateurCode(string $type): string
    {
        return match($type) {
            'etudiant' => 'TYPE_ETUDIANT',
            'enseignant' => 'TYPE_ENSEIGNANT',
            'personnel' => 'TYPE_PERSONNEL_ADMIN',
            default => 'TYPE_ADMIN'
        };
    }

    /**
     * Affiche le formulaire d'import
     */
    private function showImportForm(): void
    {
        $data = [
            'title' => 'Import Étudiants en Masse',
            'csrf_token' => $this->generateCsrfToken('import_form'),
            'annees_academiques' => $this->serviceSysteme->gererReferentiel('list', 'annee_academique'),
            'niveaux_etude' => $this->serviceSysteme->gererReferentiel('list', 'niveau_etude')
        ];

        $this->render('Administration/Utilisateurs/import_etudiants', $data);
    }

    /**
     * Traite l'import d'étudiants
     */
    private function handleImportEtudiants(): void
    {
        if (!$this->validateCsrfToken('import_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/utilisateurs/import-etudiants');
            return;
        }

        try {
            // Vérification du fichier uploadé
            if (!isset($_FILES['fichier_import']) || $_FILES['fichier_import']['error'] !== UPLOAD_ERR_OK) {
                throw new ValidationException("Aucun fichier valide uploadé.");
            }

            $fichier = $_FILES['fichier_import'];
            $parametres = [
                'annee_academique' => $_POST['annee_academique'] ?? '',
                'niveau_etude_defaut' => $_POST['niveau_etude_defaut'] ?? '',
                'groupe_defaut' => $_POST['groupe_defaut'] ?? 'GRP_ETUDIANT'
            ];

            // Traitement via le service
            $resultat = $this->serviceUtilisateur->importerEtudiants($fichier, $parametres);

            $this->addFlashMessage('success',
                "Import terminé. {$resultat['crees']} étudiants créés, {$resultat['erreurs']} erreurs.");

            if (!empty($resultat['details_erreurs'])) {
                $this->addFlashMessage('warning', 'Détails des erreurs : ' . implode(', ', $resultat['details_erreurs']));
            }

        } catch (ValidationException $e) {
            $this->addFlashMessage('error', 'Erreur de validation : ' . $e->getMessage());
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur lors de l\'import : ' . $e->getMessage());
            error_log("Erreur import étudiants: " . $e->getMessage());
        }

        $this->redirect('/admin/utilisateurs/import-etudiants');
    }
}