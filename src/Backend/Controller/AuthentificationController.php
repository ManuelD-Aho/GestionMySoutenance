<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Config\Container;
use App\Backend\Service\Interface\AuthenticationServiceInterface;
use App\Backend\Service\Interface\CompteUtilisateurServiceInterface;
use App\Backend\Service\Interface\PermissionsServiceInterface;
use App\Backend\Util\FormValidator;
use App\Backend\Exception\AuthenticationException;
use App\Backend\Exception\CompteBloqueException;
use App\Backend\Exception\CompteNonValideException;
use App\Backend\Exception\IdentifiantsInvalidesException;
use App\Backend\Exception\TokenExpireException;
use App\Backend\Exception\TokenInvalideException;
use App\Backend\Exception\MotDePasseInvalideException;

class AuthentificationController extends BaseController
{
    private CompteUtilisateurServiceInterface $compteUtilisateurService;
    private PermissionsServiceInterface $permissionsService;
    private FormValidator $validator;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->compteUtilisateurService = $container->get(CompteUtilisateurServiceInterface::class);
        $this->permissionsService = $container->get(PermissionsServiceInterface::class);
        $this->validator = $container->get(FormValidator::class);
    }

    public function showUnifiedAuthPage(string $token = null): void
    {
        if ($this->authService->estConnecte()) {
            $this->redirect('/dashboard');
        }

        $this->render('Auth/auth', [
            'page_title' => 'Authentification',
            'csrf_token' => $this->generateCsrfToken(),
            'reset_token' => $token
        ], 'layout_auth');
    }

    public function handleLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
        }

        $this->validator->validate($_POST, [
            'login_email' => 'required|string',
            'password' => 'required|string'
        ]);

        if (!$this->validator->isValid()) {
            $this->addFlashMessage('error', 'Veuillez remplir tous les champs.');
            $this->redirect('/login');
        }

        try {
            $user = $this->authService->tenterConnexion($_POST['login_email'], $_POST['password']);

            if (!empty($user['secret_2fa'])) {
                $_SESSION['pending_2fa_user_id'] = $user['numero_utilisateur'];
                $this->redirect('/2fa');
            }

            $this->completeLogin($user['numero_utilisateur']);
            $this->redirect('/dashboard');

        } catch (IdentifiantsInvalidesException | CompteBloqueException | CompteNonValideException $e) {
            $this->addFlashMessage('error', $e->getMessage());
            $this->redirect('/login');
        } catch (\Exception $e) {
            error_log("Login Error: " . $e->getMessage());
            $this->addFlashMessage('error', 'Une erreur inattendue est survenue.');
            $this->redirect('/login');
        }
    }

    public function handle2FASubmission(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['pending_2fa_user_id'])) {
            $this->redirect('/login');
        }

        $this->validator->validate($_POST, ['code_2fa' => 'required|numeric|length:6']);

        if (!$this->validator->isValid()) {
            $this->addFlashMessage('error', 'Le code de vérification est invalide.');
            $this->redirect('/2fa');
        }

        try {
            $userId = $_SESSION['pending_2fa_user_id'];
            if ($this->authService->verifierCode2FA($userId, $_POST['code_2fa'])) {
                unset($_SESSION['pending_2fa_user_id']);
                $this->completeLogin($userId);
                $this->redirect('/dashboard');
            }
        } catch (AuthenticationException $e) {
            $this->addFlashMessage('error', $e->getMessage());
            $this->redirect('/2fa');
        } catch (\Exception $e) {
            error_log("2FA Error: " . $e->getMessage());
            $this->addFlashMessage('error', 'Une erreur inattendue est survenue lors de la vérification 2FA.');
            $this->redirect('/2fa');
        }
    }

    public function logout(): void
    {
        $this->authService->logout();
        $this->redirect('/');
    }

    public function handleForgotPasswordRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/forgot-password');
        }

        $this->validator->validate($_POST, ['email_principal' => 'required|email']);

        if (!$this->validator->isValid()) {
            $this->addFlashMessage('error', 'Veuillez fournir une adresse email valide.');
            $this->redirect('/forgot-password');
        }

        $this->authService->demanderReinitialisationMotDePasse($_POST['email_principal']);
        $this->addFlashMessage('success', 'Si un compte est associé à cet email, un lien de réinitialisation a été envoyé.');
        $this->redirect('/login');
    }

    public function handleResetPasswordSubmission(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
        }

        $this->validator->validate($_POST, [
            'token' => 'required|string',
            'new_password' => 'required|minlength:8',
            'confirm_password' => 'required|same:new_password'
        ]);

        if (!$this->validator->isValid()) {
            $this->addFlashMessage('error', 'Les données fournies sont invalides. Assurez-vous que les mots de passe correspondent et font au moins 8 caractères.');
            $this->redirect('/reset-password/' . urlencode($_POST['token'] ?? ''));
        }

        try {
            $this->authService->reinitialiserMotDePasseAvecToken($_POST['token'], $_POST['new_password']);
            $this->addFlashMessage('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
            $this->redirect('/login');
        } catch (TokenExpireException | TokenInvalideException $e) {
            $this->addFlashMessage('error', $e->getMessage());
            $this->redirect('/forgot-password');
        } catch (\Exception $e) {
            error_log("Password Reset Error: " . $e->getMessage());
            $this->addFlashMessage('error', 'Une erreur inattendue est survenue.');
            $this->redirect('/forgot-password');
        }
    }

    public function validateEmail(string $token): void
    {
        try {
            $this->compteUtilisateurService->validerEmail($token);
            $this->render('Auth/email_validation_result', [
                'page_title' => 'Validation Réussie',
                'validation_status' => true,
                'message' => 'Votre adresse email a été validée avec succès. Vous pouvez maintenant vous connecter.'
            ], 'layout_auth');
        } catch (TokenInvalideException $e) {
            $this->render('Auth/email_validation_result', [
                'page_title' => 'Validation Échouée',
                'validation_status' => false,
                'message' => 'Le lien de validation est invalide ou a expiré. Veuillez en demander un nouveau.'
            ], 'layout_auth');
        }
    }

    public function showChangePasswordForm(): void
    {
        $this->checkAuthentication();
        $this->render('Auth/change_password_form', [
            'page_title' => 'Changer de mot de passe',
            'csrf_token' => $this->generateCsrfToken()
        ], 'layout_auth');
    }

    public function handleChangePassword(): void
    {
        $this->checkAuthentication();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/dashboard/profile/change-password');
        }

        $this->validator->validate($_POST, [
            'current_password' => 'required|string',
            'new_password' => 'required|minlength:8',
            'confirm_password' => 'required|same:new_password'
        ]);

        if (!$this->validator->isValid()) {
            $this->addFlashMessage('error', 'Les données fournies sont invalides.');
            $this->redirect('/dashboard/profile/change-password');
        }

        try {
            $userId = $_SESSION['user_id'];
            $this->compteUtilisateurService->changerMotDePasse($userId, $_POST['current_password'], $_POST['new_password']);
            $this->addFlashMessage('success', 'Votre mot de passe a été mis à jour avec succès.');
            $this->redirect('/dashboard');
        } catch (MotDePasseInvalideException $e) {
            $this->addFlashMessage('error', $e->getMessage());
            $this->redirect('/dashboard/profile/change-password');
        } catch (\Exception $e) {
            error_log("Password Change Error: " . $e->getMessage());
            $this->addFlashMessage('error', 'Une erreur inattendue est survenue.');
            $this->redirect('/dashboard/profile/change-password');
        }
    }

    private function completeLogin(string $userId): void
    {
        $this->authService->demarrerSessionUtilisateur($userId);
        $user = $this->compteUtilisateurService->listerComptes(['numero_utilisateur' => $userId])[0] ?? null;
        $permissions = $this->permissionsService->getPermissionsPourSession($userId);

        $_SESSION['user_permissions'] = $permissions;
        $_SESSION['user_data'] = $user;
    }
}