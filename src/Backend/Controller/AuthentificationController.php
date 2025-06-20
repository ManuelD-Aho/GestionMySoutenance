<?php
namespace App\Backend\Controller;

use App\Backend\Service\Authentication\ServiceAuthentification;
use App\Backend\Service\Permissions\ServicePermissions; // Requis par BaseController
use App\Backend\Util\FormValidator;
use App\Backend\Exception\IdentifiantsInvalidesException;
use App\Backend\Exception\CompteBloqueException;
use App\Backend\Exception\CompteNonValideException;
use App\Backend\Exception\MotDePasseInvalideException;
use App\Backend\Exception\EmailException;
use App\Backend\Exception\TokenInvalideException;
use App\Backend\Exception\TokenExpireException;

class AuthentificationController extends BaseController
{
    private ServiceAuthentification $authService;

    public function __construct(
        ServiceAuthentification $authService,
        ServicePermissions $permissionService, // Requis par BaseController
        FormValidator $validator
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->authService = $authService; // Réassignation pour un accès direct
    }

    /**
     * Redirige l'utilisateur vers le tableau de bord s'il est déjà connecté.
     */
    protected function requireNoLogin(): void
    {
        // Vérifie si l'utilisateur est déjà connecté ET n'est pas en attente de 2FA
        if ($this->authService->estUtilisateurConnecteEtSessionValide() && !isset($_SESSION['2fa_pending'])) {
            $this->redirect('/dashboard'); // Redirige vers le tableau de bord
        }
    }

    /**
     * Affiche le formulaire de connexion.
     */
    public function showLoginForm(): void
    {
        $this->requireNoLogin(); // Assurer que l'utilisateur n'est pas déjà connecté

        $data = ['page_title' => 'Connexion'];
        $this->render('Auth/login', $data, 'none'); // Pas de layout pour la page de login pour être simple
    }

    /**
     * Traite la soumission du formulaire de connexion.
     */
    public function handleLogin(): void
    {
        $this->requireNoLogin(); // S'assurer que l'utilisateur n'est pas déjà connecté

        if (!$this->isPostRequest()) {
            $this->redirect('/login');
        }

        $identifiant = $this->getRequestData('login_email');
        $motDePasse = $this->getRequestData('password');

        // Validation simple côté contrôleur (règles plus complexes dans le service)
        $rules = [
            'login_email' => 'required|string',
            'password' => 'required|string',
        ];
        $this->validator->validate(['login_email' => $identifiant, 'password' => $motDePasse], $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect('/login');
        }

        try {
            $result = $this->authService->tenterConnexion($identifiant, $motDePasse);

            if ($result['status'] === '2fa_required') {
                $this->redirect('/2fa'); // Rediriger vers le formulaire 2FA
            } else if ($result['status'] === 'success') {
                $this->setFlashMessage('success', 'Connexion réussie !');
                $this->redirect('/dashboard'); // Rediriger vers le tableau de bord
            }
        } catch (IdentifiantsInvalidesException $e) {
            $this->setFlashMessage('error', 'Identifiants invalides. Veuillez réessayer.');
            $this->redirect('/login');
        } catch (CompteBloqueException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/login');
        } catch (CompteNonValideException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/login');
        } catch (\Exception $e) { // Capturer d'autres erreurs inattendues
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue lors de la connexion.');
            error_log("Login error: " . $e->getMessage()); // Log l'erreur pour le débogage
            $this->redirect('/login');
        }
    }

    /**
     * Gère la déconnexion de l'utilisateur.
     */
    public function logout(): void
    {
        $this->authService->logout();
        $this->setFlashMessage('success', 'Vous avez été déconnecté avec succès.');
        $this->redirect('/login');
    }

    /**
     * Affiche le formulaire de mot de passe oublié.
     */
    public function showForgotPasswordForm(): void
    {
        $this->requireNoLogin();
        $data = ['page_title' => 'Mot de passe oublié'];
        $this->render('Auth/forgot_password_form', $data, 'none');
    }

    /**
     * Traite la demande de réinitialisation de mot de passe.
     */
    public function handleForgotPasswordRequest(): void
    {
        $this->requireNoLogin();

        if (!$this->isPostRequest()) {
            $this->redirect('/forgot-password');
        }

        $email = $this->getRequestData('email');
        $rules = ['email' => 'required|email'];
        $this->validator->validate(['email' => $email], $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect('/forgot-password');
        }

        try {
            $this->authService->demanderReinitialisationMotDePasse($email);
            // Toujours afficher un message générique pour des raisons de sécurité (éviter l'énumération d'emails)
            $this->setFlashMessage('success', 'Si votre adresse e-mail est dans notre système, un lien de réinitialisation de mot de passe vous a été envoyé.');
            $this->redirect('/login'); // Rediriger vers la page de connexion
        } catch (EmailException $e) {
            $this->setFlashMessage('error', 'Impossible d\'envoyer l\'e-mail de réinitialisation. Veuillez contacter l\'administrateur.');
            $this->redirect('/forgot-password');
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue.');
            error_log("Forgot password error: " . $e->getMessage());
            $this->redirect('/forgot-password');
        }
    }

    /**
     * Affiche le formulaire de réinitialisation de mot de passe.
     * @param string $token Le token de réinitialisation reçu par email.
     */
    public function showResetPasswordForm(string $token): void
    {
        $this->requireNoLogin();

        try {
            // Vérifier la validité du token avant d'afficher le formulaire
            // Le service gère la logique de hachage et d'expiration
            $user = $this->authService->utilisateurModel->trouverParTokenResetMdp(hash('sha256', $token));
            if (!$user) {
                throw new TokenInvalideException("Le lien de réinitialisation est invalide ou a déjà été utilisé.");
            }
            if (new \DateTime() > new \DateTime($user['date_expiration_token_reset'])) {
                throw new TokenExpireException("Le lien de réinitialisation a expiré.");
            }

            $data = [
                'page_title' => 'Réinitialiser votre mot de passe',
                'token' => $token
            ];
            $this->render('Auth/reset_password_form', $data, 'none');
        } catch (TokenInvalideException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/login');
        } catch (TokenExpireException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/forgot-password'); // Rediriger vers le formulaire "mot de passe oublié" pour redemander
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur est survenue lors de la validation du lien.');
            error_log("Reset password form error: " . $e->getMessage());
            $this->redirect('/login');
        }
    }

    /**
     * Traite la soumission du formulaire de réinitialisation de mot de passe.
     */
    public function handleResetPasswordSubmission(): void
    {
        $this->requireNoLogin();

        if (!$this->isPostRequest()) {
            $this->redirect('/login');
        }

        $token = $this->getRequestData('token');
        $newPassword = $this->getRequestData('new_password');
        $confirmPassword = $this->getRequestData('confirm_password');

        $rules = [
            'token' => 'required|string',
            'new_password' => 'required|string|min:8',
            'confirm_password' => 'required|same:new_password',
        ];
        $this->validator->validate(['token' => $token, 'new_password' => $newPassword, 'confirm_password' => $confirmPassword], $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect('/reset-password?token=' . urlencode($token));
        }

        try {
            $this->authService->reinitialiserMotDePasseApresValidationToken($token, $newPassword);
            $this->setFlashMessage('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
            $this->redirect('/login');
        } catch (TokenInvalideException $e) {
            $this->setFlashMessage('error', 'Le lien de réinitialisation est invalide ou a déjà été utilisé.');
            $this->redirect('/login');
        } catch (TokenExpireException $e) {
            $this->setFlashMessage('error', 'Le lien de réinitialisation a expiré. Veuillez refaire une demande.');
            $this->redirect('/forgot-password');
        } catch (MotDePasseInvalideException $e) {
            $this->setFlashMessage('error', 'Mot de passe invalide: ' . $e->getMessage());
            $this->redirect('/reset-password?token=' . urlencode($token));
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue lors de la réinitialisation.');
            error_log("Reset password submission error: " . $e->getMessage());
            $this->redirect('/login');
        }
    }

    /**
     * Affiche le formulaire de saisie du code 2FA.
     */
    public function show2FAForm(): void
    {
        // L'utilisateur doit être en attente de 2FA
        if (!isset($_SESSION['2fa_pending']) || !isset($_SESSION['2fa_user_id'])) {
            $this->setFlashMessage('error', 'Accès non autorisé au formulaire 2FA.');
            $this->redirect('/login');
        }
        $data = ['page_title' => 'Vérification 2FA'];
        $this->render('Auth/form_2fa', $data, 'none');
    }

    /**
     * Traite la soumission du code 2FA.
     */
    public function handle2FASubmission(): void
    {
        // Vérifier que la vérification 2FA est en attente
        if (!isset($_SESSION['2fa_pending']) || !isset($_SESSION['2fa_user_id'])) {
            $this->setFlashMessage('error', 'Session 2FA expirée ou accès non autorisé.');
            $this->redirect('/login');
        }

        if (!$this->isPostRequest()) {
            $this->redirect('/2fa');
        }

        $userId = $_SESSION['2fa_user_id'];
        $codeTOTP = $this->getRequestData('code_2fa');

        $rules = ['code_2fa' => 'required|numeric|length:6']; // Le code TOTP est généralement à 6 chiffres
        $this->validator->validate(['code_2fa' => $codeTOTP], $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect('/2fa');
        }

        try {
            if ($this->authService->verifierCodeAuthentificationDeuxFacteurs($userId, $codeTOTP)) {
                $this->authService->demarrerSessionUtilisateur($userId); // Démarre la session complète
                $this->setFlashMessage('success', 'Vérification 2FA réussie. Bienvenue !');
                $this->redirect('/dashboard');
            } else {
                throw new IdentifiantsInvalidesException("Code 2FA incorrect. Veuillez réessayer.");
            }
        } catch (IdentifiantsInvalidesException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/2fa');
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue lors de la vérification 2FA.');
            error_log("2FA submission error: " . $e->getMessage());
            $this->redirect('/login');
        }
    }

    /**
     * Affiche le formulaire de changement de mot de passe (pour utilisateur connecté).
     */
    public function showChangePasswordForm(): void
    {
        $this->requireLogin(); // Exiger que l'utilisateur soit connecté

        $data = ['page_title' => 'Changer votre mot de passe'];
        $this->render('Auth/change_password_form', $data); // Créer cette vue
    }

    /**
     * Traite le changement de mot de passe de l'utilisateur connecté.
     */
    public function handleChangePassword(): void
    {
        $this->requireLogin(); // Exiger que l'utilisateur soit connecté

        if (!$this->isPostRequest()) {
            $this->redirect('/dashboard/profile/change-password');
        }

        $ancienMotDePasse = $this->getRequestData('old_password');
        $nouveauMotDePasse = $this->getRequestData('new_password');
        $confirmNouveauMotDePasse = $this->getRequestData('confirm_new_password');

        $rules = [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8',
            'confirm_new_password' => 'required|same:new_password',
        ];
        $this->validator->validate([
            'old_password' => $ancienMotDePasse,
            'new_password' => $nouveauMotDePasse,
            'confirm_new_password' => $confirmNouveauMotDePasse
        ], $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect('/dashboard/profile/change-password');
        }

        try {
            $userId = $this->getCurrentUser()['numero_utilisateur'];
            $this->authService->modifierMotDePasse($userId, $nouveauMotDePasse, $ancienMotDePasse);
            $this->setFlashMessage('success', 'Votre mot de passe a été modifié avec succès.');
            $this->redirect('/dashboard/profile'); // Rediriger vers la page de profil
        } catch (MotDePasseInvalideException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/dashboard/profile/change-password');
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue lors du changement de mot de passe.');
            error_log("Change password error: " . $e->getMessage());
            $this->redirect('/dashboard/profile/change-password');
        }
    }
}