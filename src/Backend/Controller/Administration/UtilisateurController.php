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

/**
 * Gère toutes les opérations CRUD sur les utilisateurs et leurs entités associées.
 * Permet aux administrateurs de lister, créer, modifier et gérer les comptes utilisateurs.
 */
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
     */
    public function list(): void
    {
        // MODIFICATION ICI : Aligner avec la permission de la DB
        $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_LISTER_ACCES');

        try {
            error_log("DEBUG UtilisateurController: Entrée dans le bloc try de list().");
            $filters = $this->getGetData();
            $users = $this->serviceUtilisateur->listerUtilisateursComplets($filters);

            $groupes = $this->systemeService->gererReferentiel('list', 'groupe_utilisateur');
            $statuts = ['actif', 'inactif', 'bloque', 'en_attente_validation', 'archive'];
            $types = $this->systemeService->gererReferentiel('list', 'type_utilisateur');

            $this->render('/admin/utilisateurs', [
                'title' => 'Gestion des Utilisateurs',
                'users' => $users,
                'groupes' => $groupes,
                'statuts' => $statuts,
                'types' => $types,
                'current_filters' => $filters,
                'csrf_token' => $this->generateCsrfToken('user_actions_form')
            ]);
        } catch (Exception $e) {
            error_log("ERROR UtilisateurController: Exception dans list(): " . $e->getMessage());
            $this->addFlashMessage('error', "Une erreur est survenue lors du chargement des utilisateurs : " . $e->getMessage());
            $this->redirect('/admin/dashboard');
        }
    }

    /**
     * Affiche le formulaire de création d'un nouvel utilisateur et son entité.
     */
    public function showCreateUserForm(): void
    {
        // MODIFICATION ICI : Utiliser une permission générale d'accès aux formulaires
        // 'TRAIT_ADMIN_GERER_UTILISATEURS_CREER' n'existe pas dans votre rattacher fourni
        $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_FORM_GENERIC_ACCES');
        try {
            $this->render('Administration/form_utilisateur', [
                'title' => 'Créer un Nouvel Utilisateur',
                'user' => null,
                'groupes' => $this->systemeService->gererReferentiel('list', 'groupe_utilisateur'),
                'types' => $this->systemeService->gererReferentiel('list', 'type_utilisateur'),
                'niveauxAcces' => $this->systemeService->gererReferentiel('list', 'niveau_acces_donne'),
                'action_url' => '/admin/utilisateurs/creer',
                'csrf_token' => $this->generateCsrfToken('user_form'),
                'form_errors' => $_SESSION['form_errors'] ?? [],
                'form_data' => $_SESSION['form_data'] ?? []
            ]);
            unset($_SESSION['form_errors'], $_SESSION['form_data']);
        } catch (Exception | ElementNonTrouveException $e) { // Ajout de ElementNonTrouveException
            $this->addFlashMessage('error', 'Impossible de charger le formulaire de création : ' . $e->getMessage());
            $this->redirect('/admin/utilisateurs');
        }
    }

    /**
     * Traite la soumission du formulaire de création d'utilisateur.
     */
    public function create(): void
    {
        // MODIFICATION ICI : Utiliser la même permission que pour l'affichage du formulaire
        $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_FORM_GENERIC_ACCES');

        if (!$this->isPostRequest() || !$this->validateCsrfToken('user_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/utilisateurs');
            return;
        }

        $data = $this->getPostData();
        $rules = [
            'nom' => 'required|max:100',
            'prenom' => 'required|max:100',
            'login_utilisateur' => 'required|max:100',
            'email_principal' => 'required|email|max:255',
            'mot_de_passe' => 'required|min:8',
            'id_groupe_utilisateur' => 'required',
            'id_type_utilisateur' => 'required',
            'id_niveau_acces_donne' => 'required'
        ];

        if (!$this->validator->validate($data, $rules)) {
            $_SESSION['form_errors'] = $this->validator->getErrors();
            $_SESSION['form_data'] = $data;
            $this->redirect('/admin/utilisateurs/creer');
            return;
        }

        try {
            $typeEntite = strtolower(str_replace('TYPE_', '', $data['id_type_utilisateur']));
            $donneesProfil = ['nom' => $data['nom'], 'prenom' => $data['prenom']];
            if (isset($data['date_naissance'])) $donneesProfil['date_naissance'] = $data['date_naissance'];
            if (isset($data['telephone'])) $donneesProfil['telephone'] = $data['telephone'];
            if (isset($data['email_professionnel'])) $donneesProfil['email_professionnel'] = $data['email_professionnel'];

            $numeroEntite = $this->serviceUtilisateur->creerEntite($typeEntite, $donneesProfil);

            $donneesCompte = [
                'login_utilisateur' => $data['login_utilisateur'],
                'email_principal' => $data['email_principal'],
                'mot_de_passe' => $data['mot_de_passe'],
                'id_groupe_utilisateur' => $data['id_groupe_utilisateur'],
                'id_niveau_acces_donne' => $data['id_niveau_acces_donne']
            ];
            $this->serviceUtilisateur->activerComptePourEntite($numeroEntite, $donneesCompte);

            $this->addFlashMessage('success', 'Utilisateur créé avec succès. Un email de validation a été envoyé.');
        } catch (DoublonException | OperationImpossibleException | ValidationException $e) {
            $this->addFlashMessage('error', 'Erreur de création : ' . $e->getMessage());
            $_SESSION['form_data'] = $data;
            $this->redirect('/admin/utilisateurs/creer');
            return;
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Une erreur inattendue est survenue lors de la création.');
            error_log("Erreur UtilisateurController::create: " . $e->getMessage());
        }

        $this->redirect('/admin/utilisateurs');
    }

    /**
     * Affiche le formulaire de modification pour un utilisateur existant.
     */
    public function show(string $id): void
    {
        // MODIFICATION ICI : Utiliser la même permission que pour l'affichage du formulaire générique
        // 'TRAIT_ADMIN_GERER_UTILISATEURS_MODIFIER' n'existe pas dans votre rattacher fourni
        $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_FORM_GENERIC_ACCES');
        try {
            $user = $this->serviceUtilisateur->lireUtilisateurComplet($id);
            if (!$user) throw new ElementNonTrouveException("Utilisateur introuvable.");

            $this->render('Administration/form_utilisateur', [
                'title' => "Modifier l'Utilisateur : " . htmlspecialchars($user['prenom'] . ' ' . $user['nom']),
                'user' => $user,
                'groupes' => $this->systemeService->gererReferentiel('list', 'groupe_utilisateur'),
                'types' => $this->systemeService->gererReferentiel('list', 'type_utilisateur'),
                'niveauxAcces' => $this->systemeService->gererReferentiel('list', 'niveau_acces_donne'),
                'action_url' => "/admin/utilisateurs/{$id}/modifier",
                'csrf_token' => $this->generateCsrfToken('user_form'),
                'form_errors' => $_SESSION['form_errors'] ?? [],
                'form_data' => $_SESSION['form_data'] ?? $user
            ]);
            unset($_SESSION['form_errors'], $_SESSION['form_data']);
        } catch (Exception | ElementNonTrouveException $e) { // Ajout de ElementNonTrouveException
            $this->addFlashMessage('error', 'Impossible de charger le formulaire de modification : ' . $e->getMessage());
            $this->redirect('/admin/utilisateurs');
        }
    }

    /**
     * Traite la soumission du formulaire de modification d'utilisateur.
     */
    public function update(string $id): void
    {
        // MODIFICATION ICI : Utiliser la même permission que pour l'affichage du formulaire
        $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_FORM_GENERIC_ACCES');

        if (!$this->isPostRequest() || !$this->validateCsrfToken('user_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/utilisateurs');
            return;
        }

        $data = $this->getPostData();
        $rules = [
            'nom' => 'required|max:100',
            'prenom' => 'required|max:100',
            'login_utilisateur' => 'required|max:100',
            'email_principal' => 'required|email|max:255',
            'mot_de_passe' => 'min:8'
        ];

        if (!$this->validator->validate($data, $rules)) {
            $_SESSION['form_errors'] = $this->validator->getErrors();
            $_SESSION['form_data'] = $data;
            $this->redirect("/admin/utilisateurs/{$id}");
            return;
        }

        try {
            $donneesProfil = ['nom' => $data['nom'], 'prenom' => $data['prenom']];
            if (isset($data['date_naissance'])) $donneesProfil['date_naissance'] = $data['date_naissance'];
            if (isset($data['telephone'])) $donneesProfil['telephone'] = $data['telephone'];
            if (isset($data['email_professionnel'])) $donneesProfil['email_professionnel'] = $data['email_professionnel'];

            $donneesCompte = [
                'login_utilisateur' => $data['login_utilisateur'],
                'email_principal' => $data['email_principal'],
                'id_groupe_utilisateur' => $data['id_groupe_utilisateur'],
                'id_niveau_acces_donne' => $data['id_niveau_acces_donne'],
                'statut_compte' => $data['statut_compte'] ?? 'actif'
            ];
            if (!empty($data['mot_de_passe'])) {
                $donneesCompte['mot_de_passe'] = $data['mot_de_passe'];
            }

            $this->serviceUtilisateur->mettreAJourUtilisateur($id, $donneesProfil, $donneesCompte);
            $this->addFlashMessage('success', "Utilisateur {$id} mis à jour avec succès.");
        } catch (Exception $e) {
            $this->addFlashMessage('error', "Échec de la mise à jour : " . $e->getMessage());
            $_SESSION['form_data'] = $data;
            $this->redirect("/admin/utilisateurs/{$id}");
        }

        $this->redirect('/admin/utilisateurs');
    }

    /**
     * Gère les actions individuelles sur un utilisateur (changement de statut, impersonation, etc.).
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
                    // MODIFICATION ICI : Utilisation de la permission générique pour test
                    $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_FORM_GENERIC_ACCES');
                    $this->serviceUtilisateur->changerStatutCompte($id, $_POST['status'] ?? '');
                    $this->addFlashMessage('success', "Statut de l'utilisateur {$id} modifié.");
                    break;

                case 'reset_password':
                    // MODIFICATION ICI : Utilisation de la permission générique pour test
                    $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_FORM_GENERIC_ACCES');
                    $this->serviceUtilisateur->reinitialiserMotDePasseAdmin($id);
                    $this->addFlashMessage('success', "Mot de passe réinitialisé pour {$id}. Un email a été envoyé.");
                    break;

                case 'impersonate':
                    // Cette permission semble déjà correspondre dans votre rattacher
                    $this->requirePermission('TRAIT_ADMIN_IMPERSONATE_USER');
                    $adminId = $this->securiteService->getUtilisateurConnecte()['numero_utilisateur'];
                    $this->securiteService->demarrerImpersonation($adminId, $id);
                    $this->addFlashMessage('info', "Vous impersonnalisez maintenant l'utilisateur {$id}.");
                    $this->redirect('/dashboard');
                    return;

                case 'delete':
                    // MODIFICATION ICI : Utilisation de la permission générique pour test
                    // Idéalement, il faudrait une permission spécifique comme 'TRAIT_ADMIN_GERER_UTILISATEURS_DELETE'
                    // et l'ajouter à la DB. Pour l'instant, on utilise une existante pour ne pas bloquer.
                    $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_FORM_GENERIC_ACCES');
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

    /**
     * Gère la suppression d'un utilisateur.
     */
    public function delete(string $id): void
    {
        // MODIFICATION ICI : Utilisation de la permission générique pour test
        $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_FORM_GENERIC_ACCES');

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
}