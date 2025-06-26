<?php
namespace App\Backend\Controller\Etudiant;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ValidationException;

class ProfilEtudiantController extends BaseController
{
    protected ServiceAuthentication $authService;

    public function __construct(
        ServiceAuthentication $authService,
        ServicePermissions    $permissionService,
        FormValidator         $validator
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->authService = $authService; // Réassignation pour un accès direct
    }

    /**
     * Affiche le profil de l'étudiant ou la traite (si c'est un POST pour la mise à jour).
     */
    public function index(): void
    {
        $this->requirePermission('TRAIT_ETUDIANT_PROFIL_ACCEDER'); // Exiger la permission

        if ($this->isPostRequest()) {
            $this->handleUpdateProfile();
        } else {
            try {
                $currentUser = $this->getCurrentUser();
                if (!$currentUser || $currentUser['id_type_utilisateur'] !== 'TYPE_ETUD') {
                    throw new OperationImpossibleException("Accès refusé. Non étudiant.");
                }
                $etudiantData = $currentUser['profil']; // Le profil est chargé avec l'utilisateur complet

                $data = [
                    'page_title' => 'Mon Profil Étudiant',
                    'etudiant' => $etudiantData,
                    'user_account' => $currentUser, // Pour les infos du compte utilisateur (email, login)
                    'form_action' => '/dashboard/etudiant/profile'
                ];
                $this->render('Etudiant/profil_etudiant', $data);
            } catch (\Exception $e) {
                $this->setFlashMessage('error', "Erreur lors du chargement de votre profil: " . $e->getMessage());
                $this->redirect('/dashboard/etudiant');
            }
        }
    }

    /**
     * Traite la soumission du formulaire de mise à jour du profil étudiant.
     */
    private function handleUpdateProfile(): void
    {
        $this->requirePermission('TRAIT_ETUDIANT_PROFIL_MODIFIER'); // Permission de modifier le profil

        $currentUser = $this->getCurrentUser();
        if (!$currentUser || $currentUser['id_type_utilisateur'] !== 'TYPE_ETUD') {
            $this->setFlashMessage('error', "Accès refusé.");
            $this->redirect('/dashboard/etudiant/profile');
        }
        $numeroUtilisateur = $currentUser['numero_utilisateur'];

        $profileData = [
            'nom' => $this->getRequestData('nom'),
            'prenom' => $this->getRequestData('prenom'),
            'date_naissance' => $this->getRequestData('date_naissance'),
            'telephone' => $this->getRequestData('telephone'),
            'email_contact_secondaire' => $this->getRequestData('email_contact_secondaire'),
            // ... autres champs du profil Etudiant
        ];
        $userAccountData = [
            'email_principal' => $this->getRequestData('email_principal'),
            'login_utilisateur' => $this->getRequestData('login_utilisateur'),
        ];

        // Gérer l'upload de la photo de profil (si un champ 'photo_profil' existe dans le formulaire)
        if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../Public/uploads/profile_pictures/';
            if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
            $fileExtension = pathinfo($_FILES['photo_profil']['name'], PATHINFO_EXTENSION);
            $newFileName = $numeroUtilisateur . '_' . time() . '.' . $fileExtension;
            $filePath = $uploadDir . $newFileName;

            if (move_uploaded_file($_FILES['photo_profil']['tmp_name'], $filePath)) {
                $userAccountData['photo_profil'] = '/uploads/profile_pictures/' . $newFileName; // Chemin relatif pour la DB
            } else {
                $this->setFlashMessage('error', 'Échec de l\'upload de la photo de profil.');
                $this->redirect('/dashboard/etudiant/profile');
                return;
            }
        }

        $rulesProfil = [
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'date_naissance' => 'nullable|date',
            'telephone' => 'nullable|string|max:20',
            'email_contact_secondaire' => 'nullable|email|max:255',
        ];
        $rulesAccount = [
            'login_utilisateur' => 'required|string|min:3|max:100',
            'email_principal' => 'required|email|max:255',
        ];

        $this->validator->validate($profileData, $rulesProfil);
        $this->validator->validate($userAccountData, $rulesAccount);


        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect('/dashboard/etudiant/profile');
        }

        try {
            // Mettre à jour les infos du compte utilisateur (login, email, photo)
            $this->authService->mettreAJourCompteUtilisateurParAdmin($numeroUtilisateur, $userAccountData); // Utiliser cette méthode même pour l'utilisateur lui-même car elle gère les unicité
            // Mettre à jour les infos du profil spécifique (étudiant)
            $this->authService->mettreAJourProfilUtilisateur($numeroUtilisateur, 'TYPE_ETUD', $profileData);

            $this->setFlashMessage('success', 'Votre profil a été mis à jour avec succès.');
            $this->redirect('/dashboard/etudiant/profile');
        } catch (DoublonException $e) {
            $this->setFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect('/dashboard/etudiant/profile');
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
            $this->redirect('/dashboard/etudiant/profile');
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
            $this->redirect('/dashboard/etudiant/profile');
        }
    }

    /**
     * Gère la génération ou l'activation de l'authentification à deux facteurs (2FA).
     */
    public function manage2FA(): void
    {
        $this->requirePermission('TRAIT_ETUDIANT_PROFIL_GERER_2FA'); // Permission pour gérer la 2FA

        if ($this->isPostRequest()) {
            $this->handle2FASubmission(); // Gère l'activation/désactivation via POST
        } else {
            try {
                $currentUser = $this->getCurrentUser();
                if (!$currentUser) { throw new ElementNonTrouveException("Utilisateur non trouvé."); }
                $numeroUtilisateur = $currentUser['numero_utilisateur'];

                $qrCodeData = null;
                if (!$currentUser['preferences_2fa_active']) {
                    // Générer un nouveau secret et QR code seulement si 2FA n'est pas actif
                    $qrCodeData = $this->authService->genererEtStockerSecret2FA($numeroUtilisateur);
                }

                $data = [
                    'page_title' => 'Sécurité - Authentification à Deux Facteurs',
                    'is_2fa_active' => $currentUser['preferences_2fa_active'],
                    'qr_code_url' => $qrCodeData['qr_code_url'] ?? null,
                    'secret_key' => $qrCodeData['secret'] ?? null,
                    'form_action_activate' => '/dashboard/etudiant/profile/2fa/activate',
                    'form_action_deactivate' => '/dashboard/etudiant/profile/2fa/deactivate',
                ];
                $this->render('Auth/form_2fa_setup', $data); // Créer une vue spécifique pour le setup 2FA
            } catch (\Exception $e) {
                $this->setFlashMessage('error', 'Erreur chargement 2FA: ' . $e->getMessage());
                $this->redirect('/dashboard/etudiant/profile');
            }
        }
    }

    /**
     * Traite l'activation ou la désactivation de la 2FA.
     */
    private function handle2FASubmission(): void
    {
        $this->requirePermission('TRAIT_ETUDIANT_PROFIL_GERER_2FA');

        $action = $this->getRequestData('action'); // 'activate' ou 'deactivate'
        $codeTOTP = $this->getRequestData('code_2fa');

        $currentUser = $this->getCurrentUser();
        if (!$currentUser) { throw new ElementNonTrouveException("Utilisateur non trouvé."); }
        $numeroUtilisateur = $currentUser['numero_utilisateur'];

        try {
            if ($action === 'activate') {
                $rules = ['code_2fa' => 'required|numeric|length:6'];
                $this->validator->validate(['code_2fa' => $codeTOTP], $rules);
                if (!$this->validator->isValid()) {
                    throw new ValidationException(implode('<br>', $this->validator->getErrors()));
                }
                $this->authService->activerAuthentificationDeuxFacteurs($numeroUtilisateur, $codeTOTP);
                $this->setFlashMessage('success', 'Authentification à deux facteurs activée avec succès !');
            } elseif ($action === 'deactivate') {
                $this->authService->desactiverAuthentificationDeuxFacteurs($numeroUtilisateur);
                $this->setFlashMessage('success', 'Authentification à deux facteurs désactivée.');
            } else {
                throw new OperationImpossibleException("Action 2FA invalide.");
            }
            $this->redirect('/dashboard/etudiant/profile');
        } catch (ValidationException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/dashboard/etudiant/profile/2fa');
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erreur lors de la gestion 2FA: ' . $e->getMessage());
            $this->redirect('/dashboard/etudiant/profile/2fa');
        }
    }

    // Les méthodes create(), update(), delete() génériques du template initial sont à supprimer.
    /*
    public function create(): void {}
    public function update($id): void {}
    public function delete($id): void {}
    */
}