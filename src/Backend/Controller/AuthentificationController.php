<?php

namespace App\Backend\Controller;

use App\Backend\Service\Authentication\ServiceAuthenticationInterface;
use App\Backend\Exception\AuthenticationException;
use App\Backend\Exception\CompteBloqueException;
use App\Backend\Exception\IdentifiantsInvalidesException;
use App\Backend\Exception\UtilisateurNonTrouveException;
use App\Backend\Exception\CompteNonValideException;
use App\Backend\Exception\ValidationException;
use App\Backend\Exception\TokenInvalideException;
use App\Backend\Exception\TokenExpireException;
use App\Backend\Exception\MotDePasseInvalideException;
use App\Backend\Exception\OperationImpossibleException;

class AuthentificationController extends BaseController
{
    private ServiceAuthenticationInterface $authService;

    public function __construct()
    {
        parent::__construct();
        $this->authService = $this->container->getAuthService();
    }

    /**
     * Redirige l'utilisateur vers le tableau de bord s'il est déjà connecté.
     */
    protected function requireNoLogin(): void
    {
        if (isset($_SESSION['user']['numero_utilisateur'])) {
            $this->redirect('/dashboard');
        }
    }

    /**
     * Affiche le formulaire de connexion.
     */
    public function showLoginForm(): void
    {
        $this->requireNoLogin();
        $this->generateCsrfToken(); // Génère le token pour le formulaire

        $this->render('Auth/login', [
            'error' => $this->getFlashMessage('login_error_message'),
            'success_message' => $this->getFlashMessage('login_message_succes'),
            'title' => 'Connexion'
        ]);
    }

    /**
     * Traite la soumission du formulaire de connexion.
     */
    public function handleLogin(): void
    {
        $this->verifyCsrfToken(); // Vérifie le token
        $this->requireNoLogin();

        $identifiant = $_POST['identifiant'] ?? '';
        $motDePasse = $_POST['mot_de_passe'] ?? '';

        if (empty($identifiant) || empty($motDePasse)) {
            $this->setFlashMessage('login_error_message', "L'identifiant et le mot de passe sont requis.");
            $this->redirect('/login');
        }

        try {
            $this->authService->tenterConnexion($identifiant, $motDePasse);
            $redirectUrl = $_SESSION['redirect_after_login'] ?? '/dashboard';
            unset($_SESSION['redirect_after_login']);
            $this->redirect($redirectUrl);
        } catch (AuthenticationException $e) {
            if ($e->getCode() === 1001) { // Redirection vers 2FA
                $this->redirect('/login-2fa');
            }
            $this->setFlashMessage('login_error_message', $e->getMessage());
        } catch (IdentifiantsInvalidesException | CompteBloqueException | CompteNonValideException | UtilisateurNonTrouveException $e) {
            $this->setFlashMessage('login_error_message', $e->getMessage());
        } catch (\Exception $e) {
            error_log("Erreur dans handleLogin: " . $e->getMessage());
            $this->setFlashMessage('login_error_message', "Une erreur inattendue est survenue.");
        }
        $this->redirect('/login');
    }

    /**
     * Gère la déconnexion.
     */
    public function logout(): void
    {
        $this->authService->terminerSessionUtilisateur();
        $this->setFlashMessage('login_message_succes', 'Vous avez été déconnecté avec succès.');
        $this->redirect('/login');
    }

    /**
     * Affiche le formulaire pour mot de passe oublié.
     */
    public function showForgotPasswordForm(): void
    {
        $this->requireNoLogin();
        $this->generateCsrfToken();
        $this->render('Auth/forgot_password_form', [
            'success_message' => $this->getFlashMessage('forgot_password_message_succes'),
            'error_message' => $this->getFlashMessage('forgot_password_message_erreur'),
            'title' => 'Mot de Passe Oublié'
        ], 'layout/minimal');
    }

    /**
     * Traite la demande de réinitialisation de mot de passe.
     */
    public function handleForgotPasswordRequest(): void
    {
        $this->verifyCsrfToken();
        $this->requireNoLogin();
        $email = $_POST['email_principal'] ?? '';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlashMessage('forgot_password_message_erreur', "Veuillez fournir une adresse email valide.");
            $this->redirect('/forgot-password');
        }

        try {
            $this->authService->demanderReinitialisationMotDePasse($email);
        } catch (\Exception $e) {
            error_log("Erreur dans handleForgotPasswordRequest: " . $e->getMessage());
        }
        // Dans tous les cas, on affiche le même message pour ne pas révéler si un email existe.
        $this->setFlashMessage('forgot_password_message_succes', "Si un compte est associé à cet email, un lien de réinitialisation a été envoyé.");
        $this->redirect('/forgot-password');
    }

    /**
     * Affiche le formulaire de réinitialisation de mot de passe.
     */
    public function showResetPasswordForm(): void
    {
        $this->requireNoLogin();
        $token = $_GET['token'] ?? '';
        $this->generateCsrfToken();

        try {
            $this->authService->validerTokenReinitialisationMotDePasse($token);
            // Stocker le token validé en session pour la soumission du formulaire
            $_SESSION['reset_password_token_valide'] = $token;
            $this->render('Auth/reset_password_form', ['token' => $token, 'title' => 'Réinitialiser le Mot de Passe'], 'layout/minimal');
        } catch (TokenInvalideException | TokenExpireException $e) {
            $this->setFlashMessage('login_error_message', $e->getMessage());
            $this->redirect('/login');
        }
    }

    /**
     * Traite la soumission du nouveau mot de passe.
     */
    public function handleResetPasswordSubmission(): void
    {
        $this->verifyCsrfToken();
        $this->requireNoLogin();

        $token = $_POST['token'] ?? '';
        $nouveauMdp = $_POST['nouveau_mot_de_passe'] ?? '';
        $confirmerMdp = $_POST['confirmer_mot_de_passe'] ?? '';

        if (empty($token) || empty($_SESSION['reset_password_token_valide']) || !hash_equals($_SESSION['reset_password_token_valide'], $token)) {
            $this->setFlashMessage('login_error_message', "Session de réinitialisation invalide. Veuillez refaire la demande.");
            unset($_SESSION['reset_password_token_valide']);
            $this->redirect('/forgot-password');
        }

        if ($nouveauMdp !== $confirmerMdp) {
            $this->setFlashMessage('reset_password_error', "Les mots de passe ne correspondent pas.");
            $this->redirect('/reset-password?token=' . urlencode($token));
        }

        try {
            $this->authService->reinitialiserMotDePasseApresValidationToken($token, $nouveauMdp);
            $this->setFlashMessage('login_message_succes', "Votre mot de passe a été réinitialisé avec succès. Vous pouvez vous connecter.");
            unset($_SESSION['reset_password_token_valide']);
            $this->redirect('/login');
        } catch (ValidationException | MotDePasseInvalideException $e) {
            $this->setFlashMessage('reset_password_error', $e->getMessage());
            $this->redirect('/reset-password?token=' . urlencode($token));
        } catch (\Exception $e) {
            error_log("Erreur handleResetPasswordSubmission: " . $e->getMessage());
            $this->setFlashMessage('reset_password_error', "Une erreur inattendue est survenue.");
            $this->redirect('/reset-password?token=' . urlencode($token));
        }
    }

    // --- Méthodes pour le 2FA ---

    public function show2FAForm(): void
    {
        if (!isset($_SESSION['2fa_user_num_pending_verification'])) {
            $this->redirect('/login');
        }
        $this->generateCsrfToken();
        $this->render('Auth/form_2fa', [
            'error' => $this->getFlashMessage('2fa_error_message'),
            'title' => 'Vérification 2FA'
        ], 'layout/minimal');
    }

    public function handle2FASubmission(): void
    {
        $this->verifyCsrfToken();
        if (!isset($_SESSION['2fa_user_num_pending_verification'])) {
            $this->redirect('/login');
        }

        $code2FA = $_POST['code_2fa'] ?? '';
        $numUser = $_SESSION['2fa_user_num_pending_verification'];

        try {
            if ($this->authService->verifierCodeAuthentificationDeuxFacteurs($numUser, $code2FA)) {
                $utilisateurObjet = $this->authService->recupererUtilisateurCompletParNumero($numUser);
                $this->authService->demarrerSessionUtilisateur($utilisateurObjet);

                $redirectUrl = $_SESSION['redirect_after_login'] ?? '/dashboard';
                unset($_SESSION['redirect_after_login']);
                $this->redirect($redirectUrl);
            }
        } catch (MotDePasseInvalideException $e) {
            $this->setFlashMessage('2fa_error_message', $e->getMessage());
        } catch (\Exception $e) {
            error_log("Erreur handle2FASubmission: " . $e->getMessage());
            $this->setFlashMessage('2fa_error_message', "Une erreur de vérification est survenue.");
        }

        $this->redirect('/login-2fa');
    }

    // --- Méthodes pour le profil utilisateur ---

    public function showChangePasswordForm(): void
    {
        $this->requireLogin();
        $this->generateCsrfToken();
        $this->render('Profile/change_password_form', [
            'success_message' => $this->getFlashMessage('profile_message_succes'),
            'error_message' => $this->getFlashMessage('profile_error_message'),
            'title' => 'Changer Votre Mot de Passe'
        ]);
    }

    public function handleChangePassword(): void
    {
        $this->requireLogin();
        $this->verifyCsrfToken();

        $numUser = $_SESSION['user']['numero_utilisateur'];
        $ancienMdp = $_POST['ancien_mot_de_passe'] ?? '';
        $nouveauMdp = $_POST['nouveau_mot_de_passe'] ?? '';
        $confirmerMdp = $_POST['confirmer_nouveau_mot_de_passe'] ?? '';

        if ($nouveauMdp !== $confirmerMdp) {
            $this->setFlashMessage('profile_error_message', "Les nouveaux mots de passe ne correspondent pas.");
            $this->redirect('/profile/change-password');
        }

        try {
            $this->authService->modifierMotDePasse($numUser, $nouveauMdp, $ancienMdp);
            $this->setFlashMessage('profile_message_succes', "Votre mot de passe a été changé avec succès.");
        } catch (ValidationException | MotDePasseInvalideException $e) {
            $this->setFlashMessage('profile_error_message', $e->getMessage());
        } catch (\Exception $e) {
            error_log("Erreur handleChangePassword: " . $e->getMessage());
            $this->setFlashMessage('profile_error_message', "Une erreur s'est produite.");
        }
        $this->redirect('/profile/change-password');
    }
}