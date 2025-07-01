<?php
// src/Backend/Controller/Administration/UtilisateurController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Utilisateur\ServiceUtilisateurInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Util\FormValidator;
use App\Config\Container;

class UtilisateurController extends BaseController
{
    private ServiceUtilisateurInterface $serviceUtilisateur;
    private FormValidator $validator;

    public function __construct(
        Container $container,
        ServiceSecuriteInterface $serviceSecurite,
        ServiceUtilisateurInterface $serviceUtilisateur,
        FormValidator $validator
    ) {
        parent::__construct($container, $serviceSecurite);
        $this->serviceUtilisateur = $serviceUtilisateur;
        $this->validator = $validator;
    }

    /**
     * Affiche la vue principale de gestion des utilisateurs avec filtres et pagination.
     */
    public function listUsers(): void
    {
        $this->checkPermission('TRAIT_ADMIN_USERS_LIST');

        // Logique de filtrage et de recherche
        $filtres = [];
        if (!empty($_GET['q'])) $filtres['search'] = $_GET['q'];
        if (!empty($_GET['groupe'])) $filtres['id_groupe_utilisateur'] = $_GET['groupe'];
        if (!empty($_GET['statut'])) $filtres['statut_compte'] = $_GET['statut'];

        $utilisateurs = $this->serviceUtilisateur->listerUtilisateursComplets($filtres);

        $this->render('Administration/gestion_utilisateurs.php', [
            'title' => 'Gestion des Utilisateurs',
            'utilisateurs' => $utilisateurs,
            'filtres' => $_GET, // Pour pré-remplir les champs de filtre
            'flash' => $this->getFlashMessages()
        ]);
    }

    /**
     * Affiche le formulaire de création ou d'édition d'un utilisateur.
     */
    public function showUserForm(?string $id = null): void
    {
        $this->checkPermission($id ? 'TRAIT_ADMIN_USERS_EDIT' : 'TRAIT_ADMIN_USERS_CREATE');

        $utilisateur = null;
        if ($id) {
            $utilisateur = $this->serviceUtilisateur->lireUtilisateurComplet($id);
            if (!$utilisateur) {
                $this->setFlash('error', 'Utilisateur non trouvé.');
                $this->redirect('/admin/users');
                return;
            }
        }

        $this->render('Administration/form_utilisateur.php', [
            'title' => $id ? 'Modifier l\'Utilisateur' : 'Créer un Utilisateur',
            'utilisateur' => $utilisateur,
            'flash' => $this->getFlashMessages()
        ]);
    }

    /**
     * Traite la création ou la mise à jour d'un utilisateur.
     */
    public function saveUser(): void
    {
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->setFlash('error', 'Erreur de sécurité.');
            $this->redirect('/admin/users');
            return;
        }

        $id = $_POST['id_utilisateur'] ?? null;
        $this->checkPermission($id ? 'TRAIT_ADMIN_USERS_EDIT' : 'TRAIT_ADMIN_USERS_CREATE');

        // Logique de validation ici...

        try {
            if ($id) {
                // Logique de mise à jour
                $this->serviceUtilisateur->mettreAJourUtilisateur($id, $_POST['profil'], $_POST['compte']);
                $this->setFlash('success', 'Utilisateur mis à jour avec succès.');
            } else {
                // Logique de création
                $entiteId = $this->serviceUtilisateur->creerEntite($_POST['type_entite'], $_POST['profil']);
                $this->serviceUtilisateur->activerComptePourEntite($entiteId, $_POST['compte'], false);
                $this->setFlash('success', 'Utilisateur créé avec succès.');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }

        $this->redirect($id ? '/admin/users/edit/' . $id : '/admin/users');
    }

    /**
     * Traite la suppression physique d'un utilisateur.
     */
    public function deleteUser(string $id): void
    {
        $this->checkPermission('TRAIT_ADMIN_USERS_DELETE');
        // CSRF check via POST

        try {
            $this->serviceUtilisateur->supprimerUtilisateurEtEntite($id);
            $this->setFlash('success', 'Utilisateur supprimé définitivement.');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        $this->redirect('/admin/users');
    }

    /**
     * Traite les actions en masse sur une sélection d'utilisateurs.
     */
    public function handleBulkActions(): void
    {
        $this->checkPermission('TRAIT_ADMIN_USERS_BULK_ACTION');
        // ... Logique pour récupérer les IDs et l'action, puis boucler en appelant les services ...
        // ... Construire un rapport et l'afficher via un message flash ...
        $this->redirect('/admin/users');
    }

    /**
     * Déclenche la réinitialisation du mot de passe par l'admin.
     */
    public function resetPassword(string $id): void
    {
        $this->checkPermission('TRAIT_ADMIN_USERS_RESET_PASSWORD');
        try {
            $this->serviceUtilisateur->reinitialiserMotDePasseAdmin($id);
            $this->setFlash('success', 'Un nouveau mot de passe a été généré et envoyé à l\'utilisateur.');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        $this->redirect('/admin/users/edit/' . $id);
    }

    /**
     * Démarre une session d'impersonation.
     */
    public function impersonate(string $id): void
    {
        $this->checkPermission('TRAIT_ADMIN_USERS_IMPERSONATE');
        $adminId = $this->serviceSecurite->getUtilisateurConnecte()['numero_utilisateur'];

        if ($this->serviceSecurite->demarrerImpersonation($adminId, $id)) {
            $this->redirect('/dashboard'); // Redirige vers le dashboard de l'utilisateur cible
        } else {
            $this->setFlash('error', 'Impossible de démarrer l\'impersonation.');
            $this->redirect('/admin/users');
        }
    }

    /**
     * Arrête la session d'impersonation en cours.
     */
    public function stopImpersonating(): void
    {
        if ($this->serviceSecurite->arreterImpersonation()) {
            $this->redirect('/admin/dashboard');
        } else {
            $this->redirect('/');
        }
    }

    /**
     * Affiche l'interface d'importation en masse.
     */
    public function showImportForm(): void
    {
        $this->checkPermission('TRAIT_ADMIN_USERS_IMPORT');
        $this->render('Administration/import_utilisateurs.php', [
            'title' => 'Importer des Utilisateurs'
        ]);
    }

    /**
     * Traite le fichier importé.
     */
    public function handleImport(): void
    {
        $this->checkPermission('TRAIT_ADMIN_USERS_IMPORT');
        // ... Logique d'upload, de validation du fichier et appel à $this->serviceUtilisateur->importerEtudiantsDepuisFichier() ...
        $this->redirect('/admin/users');
    }
}