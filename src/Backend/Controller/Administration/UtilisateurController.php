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
        error_log("DEBUG: Permission OK"); // Ce log confirme que la permission est acquise.

        try {
            $filters = $this->getGetData();
            // L'erreur SQL "Unknown column 'u.login'" vient de `listerUtilisateursComplets`.
            // La correction doit se faire dans ServiceUtilisateur::listerUtilisateursComplets
            // ET cette version corrigée doit être déployée.
            $users = $this->serviceUtilisateur->listerUtilisateursComplets($filters);

            // Correction: Votre table `groupe_utilisateur` a `libelle_groupe`, pas `nom_groupe`.
            // Assurez-vous que gererReferentiel renvoie les bonnes colonnes.
            // Si `gererReferentiel` renvoie juste un tableau associatif type `id => libelle`,
            // alors c'est la vue qui devra s'adapter.
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
            // Si l'erreur est critique, rediriger vers le dashboard, sinon rester sur la page avec un message
            $this->redirect('/admin/dashboard'); // Redirection vers le dashboard comme dans votre log
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
            // Correction du nom de la vue: `form_utilisateur_generic` existe, `form_generic` non.
            $this->showForm('generic', null, 'Administration/Utilisateurs/form_utilisateur_generic');
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
            // Vue correcte: `form_etudiant`
            $this->showForm('etudiant', null, 'Administration/Utilisateurs/form_etudiant');
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
            // Vue correcte: `form_enseignant`
            $this->showForm('enseignant', null, 'Administration/Utilisateurs/form_enseignant');
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
            // Vue correcte: `form_personnel`
            $this->showForm('personnel', null, 'Administration/Utilisateurs/form_personnel');
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
            // Correction du nom de la vue: `import_etudiants_form` existe, `import_etudiants` non.
            $this->showImportForm();
        }
    }

    /**
     * Méthode générique pour afficher les formulaires selon le type
     * Correction: Ajout du paramètre $viewPath pour spécifier directement le chemin de la vue.
     */
    private function showForm(string $type, ?array $user = null, string $viewPath = null): void
    {
        $isEdit = !empty($user);

        try {
            // Données communes à tous les formulaires
            $data = [
                'title' => $this->getFormTitle($type, $isEdit),
                'type' => $type,
                'user' => $user,
                // Correction: `libelle_groupe` est le nom de colonne correct
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
            // Si viewPath est fourni, l'utiliser, sinon utiliser la logique par défaut
            $finalView = $viewPath ?: $this->getViewName($type);

            $this->render($finalView, $data);
            unset($_SESSION['form_errors'], $_SESSION['form_data']);

        } catch (Exception $e) {
            error_log("ERROR UtilisateurController::showForm: " . $e->getMessage()); // Log l'erreur ici aussi
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
//    private function processUserCreation(string $type): void
//    {
//        // Correction: Utiliser le bon nom de vue pour la redirection en cas d'erreur
//        $formRedirectUrl = $this->getFormUrl($type);
//
//        if (!$this->validateCsrfToken('user_form', $_POST['csrf_token'] ?? '')) {
//            $this->addFlashMessage('error', 'Token CSRF invalide.'); // Message plus clair
//            $this->redirect($formRedirectUrl);
//            return;
//        }
//
//        $data = $this->getPostData();
//        $rules = $this->getValidationRules($type);
//
//        if (!$this->validator->validate($data, $rules)) {
//            $_SESSION['form_errors'] = $this->validator->getErrors();
//            $_SESSION['form_data'] = $data;
//            $this->redirect($formRedirectUrl);
//            return;
//        }
//
//        try {
//            // Préparation des données selon le type
//            // Correction: Assurez-vous que les clés de $data passées à prepareUserData/prepareProfileData
//            // correspondent à ce que `ServiceUtilisateur` attend (login_utilisateur, email_principal, etc.)
//            $userData = $this->prepareUserData($data, $type);
//            $profileData = $this->prepareProfileData($data, $type);
//
//            // Création via le service
//            $numeroUtilisateur = $this->serviceUtilisateur->creerUtilisateurComplet($userData, $profileData, $type);
//
//            $this->addFlashMessage('success', "Utilisateur {$type} créé avec succès. ID: {$numeroUtilisateur}");
//            $this->redirect('/admin/utilisateurs');
//
//        } catch (DoublonException | OperationImpossibleException | ValidationException $e) {
//            $this->addFlashMessage('error', 'Erreur de création : ' . $e->getMessage());
//            $_SESSION['form_data'] = $data;
//            $this->redirect($formRedirectUrl);
//        } catch (Exception $e) {
//            $this->addFlashMessage('error', 'Erreur inattendue : ' . $e->getMessage());
//            error_log("Erreur création utilisateur {$type}: " . $e->getMessage());
//            $this->redirect($formRedirectUrl);
//        }
//    }

    /**
     * Modification d'utilisateur
     */
    public function edit(string $id): void
    {
        // Utiliser une permission de modification si elle existe, sinon utiliser création
        // Correction: Assurez-vous que cette permission existe ou utilisez une plus générique si non
        $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_MODIFIER_ACCES'); // Supposons cette permission

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
            // Correction du nom de la vue dans showForm
            $this->showForm($type, $user, $this->getViewName($type));

        } catch (ElementNonTrouveException $e) {
            $this->addFlashMessage('error', $e->getMessage());
            $this->redirect('/admin/utilisateurs');
        } catch (Exception $e) {
            error_log("ERROR UtilisateurController::showEditForm: " . $e->getMessage()); // Log l'erreur ici aussi
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
            $this->addFlashMessage('error', 'Token CSRF invalide.');
            $this->redirect("/admin/utilisateurs/{$id}/edit");
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
            // Correction: Passer les données `login_utilisateur` et `email_principal` correctement
            // Assurez-vous que prepareUserData pour edit ne change pas le login si non soumis
            $userData = $this->prepareUserData($data, $type, true);
            $profileData = $this->prepareProfileData($data, $type, true);

            // Appel à mettreAJourUtilisateur, assurez-vous qu'elle gère bien les données de profil séparément
            // (comme corrigé dans ServiceUtilisateur)
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
        // Correction: Assurez-vous que cette permission existe ou utilisez une plus générique si non
        $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_SUPPRIMER_ACCES'); // Supposons cette permission

        if (!$this->isPostRequest() || !$this->validateCsrfToken('user_actions_form', $_POST['csrf_token'] ?? '')) {
            $this->addFlashMessage('error', 'Token CSRF invalide ou méthode non autorisée.'); // Message plus clair
            $this->redirect('/admin/utilisateurs');
            return;
        }

        try {
            $this->serviceUtilisateur->supprimerUtilisateurEtEntite($id);
            $this->addFlashMessage('success', "L'utilisateur {$id} a été supprimé.");
        } catch (Exception $e) {
            $this->addFlashMessage('error', "Erreur lors de la suppression : " . $e->getMessage());
            error_log("Erreur UtilisateurController::delete: " . $e->getMessage()); // Log l'erreur
        }

        $this->redirect('/admin/utilisateurs');
    }

    /**
     * Actions diverses sur utilisateur (changement de statut, reset mot de passe, impersonnation, suppression)
     */
    public function handleUserAction(string $id): void
    {
        if (!$this->isPostRequest() || !$this->validateCsrfToken('user_actions_form', $_POST['csrf_token'] ?? '')) {
            $this->addFlashMessage('error', 'Token CSRF invalide ou méthode non autorisée.');
            $this->redirect('/admin/utilisateurs');
            return;
        }

        $action = $_POST['action'] ?? '';

        try {
            switch ($action) {
                case 'change_status':
                    // Correction: Assurez-vous que cette permission existe ou utilisez une plus générique
                    $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_MODIFIER_ACCES');
                    $this->serviceUtilisateur->changerStatutCompte($id, $_POST['status'] ?? '');
                    $this->addFlashMessage('success', "Statut de l'utilisateur {$id} modifié.");
                    break;

                case 'reset_password':
                    // Correction: Assurez-vous que cette permission existe ou utilisez une plus générique
                    $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_MODIFIER_ACCES');
                    $this->serviceUtilisateur->reinitialiserMotDePasseAdmin($id);
                    $this->addFlashMessage('success', "Mot de passe réinitialisé pour {$id}. Un email a été envoyé.");
                    break;

                case 'impersonate':
                    $this->requirePermission('TRAIT_ADMIN_IMPERSONATE_USER'); // Assurez-vous que cette permission est définie
                    $adminId = $this->securiteService->getUtilisateurConnecte()['numero_utilisateur'];
                    $this->securiteService->demarrerImpersonation($adminId, $id);
                    $this->addFlashMessage('info', "Vous impersonnalisez maintenant l'utilisateur {$id}.");
                    $this->redirect('/dashboard');
                    return;

                case 'delete':
                    // Correction: Assurez-vous que cette permission existe ou utilisez une plus générique
                    $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_SUPPRIMER_ACCES');
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
//    private function getFormUrl(string $type): string
//    {
//        return match($type) {
//            'etudiant' => '/admin/utilisateurs/etudiant/form',
//            'enseignant' => '/admin/utilisateurs/enseignant/form',
//            'personnel' => '/admin/utilisateurs/personnel/form',
//            default => '/admin/utilisateurs/form'
//        };
//    }

    /**
     * Retourne le nom de la vue selon le type
     * Correction: Utilisation des noms de fichiers de vue exacts que vous avez fournis.
     */
    private function getViewName(string $type): string
    {
        return match($type) {
            'etudiant' => 'Administration/Utilisateurs/form_etudiant',
            'enseignant' => 'Administration/Utilisateurs/form_enseignant',
            'personnel' => 'Administration/Utilisateurs/form_personnel',
            'generic' => 'Administration/Utilisateurs/form_utilisateur_generic', // Correction ici
            default => 'Administration/Utilisateurs/form_utilisateur' // Ou un fichier générique de secours si vous en avez un
        };
    }

    /**
     * Détermine le type d'utilisateur à partir des données utilisateur
     * Correction: Utilisation des IDs de type_utilisateur exacts de votre DB.
     */
    private function getUserTypeFromUser(array $user): string
    {
        return match($user['id_type_utilisateur'] ?? '') {
            'TYPE_ETUD' => 'etudiant', // Utiliser l'ID court de votre table type_utilisateur
            'TYPE_ENS' => 'enseignant', // Utiliser l'ID court de votre table type_utilisateur
            'TYPE_PERS_ADMIN' => 'personnel', // Utiliser l'ID court de votre table type_utilisateur
            'TYPE_ADMIN' => 'personnel', // Un admin est souvent un type de personnel
            default => 'generic'
        };
    }

    /**
     * Ajoute les données spécifiques selon le type
     * Correction: Les `gererReferentiel` doivent demander les entités qui existent dans la DB.
     * Si `grade_enseignant` et `fonction_personnel` n'existent pas en tant que tables/vues,
     * ces appels vont échouer et générer les erreurs "Table not found".
     * Il faudrait interroger les tables `grade` et `fonction` directement.
     */
    private function addTypeSpecificData(array &$data, string $type): void
    {
        switch ($type) {
            case 'etudiant':
                $data['niveaux_etude'] = $this->serviceSysteme->gererReferentiel('list', 'niveau_etude');
                $data['annees_academiques'] = $this->serviceSysteme->gererReferentiel('list', 'annee_academique');
                break;

            case 'enseignant':
                // Correction: `grade_enseignant` n'est pas une table/vue. Récupérez les grades depuis la table `grade`.
                $data['grades'] = $this->serviceSysteme->gererReferentiel('list', 'grade');
                $data['specialites'] = $this->serviceSysteme->gererReferentiel('list', 'specialite');
                break;

            case 'personnel':
                // Correction: `fonction_personnel` n'est pas une table/vue. Récupérez les fonctions depuis la table `fonction`.
                $data['fonctions'] = $this->serviceSysteme->gererReferentiel('list', 'fonction');
                break;

            case 'generic':
                // ✅ AJOUTÉ : Récupérer TOUTES les données pour le formulaire générique
                $data['niveaux_etude'] = $this->serviceSysteme->gererReferentiel('list', 'niveau_etude');
                $data['annees_academiques'] = $this->serviceSysteme->gererReferentiel('list', 'annee_academique');
                $data['grades'] = $this->serviceSysteme->gererReferentiel('list', 'grade');
                $data['specialites'] = $this->serviceSysteme->gererReferentiel('list', 'specialite');
                $data['fonctions'] = $this->serviceSysteme->gererReferentiel('list', 'fonction');
                break;
        }
    }

    /**
     * Retourne les règles de validation selon le type
     * Correction: Ajustement des noms de champs (`login_utilisateur`, `mot_de_passe`)
     * `numero_etudiant`, `numero_enseignant`, `numero_personnel` ne sont pas des champs de validation
     * dans les données `$_POST` pour la création/modification d'utilisateurs, ils sont générés par le service.
     * Ces champs devraient être les IDs des _entités_ liées, pas des "numéros d'utilisateur" qui sont les FK.
     */
    private function getValidationRules(string $type, bool $isEdit = false): array
    {
        $baseRules = [
            'nom' => 'required|max:100',
            'prenom' => 'required|max:100',
            'email_principal' => 'required|email|max:255',
            'id_groupe_utilisateur' => 'required',
            'id_niveau_acces_donne' => 'required',
            'id_type_utilisateur' => 'required' // ✅ AJOUTÉ pour le formulaire générique
        ];

        // Pour la création, le login et le mot de passe sont requis
        if (!$isEdit) {
            $baseRules['login_utilisateur'] = 'required|max:100';
            $baseRules['password'] = 'required|min:8';
        } else {
            $baseRules['password'] = 'min:8';
        }

        // Règles spécifiques selon le type de profil
        switch ($type) {
            case 'etudiant':
                $baseRules['id_niveau_etude'] = 'required';
                $baseRules['sexe'] = 'required';
                break;

            case 'enseignant':
                $baseRules['id_grade'] = 'required';
                $baseRules['id_specialite'] = 'required';
                $baseRules['sexe'] = 'required';
                break;

            case 'personnel':
                $baseRules['id_fonction'] = 'required';
                $baseRules['sexe'] = 'required';
                break;

            case 'generic':
                // ✅ NOUVEAU : Validation dynamique selon le type d'utilisateur sélectionné
                $baseRules['sexe'] = 'required';
                // La validation des champs spécifiques sera gérée par JavaScript côté client
                // et par la logique métier côté serveur
                break;
        }

        return $baseRules;
    }


    /**
     * Prépare les données utilisateur (pour la table `utilisateur`)
     * Correction: Utilisation des noms de champs exacts de votre DB.
     */
    private function prepareUserData(array $data, string $type, bool $isEdit = false): array
    {
        $userData = [
            'login_utilisateur' => $data['login_utilisateur'] ?? null, // Correction nom de champ
            'email_principal' => $data['email_principal'], // Correction nom de champ
            'id_groupe_utilisateur' => $data['id_groupe_utilisateur'],
            'id_niveau_acces_donne' => $data['id_niveau_acces_donne'],
            'id_type_utilisateur' => $this->getTypeUtilisateurCode($type),
            'statut_compte' => $data['statut_compte'] ?? 'actif'
        ];

        // Le mot de passe n'est pas envoyé par le formulaire si on est en édition et qu'il n'est pas changé.
        // Si le champ 'password' est présent et non vide, on le hache.
        if (isset($data['password']) && !empty($data['password'])) { // Utilisez 'password' pour le champ du formulaire
            $userData['password'] = $data['password']; // Le service se charge du hashage
        } elseif (!$isEdit) {
            // Pour la création, le mot de passe est obligatoire et doit être présent dans $data.
            // La validation des règles gérera déjà ça, mais c'est une sécurité supplémentaire.
            throw new ValidationException("Le mot de passe est manquant pour la création d'utilisateur.");
        }
        // Pour l'édition, si 'password' n'est pas présent ou est vide, on ne le modifie pas.

        return $userData;
    }

    /**
     * Prépare les données de profil (pour les tables `etudiant`, `enseignant`, `personnel_administratif`)
     * Correction: Utilisation des noms de champs exacts de votre DB et gestion des IDs d'entités.
     */
//    private function prepareProfileData(array $data, string $type, bool $isEdit = false): array
//    {
//        $profileData = [
//            'nom' => $data['nom'],
//            'prenom' => $data['prenom'],
//            // Champs communs mais noms différents dans les tables de profil
//            'date_naissance' => $data['date_naissance'] ?? null,
//            'sexe' => $data['sexe'] ?? null, // 'sexe' est le nom de la colonne dans toutes les tables de profil
//            'adresse_postale' => $data['adresse_postale'] ?? null, // Nom de colonne réel
//            'ville' => $data['ville'] ?? null,
//            'code_postal' => $data['code_postal'] ?? null,
//            'nationalite' => $data['nationalite'] ?? null,
//            'pays_naissance' => $data['pays_naissance'] ?? null,
//            'lieu_naissance' => $data['lieu_naissance'] ?? null,
//        ];
//
//        // Données spécifiques selon le type
//        switch ($type) {
//            case 'etudiant':
//                // Le `numero_carte_etudiant` est généré par le service lors de la création
//                // Pour l'édition, il serait déjà dans $user['numero_carte_etudiant'] et n'est pas un champ à modifier ici.
//                // $profileData['numero_carte_etudiant'] = $data['numero_etudiant']; // Si le formulaire envoie numero_etudiant
//                $profileData['telephone'] = $data['telephone'] ?? null; // Nom de colonne réel
//                $profileData['email_contact_secondaire'] = $data['email_contact_secondaire'] ?? null; // Nom de colonne réel
//                $profileData['contact_urgence_nom'] = $data['contact_urgence_nom'] ?? null;
//                $profileData['contact_urgence_telephone'] = $data['contact_urgence_telephone'] ?? null;
//                $profileData['contact_urgence_relation'] = $data['contact_urgence_relation'] ?? null;
//                // Note: id_niveau_etude et annee_academique sont gérés dans la table `inscrire`, pas `etudiant`.
//                // Si vous voulez les sauvegarder, le service utilisateur doit avoir une méthode pour interagir avec `inscrire`.
//                break;
//
//            case 'enseignant':
//                // Le `numero_enseignant` est généré par le service lors de la création.
//                $profileData['telephone_professionnel'] = $data['telephone_professionnel'] ?? null;
//                $profileData['email_professionnel'] = $data['email_professionnel'] ?? null;
//                $profileData['telephone_personnel'] = $data['telephone_personnel'] ?? null;
//                $profileData['email_personnel_secondaire'] = $data['email_personnel_secondaire'] ?? null;
//                $profileData['specialite'] = $data['id_specialite'] ?? null; // Assurez-vous que c'est l'ID de specialite
//                // Note: id_grade et date_prise_fonction sont gérés dans la table `acquerir`, pas `enseignant`.
//                // Le service utilisateur devra gérer l'insertion dans `acquerir` si ces données sont présentes.
//                break;
//
//            case 'personnel':
//                // Le `numero_personnel_administratif` est généré par le service lors de la création.
//                $profileData['telephone_professionnel'] = $data['telephone_professionnel'] ?? null;
//                $profileData['email_professionnel'] = $data['email_professionnel'] ?? null;
//                $profileData['date_affectation_service'] = $data['date_affectation_service'] ?? null; // Nom de colonne réel
//                $profileData['responsabilites_cles'] = $data['responsabilites_cles'] ?? null;
//                $profileData['telephone_personnel'] = $data['telephone_personnel'] ?? null;
//                $profileData['email_personnel_secondaire'] = $data['email_personnel_secondaire'] ?? null;
//                $profileData['poste'] = $data['poste'] ?? null; // Assurez-vous que c'est le libellé du poste
//                $profileData['service'] = $data['service'] ?? null; // Assurez-vous que c'est le libellé du service
//                // Note: id_fonction et date_embauche sont gérés dans la table `occuper`, pas `personnel_administratif`.
//                // Le service utilisateur devra gérer l'insertion dans `occuper` si ces données sont présentes.
//                break;
//        }
//
//        return $profileData;
//    }

    /**
     * Retourne le code type utilisateur selon le type
     * Correction: Utilisation des IDs de type_utilisateur exacts de votre DB.
     */
    private function getTypeUtilisateurCode(string $type): string
    {
        return match($type) {
            'etudiant' => 'TYPE_ETUD',
            'enseignant' => 'TYPE_ENS',
            'personnel' => 'TYPE_PERS_ADMIN',
            'generic' => 'TYPE_ADMIN', // Si le générique est pour les admins
            default => 'TYPE_ADMIN' // Fallback si le type n'est pas reconnu
        };
    }

    /**
     * Affiche le formulaire d'import
     * Correction: Utilisation du nom de fichier de vue exact.
     */
    private function showImportForm(): void
    {
        $data = [
            'title' => 'Import Étudiants en Masse',
            'csrf_token' => $this->generateCsrfToken('import_form'),
            'annees_academiques' => $this->serviceSysteme->gererReferentiel('list', 'annee_academique'),
            'niveaux_etude' => $this->serviceSysteme->gererReferentiel('list', 'niveau_etude')
        ];

        $this->render('Administration/Utilisateurs/import_etudiants_form', $data); // Correction ici
    }

    /**
     * Traite l'import d'étudiants
     */
    private function handleImportEtudiants(): void
    {
        if (!$this->validateCsrfToken('import_form', $_POST['csrf_token'] ?? '')) {
            $this->addFlashMessage('error', 'Token CSRF invalide.');
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
            // Assurez-vous que serviceUtilisateur->importerEtudiants accepte ces paramètres.
            $resultat = $this->serviceUtilisateur->importerEtudiantsDepuisFichier($fichier['tmp_name'], $parametres); // Passer le chemin temporaire

            $this->addFlashMessage('success',
                "Import terminé. {$resultat['crees']} étudiants créés, {$resultat['erreurs']} erreurs.");

            if (!empty($resultat['details_erreurs'])) {
                $this->addFlashMessage('warning', 'Détails des erreurs : ' . implode(', ', $resultat['details_erreurs']));
            }

        } catch (ValidationException $e) {
            $this->addFlashMessage('error', 'Erreur de validation : ' . $e->getMessage());
            error_log("Erreur validation import étudiants: " . $e->getMessage()); // Log l'erreur
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur lors de l\'import : ' . $e->getMessage());
            error_log("Erreur import étudiants: " . $e->getMessage());
        }

        $this->redirect('/admin/utilisateurs/import-etudiants');
    }

    private function processUserCreation(string $type): void
    {
        $formRedirectUrl = $this->getFormUrl($type);

        if (!$this->validateCsrfToken('user_form', $_POST['csrf_token'] ?? '')) {
            $this->addFlashMessage('error', 'Token CSRF invalide.');
            $this->redirect($formRedirectUrl);
            return;
        }

        $data = $this->getPostData();

        // ✅ NOUVEAU : Pour le type generic, déterminer le vrai type depuis les données
        $realType = $type;
        if ($type === 'generic') {
            $realType = match($data['id_type_utilisateur'] ?? '') {
                'TYPE_ETUD' => 'etudiant',
                'TYPE_ENS' => 'enseignant',
                'TYPE_PERS_ADMIN' => 'personnel',
                default => 'generic'
            };

            // ✅ Validation supplémentaire pour les champs requis selon le type réel
            $this->validateAdditionalFieldsForGeneric($data, $realType);
        }

        $rules = $this->getValidationRules($type);

        if (!$this->validator->validate($data, $rules)) {
            $_SESSION['form_errors'] = $this->validator->getErrors();
            $_SESSION['form_data'] = $data;
            $this->redirect($formRedirectUrl);
            return;
        }

        try {
            // Utiliser le vrai type pour la préparation des données
            $userData = $this->prepareUserData($data, $realType);
            $profileData = $this->prepareProfileData($data, $realType);

            // Création via le service avec le type réel
            $numeroUtilisateur = $this->serviceUtilisateur->creerUtilisateurComplet($userData, $profileData, $realType);

            $this->addFlashMessage('success', "Utilisateur {$realType} créé avec succès. ID: {$numeroUtilisateur}");
            $this->redirect('/admin/utilisateurs');

        } catch (DoublonException | OperationImpossibleException | ValidationException $e) {
            $this->addFlashMessage('error', 'Erreur de création : ' . $e->getMessage());
            error_log("Erreur création utilisateur {$type}: " . $e->getMessage());
            $_SESSION['form_data'] = $data;
            $this->redirect($formRedirectUrl);
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur inattendue : ' . $e->getMessage());
            error_log("Erreur inattendue création utilisateur {$type}: " . $e->getMessage());
            $_SESSION['form_data'] = $data;
            $this->redirect($formRedirectUrl);
        }
    }

// 3. ✅ NOUVELLE MÉTHODE : Validation supplémentaire pour le formulaire générique
    private function validateAdditionalFieldsForGeneric(array $data, string $realType): void
    {
        $errors = [];

        switch ($realType) {
            case 'etudiant':
                if (empty($data['id_niveau_etude'])) {
                    $errors['id_niveau_etude'] = 'Le niveau d\'étude est obligatoire pour un étudiant.';
                }
                break;

            case 'enseignant':
                if (empty($data['id_grade'])) {
                    $errors['id_grade'] = 'Le grade est obligatoire pour un enseignant.';
                }
                if (empty($data['id_specialite'])) {
                    $errors['id_specialite'] = 'La spécialité est obligatoire pour un enseignant.';
                }
                break;

            case 'personnel':
                if (empty($data['id_fonction'])) {
                    $errors['id_fonction'] = 'La fonction est obligatoire pour un personnel.';
                }
                break;
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = array_merge($_SESSION['form_errors'] ?? [], $errors);
            $_SESSION['form_data'] = $data;
            $this->redirect($this->getFormUrl('generic'));
            exit;
        }
    }

// 4. Modifier la méthode prepareProfileData() pour gérer le type 'generic'
    private function prepareProfileData(array $data, string $type, bool $isEdit = false): array
    {
        $profileData = [
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'date_naissance' => $data['date_naissance'] ?? null,
            'sexe' => $data['sexe'] ?? null,
            'adresse_postale' => $data['adresse_postale'] ?? null,
            'ville' => $data['ville'] ?? null,
            'code_postal' => $data['code_postal'] ?? null,
            'nationalite' => $data['nationalite'] ?? null,
            'pays_naissance' => $data['pays_naissance'] ?? null,
            'lieu_naissance' => $data['lieu_naissance'] ?? null,
        ];

        // Données spécifiques selon le type
        switch ($type) {
            case 'etudiant':
                $profileData['telephone'] = $data['telephone'] ?? null;
                $profileData['email_contact_secondaire'] = $data['email_contact_secondaire'] ?? null;
                $profileData['contact_urgence_nom'] = $data['contact_urgence_nom'] ?? null;
                $profileData['contact_urgence_telephone'] = $data['contact_urgence_telephone'] ?? null;
                $profileData['contact_urgence_relation'] = $data['contact_urgence_relation'] ?? null;
                break;

            case 'enseignant':
                $profileData['telephone'] = $data['telephone'] ?? null;
                $profileData['email_contact_secondaire'] = $data['email_contact_secondaire'] ?? null;
                break;

            case 'personnel':
                $profileData['telephone'] = $data['telephone'] ?? null;
                $profileData['poste'] = $data['poste'] ?? null;
                $profileData['service_affectation'] = $data['service_affectation'] ?? null;
                break;

            case 'generic':
                // ✅ NOUVEAU : Pour le type generic, inclure tous les champs possibles
                $profileData['telephone'] = $data['telephone'] ?? null;
                $profileData['email_contact_secondaire'] = $data['email_contact_secondaire'] ?? null;
                $profileData['contact_urgence_nom'] = $data['contact_urgence_nom'] ?? null;
                $profileData['contact_urgence_telephone'] = $data['contact_urgence_telephone'] ?? null;
                $profileData['contact_urgence_relation'] = $data['contact_urgence_relation'] ?? null;
                $profileData['poste'] = $data['poste'] ?? null;
                $profileData['service_affectation'] = $data['service_affectation'] ?? null;
                break;
        }

        return $profileData;
    }

// 5. ✅ NOUVELLE MÉTHODE : Gestion des relations spécifiques pour le formulaire générique
    private function handleGenericTypeSpecificData(array $data, string $realType, string $numeroUtilisateur): void
    {
        try {
            switch ($realType) {
                case 'etudiant':
                    // Gérer l'inscription niveau d'étude si fournie
                    if (!empty($data['id_niveau_etude']) && !empty($data['annee_academique'])) {
                        // Utiliser un service d'inscription ou directement le modèle
                        // $this->serviceUtilisateur->inscrireEtudiant($numeroUtilisateur, $data['id_niveau_etude'], $data['annee_academique']);
                    }
                    break;

                case 'enseignant':
                    // Gérer l'attribution du grade si fournie
                    if (!empty($data['id_grade'])) {
                        // Utiliser un service approprié pour lier l'enseignant au grade
                        // $this->serviceUtilisateur->attribuerGrade($numeroUtilisateur, $data['id_grade']);
                    }
                    break;

                case 'personnel':
                    // Gérer l'attribution de la fonction si fournie
                    if (!empty($data['id_fonction'])) {
                        // Utiliser un service approprié pour lier le personnel à la fonction
                        // $this->serviceUtilisateur->attribuerFonction($numeroUtilisateur, $data['id_fonction']);
                    }
                    break;
            }
        } catch (Exception $e) {
            error_log("Erreur lors de la gestion des données spécifiques pour {$realType}: " . $e->getMessage());
            // Ne pas faire échouer toute la création pour ces erreurs secondaires
        }
    }

// 6. Modifier la méthode getFormUrl() pour gérer le type 'generic'
    private function getFormUrl(string $type): string
    {
        return match($type) {
            'etudiant' => '/admin/utilisateurs/etudiant/form',
            'enseignant' => '/admin/utilisateurs/enseignant/form',
            'personnel' => '/admin/utilisateurs/personnel/form',
            'generic' => '/admin/utilisateurs/form', // ✅ AJOUTÉ
            default => '/admin/utilisateurs/form'
        };
    }

// 7. ✅ NOUVELLE MÉTHODE : Debug et logging pour le formulaire générique
    private function debugGenericForm(array $data, string $realType): void
    {
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            error_log("=== DEBUG FORMULAIRE GÉNÉRIQUE ===");
            error_log("Type original: generic");
            error_log("Type réel déterminé: " . $realType);
            error_log("id_type_utilisateur: " . ($data['id_type_utilisateur'] ?? 'NON DÉFINI'));
            error_log("Données POST: " . json_encode($data, JSON_PRETTY_PRINT));
            error_log("================================");
        }
    }
}