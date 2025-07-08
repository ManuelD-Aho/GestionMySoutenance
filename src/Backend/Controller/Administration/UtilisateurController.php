<?php
// Emplacement: src/Backend/Controller/Administration/UtilisateurController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Utilisateur\ServiceUtilisateurInterface;
use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;
use Exception;

class UtilisateurController extends BaseController
{
    private ServiceUtilisateurInterface $serviceUtilisateur;
    private ServiceSystemeInterface $systemeService;

    public function __construct(
        ServiceUtilisateurInterface $serviceUtilisateur,
        ServiceSystemeInterface $systemeService,
        FormValidator $validator,
        ServiceSecuriteInterface $securiteService,
        ServiceSupervisionInterface $supervisionService
    ) {
        parent::__construct($securiteService, $supervisionService, $validator);
        $this->serviceUtilisateur = $serviceUtilisateur;
        $this->systemeService = $systemeService;
    }

    public function list(): void
    {
        $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_LISTER_ACCES');
        try {
            $filters = $this->getGetData();
            $this->render('Administration/utilisateurs/index', [
                'title' => 'Gestion des Utilisateurs',
                'users' => $this->serviceUtilisateur->listerUtilisateursComplets($filters),
                'groupes' => $this->systemeService->gererReferentiel('list', 'groupe_utilisateur'),
                'statuts' => ['actif', 'inactif', 'bloque', 'en_attente_validation', 'archive'],
                'types' => $this->systemeService->gererReferentiel('list', 'type_utilisateur'),
                'current_filters' => $filters,
                'csrf_token' => $this->generateCsrfToken('user_actions_form')
            ]);
        } catch (Exception $e) {
            $this->addFlashMessage('error', "Erreur: " . $e->getMessage());
            $this->redirect('/admin/dashboard');
        }
    }

    public function showForm(string $id = null): void
    {
        $permission = $id ? 'TRAIT_ADMIN_UTILISATEURS_FORM_GENERIC_ACCES' : 'TRAIT_ADMIN_UTILISATEURS_FORM_GENERIC_ACCES';
        $this->requirePermission($permission);

        try {
            $user = $id ? $this->serviceUtilisateur->lireUtilisateurComplet($id) : null;
            $this->render('Administration/utilisateurs/form', [
                'title' => $id ? "Modifier l'Utilisateur" : 'Créer un Utilisateur',
                'user' => $user,
                'groupes' => $this->systemeService->gererReferentiel('list', 'groupe_utilisateur'),
                'types' => $this->systemeService->gererReferentiel('list', 'type_utilisateur'),
                'niveauxAcces' => $this->systemeService->gererReferentiel('list', 'niveau_acces_donne'),
                'action_url' => $id ? "/admin/utilisateurs/{$id}/modifier" : '/admin/utilisateurs/creer',
                'csrf_token' => $this->generateCsrfToken('user_form'),
            ]);
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect('/admin/utilisateurs');
        }
    }

    public function create(): void
    {
        $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_FORM_GENERIC_ACCES');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('user_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/utilisateurs');
            return;
        }

        try {
            $data = $this->getPostData();
            $typeEntite = strtolower(str_replace('TYPE_', '', $data['id_type_utilisateur']));
            $donneesProfil = ['nom' => $data['nom'], 'prenom' => $data['prenom']];
            $numeroEntite = $this->serviceUtilisateur->creerEntite($typeEntite, $donneesProfil);
            $this->serviceUtilisateur->activerComptePourEntite($numeroEntite, $data);
            $this->addFlashMessage('success', 'Utilisateur créé avec succès.');
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        }
        $this->redirect('/admin/utilisateurs');
    }

    public function update(string $id): void
    {
        $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_FORM_GENERIC_ACCES');
        if (!$this->isPostRequest() || !$this->validateCsrfToken('user_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/utilisateurs');
            return;
        }

        try {
            $data = $this->getPostData();
            $donneesProfil = ['nom' => $data['nom'], 'prenom' => $data['prenom']];
            $donneesCompte = $data;
            unset($donneesCompte['nom'], $donneesCompte['prenom']);
            $this->serviceUtilisateur->mettreAJourUtilisateur($id, $donneesProfil, $donneesCompte);
            $this->addFlashMessage('success', 'Utilisateur mis à jour.');
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        }
        $this->redirect('/admin/utilisateurs');
    }

    public function handleAction(string $id): void
    {
        if (!$this->isPostRequest() || !$this->validateCsrfToken('user_actions_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/admin/utilisateurs');
            return;
        }

        $action = $_POST['action'] ?? '';
        try {
            switch ($action) {
                case 'change_status':
                    $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_FORM_GENERIC_ACCES');
                    $this->serviceUtilisateur->changerStatutCompte($id, $_POST['status'] ?? '');
                    $this->addFlashMessage('success', "Statut modifié.");
                    break;
                case 'reset_password':
                    $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_FORM_GENERIC_ACCES');
                    $this->serviceUtilisateur->reinitialiserMotDePasseAdmin($id);
                    $this->addFlashMessage('success', "Mot de passe réinitialisé.");
                    break;
                case 'impersonate':
                    $this->requirePermission('TRAIT_ADMIN_IMPERSONATE_USER');
                    $adminId = $this->securiteService->getUtilisateurConnecte()['numero_utilisateur'];
                    $this->securiteService->demarrerImpersonation($adminId, $id);
                    $this->redirect('/dashboard');
                    return;
                case 'delete':
                    $this->requirePermission('TRAIT_ADMIN_UTILISATEURS_FORM_GENERIC_ACCES');
                    $this->serviceUtilisateur->supprimerUtilisateurEtEntite($id);
                    $this->addFlashMessage('success', "Utilisateur supprimé.");
                    break;
            }
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur: ' . $e->getMessage());
        }
        $this->redirect('/admin/utilisateurs');
    }
}