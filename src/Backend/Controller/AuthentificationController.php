<?php
// src/Backend/Controller/AuthentificationController.php

namespace App\Backend\Controller;

use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Communication\ServiceCommunicationInterface;
use App\Backend\Util\FormValidator;
use App\Config\Container;
use App\Backend\Exception\{
    IdentifiantsInvalidesException,
    CompteBloqueException,
    CompteNonValideException,
    TokenInvalideException,
    TokenExpireException,
    MotDePasseInvalideException
};

class AuthentificationController extends BaseController
{
    private FormValidator $validator;
    private ServiceCommunicationInterface $communicationService;

    public function __construct(
        Container $container,
        ServiceSecuriteInterface $serviceSecurite,
        FormValidator $validator,
        ServiceCommunicationInterface $communicationService
    ) {
        parent::__construct($container, $serviceSecurite);
        $this->validator = $validator;
        $this->communicationService = $communicationService;
    }

    public function showLoginForm(): void
    {
        if ($this->serviceSecurite->estUtilisateurConnecte()) {
            $this->redirect('/dashboard');
            return;
        }
        $this->render('Auth/auth.php', ['form' => 'login', 'flash' => $this->getFlashMessages()], 'auth');
    }

    public function handleLogin(): void
    {
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->setFlash('error', 'Erreur de sécurité. Veuillez réessayer.');
            $this->redirect('/login');
            return;
        }

        if (!$this->validator->validate($_POST, ['identifiant' => 'required', 'mot_de_passe' => 'required'])) {
            $this->setFlash('error', 'L\'identifiant et le mot de passe sont requis.');
            $this->redirect('/login');
            return;
        }

        try {
            $resultat = $this->serviceSecurite->tenterConnexion($_POST['identifiant'], $_POST['mot_de_passe']);

            if (isset($resultat['status']) && $resultat['status'] === '2fa_required') {
                $this->redirect('/login/2fa');
            } else {
                $this->redirect('/dashboard');
            }
        } catch (IdentifiantsInvalidesException | CompteNonValideException | CompteBloqueException $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/login');
        } catch (\Exception $e) {
            error_log("Login Error: " . $e->getMessage());
            $this->setFlash('error', 'Une erreur inattendue est survenue.');
            $this->redirect('/login');
        }
    }

    public function show2faForm(): void
    {
        if (!isset($_SESSION['2fa_pending']) || $_SESSION['2fa_pending'] !== true) {
            $this->redirect('/login');
            return;
        }
        $this->render('Auth/auth.php', ['form' => '2fa', 'flash' => $this->getFlashMessages()], 'auth');
    }

    public function handle2faVerification(): void
    {
        if (!isset($_SESSION['2fa_user_id']) || !$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->redirect('/login');
            return;
        }
        if (!$this->validator->validate($_POST, ['code_2fa' => 'required|numeric'])) {
            $this->setFlash('error', 'Le code de vérification est requis.');
            $this->redirect('/login/2fa');
            return;
        }

        $userId = $_SESSION['2fa_user_id'];
        $code = $_POST['code_2fa'];

        if ($this->serviceSecurite->verifierCodeAuthentificationDeuxFacteurs($userId, $code)) {
            $this->serviceSecurite->demarrerSessionUtilisateur($userId);
            $this->redirect('/dashboard');
        } else {
            $this->setFlash('error', 'Le code de vérification est incorrect.');
            $this->redirect('/login/2fa');
        }
    }

    public function logout(): void
    {
        $this->serviceSecurite->logout();
        $this->redirect('/login');
    }

    public function showForgotPasswordForm(): void
    {
        $this->render('Auth/auth.php', ['form' => 'forgot_password', 'flash' => $this->getFlashMessages()], 'auth');
    }

    public function handleForgotPassword(): void
    {
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->setFlash('error', 'Erreur de sécurité. Veuillez réessayer.');
            $this->redirect('/forgot-password');
            return;
        }

        if (!$this->validator->validate($_POST, ['email' => 'required|email'])) {
            $this->setFlash('error', 'Une adresse email valide est requise.');
            $this->redirect('/forgot-password');
            return;
        }

        try {
            $this->serviceSecurite->demanderReinitialisationMotDePasse($_POST['email'], $this->communicationService);
        } catch (\Exception $e) {
            error_log("Forgot Password Error: " . $e->getMessage());
        }

        $this->setFlash('success', 'Si un compte correspondant à cet email existe, un lien de réinitialisation a été envoyé.');
        $this->redirect('/forgot-password');
    }

    public function showResetPasswordForm(string $token): void
    {
        $this->render('Auth/auth.php', ['form' => 'reset_password', 'token' => $token, 'flash' => $this->getFlashMessages()], 'auth');
    }

    public function handleResetPassword(): void
    {
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->setFlash('error', 'Erreur de sécurité. Veuillez réessayer.');
            $this->redirect('/reset-password/' . ($_POST['token'] ?? ''));
            return;
        }

        $rules = [
            'token' => 'required',
            'nouveau_mot_de_passe' => 'required|min:8',
            'confirmer_mot_de_passe' => 'required|same:nouveau_mot_de_passe'
        ];

        if (!$this->validator->validate($_POST, $rules)) {
            $errors = $this->validator->getErrors();
            $this->setFlash('error', reset($errors));
            $this->redirect('/reset-password/' . $_POST['token']);
            return;
        }

        try {
            $this->serviceSecurite->reinitialiserMotDePasseViaToken($_POST['token'], $_POST['nouveau_mot_de_passe']);
            $this->setFlash('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
            $this->redirect('/login');
        } catch (TokenInvalideException | TokenExpireException | MotDePasseInvalideException $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/reset-password/' . $_POST['token']);
        } catch (\Exception $e) {
            error_log("Reset Password Error: " . $e->getMessage());
            $this->setFlash('error', 'Une erreur inattendue est survenue.');
            $this->redirect('/reset-password/' . $_POST['token']);
        }
    }
}