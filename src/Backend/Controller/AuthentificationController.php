<?php
// src/Backend/Controller/AuthentificationController.php

namespace App\Backend\Controller;

use App\Backend\Service\Communication\ServiceCommunicationInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;
use App\Backend\Exception\{
    IdentifiantsInvalidesException,
    CompteBloqueException,
    CompteNonValideException,
    MotDePasseInvalideException,
    TokenInvalideException,
    TokenExpireException,
    OperationImpossibleException
};
use Exception;

class AuthentificationController extends BaseController
{
    private ServiceCommunicationInterface $communicationService;

    public function __construct(
        ServiceSecuriteInterface $securiteService,
        ServiceCommunicationInterface $communicationService,
        FormValidator $formValidator,
        ServiceSupervisionInterface $supervisionService
    ) {
        parent::__construct($securiteService, $supervisionService);
        $this->communicationService = $communicationService;
    }

    public function showLoginForm(): void
    {
        if ($this->securiteService->estUtilisateurConnecte()) {
            $this->redirect('/dashboard');
        }
        $this->render('Auth/auth', [
            'title' => 'Connexion',
            'form' => 'login',
            'csrf_token' => $this->generateCsrfToken('login_form')
        ], 'layout/layout_auth');
    }

    public function login(): void
    {
        if (!$this->isPostRequest() || !$this->validateCsrfToken('login_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/login');
            return; // Suppression de l'instruction inaccessible
        }

        try {
            $result = $this->securiteService->tenterConnexion($_POST['identifiant'] ?? '', $_POST['mot_de_passe'] ?? '');

            if ($result['status'] === '2fa_required') {
                $this->redirect('/2fa');
            } elseif ($result['status'] === 'success') {
                $this->addFlashMessage('success', 'Connexion réussie !');
                $this->redirect('/dashboard');
            }
        } catch (IdentifiantsInvalidesException | CompteBloqueException | CompteNonValideException $e) {
            $this->addFlashMessage($e instanceof CompteNonValideException ? 'warning' : 'error', $e->getMessage());
            $this->redirect('/login');
        } catch (Exception $e) {
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
        $this->render('Auth/auth', [
            'title' => 'Vérification 2FA',
            'form' => '2fa',
            'csrf_token' => $this->generateCsrfToken('2fa_form')
        ], 'layout/layout_auth');
    }

    public function handle2faVerification(): void
    {
        if (!$this->isPostRequest() || !isset($_SESSION['2fa_user_id']) || !$this->validateCsrfToken('2fa_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/login');
            return; // Suppression de l'instruction inaccessible
        }

        try {
            if ($this->securiteService->verifierCodeAuthentificationDeuxFacteurs($_SESSION['2fa_user_id'], $_POST['code_totp'] ?? '')) {
                $this->securiteService->demarrerSessionUtilisateur($_SESSION['2fa_user_id']);
                $this->addFlashMessage('success', 'Vérification 2FA réussie !');
                $this->redirect('/dashboard');
            } else {
                $this->addFlashMessage('error', 'Code 2FA incorrect. Veuillez réessayer.');
                $this->redirect('/2fa');
            }
        } catch (Exception $e) {
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
        $this->render('Auth/auth', [
            'title' => 'Mot de passe oublié',
            'form' => 'forgot_password',
            'csrf_token' => $this->generateCsrfToken('forgot_password_form')
        ], 'layout/layout_auth');
    }

    public function handleForgotPassword(): void
    {
        if (!$this->isPostRequest() || !$this->validateCsrfToken('forgot_password_form', $_POST['csrf_token'] ?? '')) {
            $this->redirect('/forgot-password');
            return; // Suppression de l'instruction inaccessible
        }
        try {
            $this->securiteService->demanderReinitialisationMotDePasse($_POST['email'] ?? '', $this->communicationService);
            $this->addFlashMessage('success', 'Si votre email est enregistré, un lien de réinitialisation a été envoyé.');
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Une erreur est survenue lors de la demande.');
            error_log("Erreur demande MDP oublié: " . $e->getMessage());
        }
        $this->redirect('/login');
    }

    public function showResetPasswordForm(string $token): void
    {
        $this->render('Auth/auth', [
            'title' => 'Réinitialiser le mot de passe',
            'form' => 'reset_password',
            'token' => $token,
            'csrf_token' => $this->generateCsrfToken('reset_password_form')
        ], 'layout/layout_auth');
    }

    public function handleResetPassword(string $token): void // <--- MODIFIEZ CETTE LIGNE (ajoutez "string $token")
    {
        if (!$this->isPostRequest()) {
            $this->redirect('/login');
            return;
        }

        $data = $this->getPostData(); // Gardez cette ligne pour récupérer les autres données du formulaire

        if (!$this->validateCsrfToken('reset_password_form', $data['csrf_token'] ?? '')) {
            $this->addFlashMessage('error', 'Token CSRF invalide.');
            $this->redirect('/reset-password/' . $token); // Utilisez $token de l'argument
            return;
        }

        if (($data['nouveau_mot_de_passe'] ?? '') !== ($data['confirmation_mot_de_passe'] ?? '')) {
            $this->addFlashMessage('error', 'Les mots de passe ne correspondent pas.');
            $this->redirect('/reset-password/' . $token); // Utilisez $token de l'argument
            return;
        }

        try {
            // Le $token vient maintenant directement de l'argument de la fonction
            $this->securiteService->reinitialiserMotDePasseViaToken($token, $data['nouveau_mot_de_passe']);
            $this->addFlashMessage('success', 'Votre mot de passe a été réinitialisé. Vous pouvez vous connecter.');
            $this->redirect('/login');
        } catch (TokenExpireException | TokenInvalideException $e) {
            $this->addFlashMessage('error', $e->getMessage());
            $this->redirect('/forgot-password');
        } catch (MotDePasseInvalideException $e) {
            $this->addFlashMessage('error', $e->getMessage());
            $this->redirect('/reset-password/' . $token); // Utilisez $token de l'argument
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Une erreur inattendue est survenue.');
            error_log("Erreur réinitialisation MDP: " . $e->getMessage());
            $this->redirect('/reset-password/' . $token); // Utilisez $token de l'argument
        }
    }

    public function validateEmail(string $token): void
    {
        try {
            $this->securiteService->validateEmailToken($token);
            $this->addFlashMessage('success', 'Votre adresse email a été validée ! Vous pouvez vous connecter.');
        } catch (TokenExpireException | TokenInvalideException | OperationImpossibleException $e) {
            $this->addFlashMessage($e instanceof OperationImpossibleException ? 'warning' : 'error', $e->getMessage());
        } catch (Exception $e) {
            $this->addFlashMessage('error', 'Une erreur est survenue lors de la validation.');
            error_log("Erreur validation email: " . $e->getMessage());
        }
        $this->redirect('/login');
    }
}