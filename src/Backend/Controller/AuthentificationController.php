<?php
// src/Backend/Controller/AuthentificationController.php

namespace App\Backend\Controller;

use App\Config\Container;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Communication\ServiceCommunicationInterface;
use App\Backend\Exception\{IdentifiantsInvalidesException, CompteBloqueException, CompteNonValideException, MotDePasseInvalideException, TokenInvalideException, TokenExpireException, OperationImpossibleException};

class AuthentificationController extends BaseController
{
    private ServiceCommunicationInterface $communicationService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->communicationService = $container->get(ServiceCommunicationInterface::class);
    }

    public function showLoginForm(): void
    {
        if ($this->securiteService->estUtilisateurConnecte()) {
            $this->redirect('/dashboard');
        }
        // ✅ CORRECTION : Ajout de la variable 'form' => 'login'
        $data = [
            'title' => 'Connexion',
            'form' => 'login',
            'csrf_token' => $this->generateCsrfToken('login_form')
        ];
        $this->render('Auth/auth', $data, 'layout/layout_auth');
    }

    public function handleLogin(): void
    {
        if (!$this->isPostRequest()) $this->redirect('/login');
        $data = $this->getPostData();
        if (!$this->validateCsrfToken('login_form', $data['csrf_token'] ?? '')) $this->redirect('/login');

        $identifiant = $data['identifiant'] ?? '';
        $motDePasse = $data['mot_de_passe'] ?? '';

        try {
            $result = $this->securiteService->tenterConnexion($identifiant, $motDePasse);
            if ($result['status'] === '2fa_required') {
                $_SESSION['2fa_pending'] = true;
                $this->redirect('/2fa');
            } elseif ($result['status'] === 'success') {
                $this->addFlashMessage('success', 'Connexion réussie !');
                $this->redirect('/dashboard');
            }
        } catch (IdentifiantsInvalidesException | CompteBloqueException | CompteNonValideException $e) {
            $this->addFlashMessage($e instanceof CompteNonValideException ? 'warning' : 'error', $e->getMessage());
            $this->redirect('/login');
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'Une erreur inattendue est survenue.');
            error_log("Erreur de connexion: " . $e->getMessage());
            $this->redirect('/login');
        }
    }

    public function show2faForm(): void
    {
        if (!isset($_SESSION['2fa_pending']) || !$_SESSION['2fa_pending'] || !isset($_SESSION['2fa_user_id'])) {
            $this->redirect('/login');
        }
        // ✅ CORRECTION : Ajout de la variable 'form' => '2fa'
        $data = [
            'title' => 'Vérification 2FA',
            'form' => '2fa',
            'csrf_token' => $this->generateCsrfToken('2fa_form')
        ];
        $this->render('Auth/auth', $data, 'layout/layout_auth');
    }

    public function handle2faVerification(): void
    {
        if (!$this->isPostRequest() || !isset($_SESSION['2fa_user_id'])) $this->redirect('/login');
        $data = $this->getPostData();
        if (!$this->validateCsrfToken('2fa_form', $data['csrf_token'] ?? '')) $this->redirect('/2fa');

        $codeTOTP = $data['code_totp'] ?? '';
        $userId = $_SESSION['2fa_user_id'];

        try {
            if ($this->securiteService->verifierCodeAuthentificationDeuxFacteurs($userId, $codeTOTP)) {
                $this->securiteService->demarrerSessionUtilisateur($userId);
                $this->addFlashMessage('success', 'Vérification 2FA réussie !');
                $this->redirect('/dashboard');
            } else {
                $this->addFlashMessage('error', 'Code 2FA incorrect. Veuillez réessayer.');
                $this->redirect('/2fa');
            }
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'Erreur lors de la vérification 2FA.');
            error_log("Erreur 2FA: " . $e->getMessage());
            $this->redirect('/2fa');
        }
    }

    public function logout(): void
    {
        $this->securiteService->logout();
        $this->addFlashMessage('info', 'Vous avez été déconnecté.');
        $this->redirect('/login');
    }

    public function showForgotPasswordForm(): void
    {
        // ✅ CORRECTION : Ajout de la variable 'form' => 'forgot_password'
        $data = [
            'title' => 'Mot de passe oublié',
            'form' => 'forgot_password',
            'csrf_token' => $this->generateCsrfToken('forgot_password_form')
        ];
        $this->render('Auth/auth', $data, 'layout/layout_auth');
    }

    public function handleForgotPassword(): void
    {
        if (!$this->isPostRequest()) $this->redirect('/forgot-password');
        $data = $this->getPostData();
        if (!$this->validateCsrfToken('forgot_password_form', $data['csrf_token'] ?? '')) $this->redirect('/forgot-password');

        $email = $data['email'] ?? '';
        try {
            $this->securiteService->demanderReinitialisationMotDePasse($email, $this->communicationService);
            $this->addFlashMessage('success', 'Si votre email est enregistré, un lien de réinitialisation a été envoyé.');
            $this->redirect('/login');
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'Une erreur est survenue.');
            error_log("Erreur demande MDP oublié: " . $e->getMessage());
            $this->redirect('/forgot-password');
        }
    }

    public function showResetPasswordForm(string $token): void
    {
        // ✅ CORRECTION : Ajout de la variable 'form' => 'reset_password'
        $data = [
            'title' => 'Réinitialiser le mot de passe',
            'form' => 'reset_password',
            'token' => $token,
            'csrf_token' => $this->generateCsrfToken('reset_password_form')
        ];
        $this->render('Auth/auth', $data, 'layout/layout_auth');
    }

    public function handleResetPassword(): void
    {
        if (!$this->isPostRequest()) $this->redirect('/login');
        $data = $this->getPostData();
        $token = $data['token'] ?? '';
        if (!$this->validateCsrfToken('reset_password_form', $data['csrf_token'] ?? '')) $this->redirect('/reset-password/' . $token);

        $nouveauMotDePasse = $data['nouveau_mot_de_passe'] ?? '';
        $confirmationMotDePasse = $data['confirmation_mot_de_passe'] ?? '';

        if ($nouveauMotDePasse !== $confirmationMotDePasse) {
            $this->addFlashMessage('error', 'Les mots de passe ne correspondent pas.');
            $this->redirect('/reset-password/' . $token);
        }

        try {
            $this->securiteService->reinitialiserMotDePasseViaToken($token, $nouveauMotDePasse);
            $this->addFlashMessage('success', 'Votre mot de passe a été réinitialisé. Vous pouvez vous connecter.');
            $this->redirect('/login');
        } catch (TokenExpireException | TokenInvalideException $e) {
            $this->addFlashMessage('error', $e->getMessage());
            $this->redirect('/forgot-password');
        } catch (MotDePasseInvalideException $e) {
            $this->addFlashMessage('error', $e->getMessage());
            $this->redirect('/reset-password/' . $token);
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'Une erreur inattendue est survenue.');
            error_log("Erreur réinitialisation MDP: " . $e->getMessage());
            $this->redirect('/reset-password/' . $token);
        }
    }

    public function validateEmail(string $token): void
    {
        try {
            $this->securiteService->validateEmailToken($token);
            $this->addFlashMessage('success', 'Votre adresse email a été validée ! Vous pouvez vous connecter.');
            $this->redirect('/login');
        } catch (TokenExpireException | TokenInvalideException | OperationImpossibleException $e) {
            $this->addFlashMessage($e instanceof OperationImpossibleException ? 'warning' : 'error', $e->getMessage());
            $this->redirect('/login');
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'Une erreur est survenue lors de la validation.');
            error_log("Erreur validation email: " . $e->getMessage());
            $this->redirect('/login');
        }
    }
}