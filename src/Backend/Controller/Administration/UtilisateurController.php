<?php
namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions; // Pour récupérer les types et groupes
use App\Backend\Service\GestionAcademique\ServiceGestionAcademique;
use App\Backend\Service\ConfigurationSysteme\ServiceConfigurationSysteme; // Pour les années académiques
use App\Backend\Util\FormValidator;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ValidationException;
use App\Backend\Exception\MotDePasseInvalideException;

class UtilisateurController extends BaseController
{
    protected ServiceAuthentication $authService;
    protected ServicePermissions $permissionService;
    protected ServiceGestionAcademique $gestionAcadService;
    protected ServiceConfigurationSysteme $configService;

    public function __construct(
        ServiceAuthentication       $authService,
        ServicePermissions          $permissionService,
        FormValidator               $validator,
        ServiceGestionAcademique    $gestionAcadService, // Pour les niveaux d'étude, grades, fonctions, spécialités
        ServiceConfigurationSysteme $configService // Pour les années académiques
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->authService = $authService; // Réassignation pour un accès direct si besoin, car déjà dans parent
        $this->permissionService = $permissionService; // Réassignation
        $this->gestionAcadService = $gestionAcadService; // Service pour la gestion académique
        $this->configService = $configService; // Service pour la configuration système
    }

    /**
     * Affiche la liste générale des utilisateurs (tous types).
     */
    public function index(): void
    {
        $this->requirePermission('TRAIT_ADMIN_GERER_UTILISATEURS_LISTER'); // Permission de lister les utilisateurs

        try {
            $page = (int) $this->getRequestData('page', 1);
            $limit = 20;
            $criteres = []; // Ajouter des filtres si besoin (ex: par type d'utilisateur)

            $utilisateurs = $this->authService->listerUtilisateursAvecProfils($criteres, $page, $limit);
            // Vous pouvez aussi récupérer le total pour la pagination
            // $totalUtilisateurs = $this->authService->countUtilisateurs([]); // Méthode à créer dans ServiceAuthentication

            $data = [
                'page_title' => 'Gestion des Utilisateurs',
                'utilisateurs' => $utilisateurs,
                'current_page' => $page,
                'items_per_page' => $limit,
                // 'total_items' => $totalUtilisateurs,
            ];
            $this->render('Administration/Utilisateurs/liste_utilisateurs', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement des utilisateurs: " . $e->getMessage());
            $this->redirect('/dashboard/admin');
        }
    }

    /**
     * Affiche le formulaire de création d'utilisateur ou la traite.
     * @param string|null $type Le type d'utilisateur à créer (ex: 'etudiant', 'enseignant', 'personnel').
     */
    public function create(string $type = null): void
    {
        $this->requirePermission('TRAIT_ADMIN_GERER_UTILISATEURS_CREER'); // Permission de créer un utilisateur

        if ($this->isPostRequest()) {
            $this->handleCreate($type);
        } else {
            $this->showCreateForm($type);
        }
    }

    /**
     * Affiche le formulaire de création d'utilisateur en fonction du type.
     * @param string|null $type
     */
    private function showCreateForm(?string $type): void
    {
        $data = [
            'page_title' => 'Créer un Nouvel Utilisateur',
            'type' => $type,
            'form_action' => "/dashboard/admin/utilisateurs/create/{$type}"
        ];

        try {
            // Charger les listes de référence pour les formulaires (groupes, types, niveaux d'accès, grades, fonctions, spécialités)
            $data['types_utilisateur_ref'] = $this->permissionService->listerTypesUtilisateur();
            $data['groupes_utilisateur_ref'] = $this->permissionService->listerGroupesUtilisateur();
            $data['niveaux_acces_ref'] = $this->permissionService->listerNiveauxAcces();
            // Spécifiques aux profils
            $data['grades_ref'] = $this->authService->getEnseignantModel()->trouverTout(); // Example, you might need a service for this
            $data['fonctions_ref'] = $this->authService->getEnseignantModel()->trouverTout(); // Example
            $data['specialites_ref'] = $this->authService->getEnseignantModel()->trouverTout(); // Example

            $view = 'Administration/Utilisateurs/form_utilisateur_generic'; // Vue générique pour les 3 types
            if ($type === 'etudiant') {
                $view = 'Administration/Utilisateurs/form_etudiant';
                $data['niveaux_etude_ref'] = $this->configService->listerNiveauxEtude(); // A ajouter
                $data['annees_academiques_ref'] = $this->configService->listerAnneesAcademiques();
            } elseif ($type === 'enseignant') {
                $view = 'Administration/Utilisateurs/form_enseignant';
                $data['grades_ref'] = $this->configService->listerGrades(); // A ajouter
                $data['fonctions_ref'] = $this->configService->listerFonctions(); // A ajouter
                $data['specialites_ref'] = $this->configService->listerSpecialites(); // A ajouter
            } elseif ($type === 'personnel') {
                $view = 'Administration/Utilisateurs/form_personnel';
                // Pas de références spécifiques ici pour l'instant
            } elseif ($type === 'admin') {
                $data['form_action'] = "/dashboard/admin/utilisateurs/create/admin"; // Pas de profil spécifique pour admin
                // Pas de références spécifiques ici
            } else {
                // Si le type n'est pas spécifié, rediriger vers un choix de type ou la liste
                $this->redirect('/dashboard/admin/utilisateurs');
                return;
            }
            $this->render($view, $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erreur chargement formulaire: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/utilisateurs');
        }
    }

    /**
     * Traite la soumission du formulaire de création d'utilisateur.
     * @param string|null $type Le type d'utilisateur à créer.
     */
    private function handleCreate(?string $type): void
    {
        $utilisateurData = [
            'login_utilisateur' => $this->getRequestData('login_utilisateur'),
            'email_principal' => $this->getRequestData('email_principal'),
            'mot_de_passe' => $this->getRequestData('mot_de_passe'),
            'confirm_mot_de_passe' => $this->getRequestData('confirm_mot_de_passe'),
            'id_niveau_acces_donne' => $this->getRequestData('id_niveau_acces_donne'),
            'id_groupe_utilisateur' => $this->getRequestData('id_groupe_utilisateur') ?? $this->getDefaultGroupIdForType($type), // Déterminer le groupe par défaut
        ];

        $profilData = [
            'nom' => $this->getRequestData('nom'),
            'prenom' => $this->getRequestData('prenom'),
            'date_naissance' => $this->getRequestData('date_naissance'),
            // ... autres champs communs ou spécifiques au profil
        ];

        // Règles de validation communes
        $rules = [
            'login_utilisateur' => 'required|string|min:3|max:100',
            'email_principal' => 'required|email|max:255',
            'mot_de_passe' => 'required|string|min:8', // La robustesse est vérifiée dans le service
            'confirm_mot_de_passe' => 'required|same:mot_de_passe',
            'id_niveau_acces_donne' => 'required|string|max:50',
            'id_groupe_utilisateur' => 'required|string|max:50',
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
        ];

        // Règles spécifiques au type de profil (à adapter selon les champs)
        if ($type === 'etudiant') {
            $rules['numero_carte_etudiant'] = 'required|string|max:50'; // Sera l'ID utilisateur
            $profilData['numero_carte_etudiant'] = $this->getRequestData('numero_carte_etudiant');
            $profilData['telephone'] = $this->getRequestData('telephone');
            $profilData['email_contact_secondaire'] = $this->getRequestData('email_contact_secondaire');
            // ... autres champs étudiant
        } elseif ($type === 'enseignant') {
            $rules['numero_enseignant'] = 'required|string|max:50'; // Sera l'ID utilisateur
            $profilData['numero_enseignant'] = $this->getRequestData('numero_enseignant');
            $profilData['telephone_professionnel'] = $this->getRequestData('telephone_professionnel');
            // ... autres champs enseignant
        } elseif ($type === 'personnel') {
            $rules['numero_personnel_administratif'] = 'required|string|max:50'; // Sera l'ID utilisateur
            $profilData['numero_personnel_administratif'] = $this->getRequestData('numero_personnel_administratif');
            // ... autres champs personnel
        } else {
            $this->setFlashMessage('error', 'Type d\'utilisateur invalide.');
            $this->redirect('/dashboard/admin/utilisateurs/create');
            return;
        }

        $this->validator->validate($utilisateurData, $rules); // Valide les données de l'utilisateur
        $this->validator->validate($profilData, $rules); // Valide les données du profil

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect("/dashboard/admin/utilisateurs/create/{$type}");
        }

        try {
            $this->authService->creerCompteUtilisateurComplet($utilisateurData, $profilData, strtoupper("TYPE_{$type}"), true); // Envoie email validation
            $this->setFlashMessage('success', 'Utilisateur créé avec succès. Un email de validation a été envoyé.');
            $this->redirect('/dashboard/admin/utilisateurs');
        } catch (DoublonException $e) {
            $this->setFlashMessage('error', 'Erreur de création: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/utilisateurs/create/{$type}");
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/utilisateurs/create/{$type}");
        } catch (MotDePasseInvalideException $e) {
            $this->setFlashMessage('error', 'Mot de passe invalide: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/utilisateurs/create/{$type}");
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/utilisateurs/create/{$type}");
        }
    }

    /**
     * Détermine l'ID du groupe par défaut en fonction du type d'utilisateur.
     * @param string $type Le type d'utilisateur ('etudiant', 'enseignant', 'personnel', 'admin').
     * @return string L'ID du groupe par défaut.
     */
    private function getDefaultGroupIdForType(string $type): string
    {
        return match($type) {
            'etudiant' => 'GRP_ETUDIANT',
            'enseignant' => 'GRP_ENSEIGNANT',
            'personnel' => 'GRP_PERS_ADMIN',
            'admin' => 'GRP_ADMIN_SYS',
            default => throw new \InvalidArgumentException("Type d'utilisateur inconnu pour le groupe par défaut.")
        };
    }


    /**
     * Affiche le formulaire de modification d'utilisateur ou la traite.
     * @param string $id L'ID de l'utilisateur à modifier.
     */
    public function edit(string $id): void
    {
        $this->requirePermission('TRAIT_ADMIN_GERER_UTILISATEURS_MODIFIER'); // Permission de modifier un utilisateur

        if ($this->isPostRequest()) {
            $this->handleEdit($id);
        } else {
            try {
                $utilisateur = $this->authService->recupererUtilisateurCompletParNumero($id);
                if (!$utilisateur) {
                    throw new ElementNonTrouveException("Utilisateur non trouvé.");
                }

                $data = [
                    'page_title' => 'Modifier Utilisateur',
                    'utilisateur' => $utilisateur,
                    'form_action' => "/dashboard/admin/utilisateurs/{$id}/edit",
                    'types_utilisateur_ref' => $this->permissionService->listerTypesUtilisateur(),
                    'groupes_utilisateur_ref' => $this->permissionService->listerGroupesUtilisateur(),
                    'niveaux_acces_ref' => $this->permissionService->listerNiveauxAcces(),
                    // Références spécifiques selon le type d'utilisateur (mêmes que pour la création)
                    'niveaux_etude_ref' => $this->configService->listerNiveauxEtude(),
                    'annees_academiques_ref' => $this->configService->listerAnneesAcademiques(),
                    'grades_ref' => $this->configService->listerGrades(),
                    'fonctions_ref' => $this->configService->listerFonctions(),
                    'specialites_ref' => $this->configService->listerSpecialites(),
                ];

                // Sélectionner la vue de formulaire appropriée
                $view = 'Administration/Utilisateurs/form_utilisateur_generic';
                if (isset($utilisateur['id_type_utilisateur'])) {
                    switch ($utilisateur['id_type_utilisateur']) {
                        case 'TYPE_ETUD': $view = 'Administration/Utilisateurs/form_etudiant'; break;
                        case 'TYPE_ENS': $view = 'Administration/Utilisateurs/form_enseignant'; break;
                        case 'TYPE_PERS_ADMIN': $view = 'Administration/Utilisateurs/form_personnel'; break;
                        // 'TYPE_ADMIN' peut utiliser le formulaire générique ou être simplifié
                    }
                }

                $this->render($view, $data);
            } catch (ElementNonTrouveException $e) {
                $this->setFlashMessage('error', $e->getMessage());
                $this->redirect('/dashboard/admin/utilisateurs');
            } catch (\Exception $e) {
                $this->setFlashMessage('error', 'Erreur: ' . $e->getMessage());
                $this->redirect('/dashboard/admin/utilisateurs');
            }
        }
    }

    /**
     * Traite la soumission du formulaire de modification d'utilisateur.
     * @param string $id L'ID de l'utilisateur.
     */
    private function handleEdit(string $id): void
    {
        // Récupérer les données de base de l'utilisateur (login, email, groupe, niveau accès)
        $utilisateurData = [
            'login_utilisateur' => $this->getRequestData('login_utilisateur'),
            'email_principal' => $this->getRequestData('email_principal'),
            'id_niveau_acces_donne' => $this->getRequestData('id_niveau_acces_donne'),
            'id_groupe_utilisateur' => $this->getRequestData('id_groupe_utilisateur'),
            'statut_compte' => $this->getRequestData('statut_compte'),
        ];

        // Récupérer les données du profil spécifique
        $profilData = [
            'nom' => $this->getRequestData('nom'),
            'prenom' => $this->getRequestData('prenom'),
            'date_naissance' => $this->getRequestData('date_naissance'),
            // ... autres champs communs ou spécifiques au profil
        ];

        // Récupérer le type d'utilisateur (nécessaire pour la MAJ du profil)
        $currentUser = $this->authService->recupererUtilisateurCompletParNumero($id);
        if (!$currentUser) {
            $this->setFlashMessage('error', 'Utilisateur à modifier non trouvé.');
            $this->redirect('/dashboard/admin/utilisateurs');
            return;
        }
        $typeProfilCode = $currentUser['id_type_utilisateur'];

        // Règles de validation (similaires à la création, mais mot de passe non requis)
        $rules = [
            'login_utilisateur' => 'required|string|min:3|max:100',
            'email_principal' => 'required|email|max:255',
            'id_niveau_acces_donne' => 'required|string|max:50',
            'id_groupe_utilisateur' => 'required|string|max:50',
            'statut_compte' => 'required|string|in:actif,inactif,bloque,en_attente_validation,archive',
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
        ];
        // Règles spécifiques au type de profil (à adapter)
        if ($typeProfilCode === 'TYPE_ETUD') {
            $profilData['telephone'] = $this->getRequestData('telephone');
            $profilData['email_contact_secondaire'] = $this->getRequestData('email_contact_secondaire');
        } elseif ($typeProfilCode === 'TYPE_ENS') {
            $profilData['telephone_professionnel'] = $this->getRequestData('telephone_professionnel');
        }
        // ...

        $this->validator->validate($utilisateurData, $rules);
        $this->validator->validate($profilData, $rules); // Valide le profil

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect("/dashboard/admin/utilisateurs/{$id}/edit");
        }

        try {
            // Mettre à jour les infos de l'utilisateur de base
            $this->authService->mettreAJourCompteUtilisateurParAdmin($id, $utilisateurData);
            // Mettre à jour les infos du profil spécifique
            $this->authService->mettreAJourProfilUtilisateur($id, $typeProfilCode, $profilData);

            $this->setFlashMessage('success', 'Utilisateur modifié avec succès.');
            $this->redirect('/dashboard/admin/utilisateurs');
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/dashboard/admin/utilisateurs');
        } catch (DoublonException $e) {
            $this->setFlashMessage('error', 'Erreur de modification: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/utilisateurs/{$id}/edit");
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/utilisateurs/{$id}/edit");
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/utilisateurs/{$id}/edit");
        }
    }

    /**
     * Supprime un utilisateur.
     * @param string $id L'ID de l'utilisateur à supprimer.
     */
    public function delete(string $id): void
    {
        $this->requirePermission('TRAIT_ADMIN_GERER_UTILISATEURS_SUPPRIMER'); // Permission de supprimer un utilisateur

        try {
            if ($id === $this->getCurrentUser()['numero_utilisateur']) {
                throw new OperationImpossibleException("Vous ne pouvez pas supprimer votre propre compte.");
            }

            $this->authService->supprimerUtilisateur($id);
            $this->setFlashMessage('success', 'Utilisateur supprimé avec succès.');
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Impossible de supprimer cet utilisateur : ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect('/dashboard/admin/utilisateurs');
    }

    /**
     * Gère le changement de statut d'un compte utilisateur.
     * @param string $id L'ID de l'utilisateur.
     */
    public function changeStatus(string $id): void
    {
        $this->requirePermission('TRAIT_ADMIN_GERER_UTILISATEURS_CHANGER_STATUT'); // Permission de changer le statut

        if (!$this->isPostRequest()) {
            $this->redirect('/dashboard/admin/utilisateurs'); // Rediriger si pas POST
        }

        $newStatus = $this->getRequestData('new_status');
        $reason = $this->getRequestData('reason'); // Optionnel, pour le log

        $rules = [
            'new_status' => 'required|string|in:actif,inactif,bloque,en_attente_validation,archive',
        ];
        $this->validator->validate(['new_status' => $newStatus], $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect('/dashboard/admin/utilisateurs');
        }

        try {
            if ($id === $this->getCurrentUser()['numero_utilisateur'] && $newStatus !== 'actif') { // Si l'admin tente de se désactiver/bloquer
                throw new OperationImpossibleException("Vous ne pouvez pas changer le statut de votre propre compte en '{$newStatus}'.");
            }

            $this->authService->changerStatutDuCompte($id, $newStatus, $reason);
            $this->setFlashMessage('success', 'Statut du compte mis à jour avec succès.');
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Impossible de changer le statut du compte : ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect('/dashboard/admin/utilisateurs');
    }

    /**
     * Gère la réinitialisation du mot de passe d'un utilisateur par l'administrateur.
     * @param string $id L'ID de l'utilisateur.
     */
    public function resetPassword(string $id): void
    {
        $this->requirePermission('TRAIT_ADMIN_GERER_UTILISATEURS_RESET_MDP'); // Permission de réinitialiser le mot de passe

        if (!$this->isPostRequest()) {
            $this->redirect('/dashboard/admin/utilisateurs');
        }

        $newPassword = $this->getRequestData('new_password');
        $confirmPassword = $this->getRequestData('confirm_password');

        $rules = [
            'new_password' => 'required|string|min:8',
            'confirm_password' => 'required|same:new_password',
        ];
        $this->validator->validate(['new_password' => $newPassword, 'confirm_password' => $confirmPassword], $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect('/dashboard/admin/utilisateurs');
        }

        try {
            $this->authService->modifierMotDePasse($id, $newPassword, null, true); // isAdminReset = true
            $this->setFlashMessage('success', 'Mot de passe réinitialisé avec succès.');
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
        } catch (MotDePasseInvalideException $e) {
            $this->setFlashMessage('error', 'Mot de passe invalide: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect('/dashboard/admin/utilisateurs');
    }

    /**
     * Gère l'importation en masse d'étudiants via un fichier.
     */
    public function importStudents(): void
    {
        $this->requirePermission('TRAIT_ADMIN_GERER_UTILISATEURS_IMPORTER'); // Permission d'importer des utilisateurs

        if ($this->isPostRequest()) {
            $this->handleImportStudents();
        } else {
            $data = [
                'page_title' => 'Importer des Étudiants',
                'form_action' => '/dashboard/admin/utilisateurs/import-students'
            ];
            $this->render('Administration/Utilisateurs/import_etudiants_form', $data); // Créer cette vue
        }
    }

    /**
     * Traite le fichier d'importation d'étudiants.
     */
    private function handleImportStudents(): void
    {
        if (!isset($_FILES['student_file']) || $_FILES['student_file']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlashMessage('error', 'Aucun fichier uploadé ou erreur lors de l\'upload.');
            $this->redirect('/dashboard/admin/utilisateurs/import-students');
            return;
        }

        $file = $_FILES['student_file']['tmp_name'];
        $mimeType = mime_content_type($file);

        if (!in_array($mimeType, ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])) {
            $this->setFlashMessage('error', 'Format de fichier non supporté. Veuillez uploader un fichier CSV ou Excel.');
            $this->redirect('/dashboard/admin/utilisateurs/import-students');
            return;
        }

        $importedCount = 0;
        $errors = [];

        // Vous auriez besoin d'une bibliothèque pour lire les CSV/Excel (ex: League/Csv, PhpSpreadsheet)
        // Pour l'exemple, nous allons simuler la lecture d'un CSV simple
        if (($handle = fopen($file, 'r')) !== FALSE) {
            fgetcsv($handle); // Skip header row
            while (($dataRow = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Supposons un CSV: numero_carte_etudiant, nom, prenom, email_principal, login, mot_de_passe
                // Adapter les indices de tableau à votre structure CSV
                $studentData = [
                    'numero_carte_etudiant' => $dataRow[0] ?? '',
                    'nom' => $dataRow[1] ?? '',
                    'prenom' => $dataRow[2] ?? '',
                    'email_principal' => $dataRow[3] ?? '',
                    'login_utilisateur' => $dataRow[4] ?? '',
                    'mot_de_passe' => $dataRow[5] ?? '',
                ];

                // Données de base de l'utilisateur
                $utilisateurData = [
                    'login_utilisateur' => $studentData['login_utilisateur'],
                    'email_principal' => $studentData['email_principal'],
                    'mot_de_passe' => $studentData['mot_de_passe'],
                    'id_niveau_acces_donne' => 'ACCES_RESTREINT', // Valeur par défaut
                    'id_groupe_utilisateur' => 'GRP_ETUDIANT', // Groupe par défaut pour étudiants
                ];
                // Données de profil étudiant
                $profilData = [
                    'nom' => $studentData['nom'],
                    'prenom' => $studentData['prenom'],
                    'numero_carte_etudiant' => $studentData['numero_carte_etudiant'], // C'est l'ID utilisateur
                ];

                try {
                    // Valider les données de chaque ligne avant d'appeler le service
                    // C'est une simplification, une validation plus robuste serait nécessaire ici.
                    if (empty($utilisateurData['login_utilisateur']) || empty($utilisateurData['email_principal']) || empty($profilData['nom'])) {
                        throw new ValidationException("Données incomplètes pour l'étudiant " . $studentData['numero_carte_etudiant']);
                    }

                    $this->authService->creerCompteUtilisateurComplet($utilisateurData, $profilData, 'TYPE_ETUD', false); // Ne pas envoyer d'email de validation ici
                    $importedCount++;
                } catch (ValidationException $e) {
                    $errors[] = "Ligne " . ($importedCount + count($errors) + 1) . ": " . $e->getMessage();
                } catch (DoublonException $e) {
                    $errors[] = "Ligne " . ($importedCount + count($errors) + 1) . " (ID: {$studentData['numero_carte_etudiant']}): " . $e->getMessage();
                } catch (\Exception $e) {
                    $errors[] = "Ligne " . ($importedCount + count($errors) + 1) . " (ID: {$studentData['numero_carte_etudiant']}): Erreur inattendue - " . $e->getMessage();
                }
            }
            fclose($handle);
        }

        if (empty($errors)) {
            $this->setFlashMessage('success', "Importation réussie : {$importedCount} étudiants importés.");
        } else {
            $this->setFlashMessage('warning', "Importation terminée avec des erreurs. {$importedCount} étudiants importés, " . count($errors) . " échecs.");
            $_SESSION['import_errors'] = $errors; // Stocker les erreurs pour les afficher dans la vue
            $this->redirect('/dashboard/admin/utilisateurs/import-students');
            return;
        }
        $this->redirect('/dashboard/admin/utilisateurs');
    }

}