<?php
// src/Backend/Controller/AuthentificationController.php

namespace App\Backend\Controller;

use App\Backend\Service\Communication\ServiceCommunicationInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;
use App\Backend\Exception\IdentifiantsInvalidesException;
use App\Backend\Exception\CompteBloqueException;
use App\Backend\Exception\CompteNonValideException;
use App\Backend\Exception\TokenInvalideException;
use App\Backend\Exception\TokenExpireException;
use App\Backend\Exception\MotDePasseInvalideException;

class AuthentificationController extends BaseController
{
    private ServiceCommunicationInterface $serviceCommunication;

    public function __construct(
        ServiceSecuriteInterface $serviceSecurite,
        ServiceSupervisionInterface $serviceSupervision,
        FormValidator $formValidator,
        ServiceCommunicationInterface $serviceCommunication
    ) {
        parent::__construct($serviceSecurite, $serviceSupervision, $formValidator);
        $this->serviceCommunication = $serviceCommunication;
    }

    public function showLoginForm(): void
    {
        if ($this->serviceSecurite->estUtilisateurConnecte()) {
            $this->redirect('/dashboard');
        }

        $data = [
            'page' => 'login',
            'title' => 'Connexion',
            'pageTitle' => 'Bienvenue',
            'pageSubtitle' => 'Accédez à votre espace personnel',
            'alerts' => [],
            'loginValue' => ''
        ];

        if (!empty($_SESSION['error'])) {
            $data['alerts'][] = ['type' => 'error', 'message' => $_SESSION['error']];
            unset($_SESSION['error']);
        }
        if (!empty($_SESSION['success'])) {
            $data['alerts'][] = ['type' => 'success', 'message' => $_SESSION['success']];
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['old_input']['login']) && is_scalar($_SESSION['old_input']['login'])) {
            $data['loginValue'] = (string) $_SESSION['old_input']['login'];
        }
        unset($_SESSION['old_input']);

        $this->render('Auth/auth.php', $data, 'layout_auth.php');
    }

    public function handleLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['success' => false, 'message' => 'Requête invalide ou expirée.'], 403);
            return;
        }
        $identifiant = $_POST['login'] ?? '';
        $motDePasse = $_POST['password'] ?? '';
        try {
            $resultat = $this->serviceSecurite->tenterConnexion($identifiant, $motDePasse);
            if ($resultat['status'] === '2fa_required') {
                $this->jsonResponse(['success' => true, 'redirect' => '/verify-2fa']);
            } else {
                $this->jsonResponse(['success' => true, 'redirect' => '/dashboard']);
            }
        } catch (IdentifiantsInvalidesException | CompteBloqueException | CompteNonValideException $e) {
            $_SESSION['error'] = $e->getMessage();
            $_SESSION['old_input']['login'] = $identifiant;
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            $this->serviceSupervision->enregistrerAction('SYSTEM', 'LOGIN_EXCEPTION', null, null, ['error' => $e->getMessage()]);
            $this->jsonResponse(['success' => false, 'message' => 'Une erreur technique est survenue.'], 500);
        }
    }

    public function show2faForm(): void
    {
        if (!isset($_SESSION['2fa_pending']) || $_SESSION['2fa_pending'] !== true) {
            $this->redirect('/login');
        }
        $this->render('Auth/auth.php', ['page' => '2fa', 'title' => 'Vérification 2FA', 'pageTitle' => 'Vérification Requise', 'pageSubtitle' => 'Saisissez votre code'], 'layout_auth.php');
    }

    public function handle2faVerification(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['2fa_pending']) || !$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['success' => false, 'message' => 'Requête invalide ou expirée.'], 403);
            return;
        }
        $codeTOTP = $_POST['2fa_code'] ?? '';
        $userId = $_SESSION['2fa_user_id'];
        if ($this->serviceSecurite->verifierCodeAuthentificationDeuxFacteurs($userId, $codeTOTP)) {
            $this->serviceSecurite->demarrerSessionUtilisateur($userId);
            $this->jsonResponse(['success' => true, 'redirect' => '/dashboard']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Le code de vérification est incorrect.'], 401);
        }
    }

    public function logout(): void
    {
        $this->serviceSecurite->logout();
        $this->redirect('/login?action=logout');
    }

    public function showForgotPasswordForm(): void
    {
        $this->render('Auth/auth.php', ['page' => 'forgot-password', 'title' => 'Mot de passe oublié', 'pageTitle' => 'Récupération', 'pageSubtitle' => 'Réinitialisez votre mot de passe'], 'layout_auth.php');
    }

    public function handleForgotPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['success' => false, 'message' => 'Requête invalide ou expirée.'], 403);
            return;
        }
        $email = $_POST['email'] ?? '';
        $this->serviceSecurite->demanderReinitialisationMotDePasse($email, $this->serviceCommunication);
        $this->jsonResponse(['success' => true, 'message' => 'Si un compte est associé à cet email, un lien de réinitialisation a été envoyé.']);
    }

    public function showResetPasswordForm(string $token): void
    {
        $this->render('Auth/auth.php', ['page' => 'reset-password', 'title' => 'Réinitialiser le mot de passe', 'pageTitle' => 'Nouveau Mot de Passe', 'pageSubtitle' => 'Choisissez un nouveau mot de passe sécurisé', 'token' => $token], 'layout_auth.php');
    }

    public function handleResetPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['success' => false, 'message' => 'Requête invalide ou expirée.'], 403);
            return;
        }

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if ($password !== $passwordConfirm) {
            $this->jsonResponse(['success' => false, 'message' => 'Les mots de passe ne correspondent pas.'], 422);
            return;
        }

        try {
            $this->serviceSecurite->reinitialiserMotDePasseViaToken($token, $password);
            $this->jsonResponse(['success' => true, 'message' => 'Votre mot de passe a été réinitialisé avec succès.', 'redirect' => '/login']);
        } catch (TokenInvalideException | TokenExpireException | MotDePasseInvalideException $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->serviceSupervision->enregistrerAction('SYSTEM', 'RESET_PASSWORD_EXCEPTION', null, null, ['error' => $e->getMessage()]);
            $this->jsonResponse(['success' => false, 'message' => 'Une erreur technique est survenue.'], 500);
        }
    }
}