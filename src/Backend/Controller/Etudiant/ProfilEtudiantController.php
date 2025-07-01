<?php
// src/Backend/Controller/Etudiant/ProfilEtudiantController.php

namespace App\Backend\Controller\Etudiant;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Utilisateur\ServiceUtilisateurInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;

class ProfilEtudiantController extends BaseController
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
     * Affiche le profil de l'étudiant connecté.
     */
    public function showProfile(): void
    {
        $this->checkPermission('ETUDIANT_PROFIL_READ');
        $user = $this->serviceSecurite->getUtilisateurConnecte();
        $this->render('Etudiant/profil_etudiant.php', [
            'title' => 'Mon Profil',
            'user' => $user
        ]);
    }

    /**
     * Traite la mise à jour du profil de l'étudiant.
     */
    public function updateProfile(): void
    {
        $this->checkPermission('ETUDIANT_PROFIL_UPDATE');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['success' => false, 'message' => 'Requête invalide.'], 403);
            return;
        }

        $user = $this->serviceSecurite->getUtilisateurConnecte();
        $rules = [
            'telephone' => 'max:20',
            'email_contact_secondaire' => 'email|max:255',
            'adresse_postale' => 'max:500'
        ];

        if (!$this->formValidator->validate($_POST, $rules)) {
            $this->jsonResponse(['success' => false, 'errors' => $this->formValidator->getErrors()], 422);
            return;
        }

        try {
            $donneesProfil = [
                'telephone' => $_POST['telephone'],
                'email_contact_secondaire' => $_POST['email_contact_secondaire'],
                'adresse_postale' => $_POST['adresse_postale']
            ];
            $this->serviceUtilisateur->mettreAJourUtilisateur($user['numero_utilisateur'], $donneesProfil, []);
            $this->jsonResponse(['success' => true, 'message' => 'Profil mis à jour avec succès.']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}