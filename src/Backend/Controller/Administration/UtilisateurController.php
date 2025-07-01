<?php
// src/Backend/Controller/Administration/UtilisateurController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Utilisateur\ServiceUtilisateurInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;
use App\Backend\Exception\ValidationException;

class UtilisateurController extends BaseController
{
    private ServiceUtilisateurInterface $serviceUtilisateur;

    public function __construct(
        ServiceSecuriteInterface $serviceSecurite,
        ServiceSupervisionInterface $serviceSupervision,
        FormValidator $formValidator,
        ServiceUtilisateurInterface $serviceUtilisateur
    ) {
        parent::__construct($serviceSecurite, $serviceSupervision, $formValidator);
        $this->serviceUtilisateur = $serviceUtilisateur;
    }

    /**
     * Affiche la liste de tous les utilisateurs.
     */
    public function listUsers(): void
    {
        $this->checkPermission('ADMIN_USERS_LIST');
        $users = $this->serviceUtilisateur->listerUtilisateursComplets();
        $this->render('Administration/gestion_utilisateurs.php', [
            'title' => 'Gestion des Utilisateurs',
            'users' => $users
        ]);
    }

    /**
     * Affiche le formulaire de création ou d'édition d'un utilisateur.
     */
    public function showUserForm(?string $id = null): void
    {
        $this->checkPermission('ADMIN_USERS_EDIT');
        $user = null;
        if ($id) {
            $user = $this->serviceUtilisateur->listerUtilisateursComplets(['numero_utilisateur' => $id])[0] ?? null;
            if (!$user) {
                $this->render('errors/404.php');
                return;
            }
        }
        $this->render('Administration/form_utilisateur.php', [
            'title' => $id ? 'Modifier l\'Utilisateur' : 'Créer un Utilisateur',
            'user' => $user
        ]);
    }

    /**
     * Traite la création d'un nouvel utilisateur.
     */
    public function createUser(): void
    {
        $this->checkPermission('ADMIN_USERS_CREATE');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['success' => false, 'message' => 'Requête invalide.'], 403);
            return;
        }

        $rules = [
            'type_entite' => 'required|in:etudiant,enseignant,personnel',
            'nom' => 'required|max:100',
            'prenom' => 'required|max:100',
            'login_utilisateur' => 'required|max:100',
            'email_principal' => 'required|email|max:255',
            'mot_de_passe' => 'required|min:8',
            'id_groupe_utilisateur' => 'required'
        ];

        if (!$this->formValidator->validate($_POST, $rules)) {
            $this->jsonResponse(['success' => false, 'errors' => $this->formValidator->getErrors()], 422);
            return;
        }

        try {
            $this->db->beginTransaction();
            $numeroEntite = $this->serviceUtilisateur->creerEntite($_POST['type_entite'], [
                'nom' => $_POST['nom'],
                'prenom' => $_POST['prenom']
            ]);

            $this->serviceUtilisateur->activerComptePourEntite($numeroEntite, [
                'login_utilisateur' => $_POST['login_utilisateur'],
                'email_principal' => $_POST['email_principal'],
                'mot_de_passe' => $_POST['mot_de_passe'],
                'id_groupe_utilisateur' => $_POST['id_groupe_utilisateur'],
                'id_niveau_acces_donne' => $_POST['id_niveau_acces_donne'] ?? 'ACCES_PERSONNEL'
            ], false); // Ne pas envoyer d'email pour une création admin

            $this->serviceUtilisateur->changerStatutCompte($numeroEntite, 'actif');
            $this->db->commit();

            $this->jsonResponse(['success' => true, 'message' => 'Utilisateur créé avec succès.', 'redirect' => '/admin/users']);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Traite la mise à jour d'un utilisateur existant.
     */
    public function updateUser(string $id): void
    {
        $this->checkPermission('ADMIN_USERS_EDIT');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['success' => false, 'message' => 'Requête invalide.'], 403);
            return;
        }

        $rules = [
            'nom' => 'required|max:100',
            'prenom' => 'required|max:100',
            'login_utilisateur' => 'required|max:100',
            'email_principal' => 'required|email|max:255',
            'id_groupe_utilisateur' => 'required',
            'statut_compte' => 'required|in:actif,inactif,bloque,archive'
        ];

        if (!$this->formValidator->validate($_POST, $rules)) {
            $this->jsonResponse(['success' => false, 'errors' => $this->formValidator->getErrors()], 422);
            return;
        }

        try {
            $donneesProfil = ['nom' => $_POST['nom'], 'prenom' => $_POST['prenom']];
            $donneesCompte = [
                'login_utilisateur' => $_POST['login_utilisateur'],
                'email_principal' => $_POST['email_principal'],
                'id_groupe_utilisateur' => $_POST['id_groupe_utilisateur'],
                'statut_compte' => $_POST['statut_compte']
            ];

            $this->serviceUtilisateur->mettreAJourUtilisateur($id, $donneesProfil, $donneesCompte);
            $this->jsonResponse(['success' => true, 'message' => 'Utilisateur mis à jour avec succès.', 'redirect' => '/admin/users']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Traite l'importation en masse d'utilisateurs depuis un fichier.
     */
    public function importUsers(): void
    {
        $this->checkPermission('ADMIN_USERS_IMPORT');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Requête invalide ou expirée.';
            $this->redirect('/admin/users');
            return;
        }

        if (empty($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Aucun fichier fourni ou erreur lors du téléversement.';
            $this->redirect('/admin/users');
            return;
        }

        try {
            $filePath = $_FILES['import_file']['tmp_name'];
            $mapping = json_decode($_POST['mapping'], true); // Le mapping est envoyé en JSON

            $rapport = $this->serviceUtilisateur->importerEtudiantsDepuisFichier($filePath, $mapping);

            $_SESSION['success'] = "Importation terminée : {$rapport['succes']} succès, {$rapport['echecs']} échecs.";
            if (!empty($rapport['erreurs'])) {
                $_SESSION['import_errors'] = $rapport['erreurs'];
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = "Erreur lors de l'importation : " . $e->getMessage();
        }

        $this->redirect('/admin/users');
    }
}
