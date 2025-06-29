<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Service\Interface\AuthenticationServiceInterface;
use App\Backend\Service\Interface\PermissionsServiceInterface;
use App\Backend\Util\FormValidator;
use App\Backend\Exception\IdentifiantsInvalidesException;
use App\Backend\Exception\CompteBloqueException;
use App\Backend\Exception\CompteNonValideException;
use App\Backend\Exception\MotDePasseInvalideException;
use App\Backend\Exception\EmailException;
use App\Backend\Exception\TokenInvalideException;
use App\Backend\Exception\TokenExpireException;
use App\Backend\Exception\ValidationException;
use App\Backend\Exception\PermissionException;

/**
 * AuthentificationController - Le Gardien de l'Accès à l'Application.
 *
 * Rédigé le : 2025-06-29 14:03:31 UTC par ManuelD-Aho
 *
 * Ce contrôleur gère l'ensemble des flux d'authentification : connexion, déconnexion,
 * mot de passe oublié, réinitialisation de mot de passe et authentification à deux facteurs (2FA).
 * Il est conçu pour être la première ligne de défense de l'application.
 */
class AuthentificationController extends BaseController
{
    /**
     * Surcharge du constructeur pour maintenir la clarté des dépendances,
     * même si elles sont déjà dans le parent.
     */
    public function __construct(
        AuthenticationServiceInterface $authService,
        PermissionsServiceInterface $permissionService,
        FormValidator $validator
    ) {
        parent::__construct($authService, $permissionService, $validator);
    }

    /**
     * Affiche le formulaire de connexion.
     */
    public function showLoginForm(): void
    {
        $this->_redirectIfLoggedIn();
        $this->render('Auth/login');
    }

    /**
     * Traite la soumission du formulaire de connexion.
     */
    public function handleLogin(): void
    {
        $this->_redirectIfLoggedIn();

        if ($this->getMethod() !== 'POST') {
            $this->redirect('/login');
        }

        try {
            $this->verifyCsrfToken();

            $identifiant = $this->post('login_utilisateur');
            $motDePasse = $this->post('password');

            $this->validator->validate(
                ['login' => $identifiant, 'password' => $motDePasse],
                ['login' => 'required|string', 'password' => 'required|string']
            );

            $result = $this->authService->tenterConnexion($identifiant, $motDePasse);

            if ($result['status'] === '2fa_required') {
                $this->redirect('/2fa');
            } elseif ($result['status'] === 'success') {
                $this->redirect('/dashboard');
            }
        } catch (PermissionException | IdentifiantsInvalidesException $e) {
            $this->errorRedirect('/login', 'Identifiants invalides ou jeton de sécurité incorrect.');
        } catch (CompteBloqueException | CompteNonValideException $e) {
            $this->errorRedirect('/login', $e->getMessage());
        } catch (ValidationException $e) {
            $this->errorRedirect('/login', 'Veuillez remplir tous les champs requis.');
        } catch (\Exception $e) {
            // Logger l'erreur $e->getMessage() avec un LoggerService
            $this->errorRedirect('/login', 'Une erreur technique est survenue. Veuillez réessayer.');
        }
    }

    /**
     * Affiche le formulaire pour la saisie du code 2FA.
     */
    public function show2faForm(): void
    {
        if (empty($_SESSION['2fa_pending']) || empty($_SESSION['2fa_user_id'])) {
            $this->redirect('/login');
        }
        $this->render('Auth/form_2fa');
    }

    /**
     * Traite la soumission du code 2FA.
     */
    public function handle2faSubmission(): void
    {
        if (empty($_SESSION['2fa_pending']) || empty($_SESSION['2fa_user_id'])) {
            $this->redirect('/login');
        }

        if ($this->getMethod() !== 'POST') {
            $this->redirect('/2fa');
        }

        try {
            $this->verifyCsrfToken();
            $code = $this->post('code_2fa');
            $userId = $_SESSION['2fa_user_id'];

            $this->validator->validate(['code' => $code], ['code' => 'required|numeric|length:6']);

            if ($this->authService->verifierCodeAuthentificationDeuxFacteurs($userId, $code)) {
                $this->authService->finaliserConnexion($userId);
                $this->redirect('/dashboard');
            }
            throw new IdentifiantsInvalidesException('Code 2FA incorrect.');

        } catch (PermissionException | IdentifiantsInvalidesException $e) {
            $this->errorRedirect('/2fa', 'Code 2FA invalide ou jeton de sécurité incorrect.');
        } catch (ValidationException $e) {
            $this->errorRedirect('/2fa', 'Le code doit être composé de 6 chiffres.');
        } catch (\Exception $e) {
            // Logger l'erreur
            $this->errorRedirect('/login', 'Une erreur technique est survenue durant la vérification 2FA.');
        }
    }

    /**
     * Affiche le formulaire de demande de réinitialisation de mot de passe.
     */
    public function showForgotPasswordForm(): void
    {
        $this->_redirectIfLoggedIn();
        $this->render('Auth/forgot_password_form');
    }

    /**
     * Traite la demande de réinitialisation de mot de passe.
     */
    public function handleForgotPasswordRequest(): void
    {
        $this->_redirectIfLoggedIn();

        if ($this->getMethod() !== 'POST') {
            $this->redirect('/forgot-password');
        }

        try {
            $this->verifyCsrfToken();
            $email = $this->post('email');
            $this->validator->validate(['email' => $email], ['email' => 'required|email']);
            $this->authService->demanderReinitialisationMotDePasse($email);
        } catch (ValidationException $e) {
            $this->errorRedirect('/forgot-password', 'Veuillez fournir une adresse email valide.');
        } catch (PermissionException $e) {
            $this->errorRedirect('/forgot-password', 'Jeton de sécurité invalide.');
        } catch (EmailException $e) {
            // Logger l'erreur
            $this->errorRedirect('/forgot-password', 'Le service d\'envoi d\'emails est indisponible. Veuillez réessayer plus tard.');
        } catch (\Exception $e) {
            // Ne rien faire de spécifique pour ne pas révéler si l'email existe.
            // Logger l'erreur en silence.
        }

        // Message générique pour la sécurité
        $this->successRedirect('/login', 'Si un compte est associé à cet email, un lien de réinitialisation a été envoyé.');
    }

    /**
     * Affiche le formulaire pour saisir un nouveau mot de passe.
     */
    public function showResetPasswordForm(string $token): void
    {
        $this->_redirectIfLoggedIn();

        try {
            $this->authService->verifierValiditeTokenReset($token);
            $this->render('Auth/reset_password_form', ['token' => $token]);
        } catch (TokenInvalideException | TokenExpireException $e) {
            $this->errorRedirect('/forgot-password', $e->getMessage());
        } catch (\Exception $e) {
            // Logger l'erreur
            $this->errorRedirect('/login', 'Une erreur technique est survenue.');
        }
    }

    /**
     * Traite la soumission du nouveau mot de passe.
     */
    public function handleResetPasswordSubmission(): void
    {
        $this->_redirectIfLoggedIn();

        if ($this->getMethod() !== 'POST') {
            $this->redirect('/login');
        }

        try {
            $this->verifyCsrfToken();

            $token = $this->post('token');
            $password = $this->post('new_password');
            $confirm = $this->post('confirm_password');

            $this->validator->validate(
                ['password' => $password, 'confirm' => $confirm],
                ['password' => 'required|min:8', 'confirm' => 'required|same:password']
            );

            $this->authService->reinitialiserMotDePasseApresValidationToken($token, $password);
            $this->successRedirect('/login', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');

        } catch (PermissionException $e) {
            $this->errorRedirect('/forgot-password', 'Jeton de sécurité invalide.');
        } catch (ValidationException | MotDePasseInvalideException $e) {
            $this->errorRedirect("/reset-password/{$this->post('token')}", $e->getMessage());
        } catch (TokenInvalideException | TokenExpireException $e) {
            $this->errorRedirect('/forgot-password', $e->getMessage());
        } catch (\Exception $e) {
            // Logger l'erreur
            $this->errorRedirect('/login', 'Une erreur technique est survenue.');
        }
    }

    /**
     * Déconnecte l'utilisateur.
     */
    public function logout(): void
    {
        $this->authService->deconnexion();
        $this->successRedirect('/login', 'Vous avez été déconnecté avec succès.');
    }

    /**
     * Méthode utilitaire pour rediriger si l'utilisateur est déjà connecté.
     */
    private function _redirectIfLoggedIn(): void
    {
        if ($this->authService->estConnecte()) {
            $this->redirect('/dashboard');
        }
    }
}