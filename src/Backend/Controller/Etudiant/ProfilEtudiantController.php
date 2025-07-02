<?php
// src/Backend/Controller/Etudiant/ProfilEtudiantController.php

namespace App\Backend\Controller\Etudiant;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Utilisateur\ServiceUtilisateurInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator; // Garder l'import pour le constructeur
use Exception;

/**
 * Gère le profil de l'étudiant : consultation, mise à jour, et photo.
 */
class ProfilEtudiantController extends BaseController
{
    private ServiceUtilisateurInterface $serviceUtilisateur;
    // Suppression de la déclaration de propriété $validator
    // car elle est déjà disponible via BaseController::$validator (si BaseController l'injecte)

    public function __construct(
        ServiceUtilisateurInterface $serviceUtilisateur,
        FormValidator $validator, // Injecté pour BaseController
        ServiceSecuriteInterface $securiteService, // Injecté pour BaseController
        ServiceSupervisionInterface $supervisionService // Injecté pour BaseController
    ) {
        parent::__construct($securiteService, $supervisionService);
        $this->serviceUtilisateur = $serviceUtilisateur;
        // Pas besoin de réassigner $this->validator ici si BaseController le fait
    }

    /**
     * Affiche la page de profil de l'étudiant connecté.
     */
    public function show(): void
    {
        $this->requirePermission('TRAIT_ETUDIANT_PROFIL_GERER');

        $user = $this->securiteService->getUtilisateurConnecte();
        if (!$user) {
            $this->redirect('/login');
            return; // Suppression de l'instruction inaccessible
        }

        try {
            $fullUserData = $this->serviceUtilisateur->lireUtilisateurComplet($user['numero_utilisateur']);
            if (!$fullUserData) {
                throw new Exception("Données de profil introuvables.");
            }

            $this->render('Etudiant/profil_etudiant', [
                'title' => 'Mon Profil',
                'user' => $fullUserData,
                'csrf_token_profile' => $this->generateCsrfToken('profile_form'),
                'csrf_token_photo' => $this->generateCsrfToken('photo_form')
            ]);
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Erreur lors du chargement du profil : ' . $e->getMessage());
            $this->redirect('/etudiant/dashboard');
        }
    }

    /**
     * Traite la mise à jour des informations personnelles de l'étudiant.
     */
    public function update(): void
    {
        $this->requirePermission('TRAIT_ETUDIANT_PROFIL_GERER');

        if (!$this->isPostRequest() || !$this->validateCsrfToken('profile_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/etudiant/profil');
            return; // Suppression de l'instruction inaccessible
        }

        $rules = [
            'telephone' => 'max:20',
            'email_contact_secondaire' => 'email|max:255',
            'adresse_postale' => 'max:500',
            'ville' => 'max:100',
            'code_postal' => 'max:20',
            'contact_urgence_nom' => 'max:100',
            'contact_urgence_telephone' => 'max:20',
            'contact_urgence_relation' => 'max:50'
        ];

        $data = $this->getPostData();

        if (!$this->validator->validate($data, $rules)) {
            $this->addFlashMessage('error', 'Erreur de validation : ' . implode(', ', $this->validator->getErrors()));
        } else {
            try {
                $user = $this->securiteService->getUtilisateurConnecte();
                $donneesProfil = [
                    'telephone' => $data['telephone'],
                    'email_contact_secondaire' => $data['email_contact_secondaire'],
                    'adresse_postale' => $data['adresse_postale'],
                    'ville' => $data['ville'],
                    'code_postal' => $data['code_postal'],
                    'contact_urgence_nom' => $data['contact_urgence_nom'],
                    'contact_urgence_telephone' => $data['contact_urgence_telephone'],
                    'contact_urgence_relation' => $data['contact_urgence_relation']
                ];
                $this->serviceUtilisateur->mettreAJourUtilisateur($user['numero_utilisateur'], $donneesProfil, []);
                $this->addFlashMessage('success', 'Profil mis à jour avec succès.');
            } catch (Exception $e) {
                $this->addFlashMessage('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
            }
        }
        $this->redirect('/etudiant/profil');
    }

    /**
     * Traite le téléversement de la photo de profil.
     */
    public function handlePhotoUpload(): void
    {
        $this->requirePermission('TRAIT_ETUDIANT_PROFIL_GERER');

        if (!$this->isPostRequest() || !$this->validateCsrfToken('photo_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/etudiant/profil');
            return; // Suppression de l'instruction inaccessible
        }

        $fileData = $this->getFileData('photo_profil_file');
        if ($fileData && $fileData['error'] === UPLOAD_ERR_OK) {
            try {
                $user = $this->securiteService->getUtilisateurConnecte();
                $this->serviceUtilisateur->telechargerPhotoProfil($user['numero_utilisateur'], $fileData);
                $this->addFlashMessage('success', 'Photo de profil mise à jour.');
            } catch (Exception $e) {
                $this->addFlashMessage('error', $e->getMessage());
            }
        } else {
            $this->addFlashMessage('error', 'Aucun fichier sélectionné ou une erreur est survenue lors du téléversement.');
        }
        $this->redirect('/etudiant/profil');
    }
}