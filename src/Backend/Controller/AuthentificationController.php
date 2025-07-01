<?php
// src/Backend/Controller/AuthentificationController.php

namespace App\Backend\Controller;

use App\Config\Container;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Communication\ServiceCommunicationInterface;
use App\Backend\Exception\{IdentifiantsInvalidesException, CompteBloqueException, CompteNonValideException, MotDePasseInvalideException, TokenInvalideException, TokenExpireException, OperationImpossibleException}; // Ajout de OperationImpossibleException si nécessaire

class AuthentificationController extends BaseController
{
    // Supprimer les déclarations de propriétés si elles sont déjà 'protected' dans BaseController
    // private ServiceSecuriteInterface $securiteService; // <-- Supprimer cette ligne
    private ServiceCommunicationInterface $communicationService; // Garder si elle n'est pas dans BaseController

    public function __construct(Container $container)
    {
        parent::__construct($container); // Appelle le constructeur de BaseController qui initialise $this->securiteService
        // $this->securiteService = $container->get(ServiceSecuriteInterface::class); // <-- Supprimer cette ligne
        $this->communicationService = $container->get(ServiceCommunicationInterface::class);
    }

    public function showLoginForm(): void
    {
        if ($this->securiteService->estUtilisateurConnecte()) {
            $this->redirect('/dashboard');
        }
        $this->render('Auth/auth', ['title' => 'Connexion', 'csrf_token' => $this->generateCsrfToken('login_form')], 'layout/layout_auth');
    }

    public function handleLogin(): void
    {
        if (!$this->isPostRequest()) {
            $this->redirect('/login');
        }

        $data = $this->getPostData();

        if (!$this->validateCsrfToken('login_form', $data['csrf_token'] ?? '')) {
            $this->redirect('/login');
        }

        $identifiant = $data['identifiant'] ?? '';
        $motDePasse = $data['mot_de_passe'] ?? '';

        try {
            $result = $this->securiteService->tenterConnexion($identifiant, $motDePasse);

            if ($result['status'] === '2fa_required') {
                // L'ID utilisateur est déjà stocké en session par tenterConnexion si 2FA est requise
                $_SESSION['2fa_pending'] = true;
                $this->redirect('/2fa');
            } elseif ($result['status'] === 'success') {
                $this->addFlashMessage('success', 'Connexion réussie !');
                $this->redirect('/dashboard');
            }
        } catch (IdentifiantsInvalidesException $e) {
            $this->addFlashMessage('error', $e->getMessage());
            $this->redirect('/login');
        } catch (CompteBloqueException $e) {
            $this->addFlashMessage('error', $e->getMessage());
            $this->redirect('/login');
        } catch (CompteNonValideException $e) {
            $this->addFlashMessage('warning', $e->getMessage() . " Veuillez vérifier votre email pour valider votre compte.");
            $this->redirect('/login');
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'Une erreur inattendue est survenue. Veuillez réessayer.');
            error_log("Erreur de connexion: " . $e->getMessage());
            $this->redirect('/login');
        }
    }

    public function show2faForm(): void
    {
        if (!isset($_SESSION['2fa_pending']) || !$_SESSION['2fa_pending'] || !isset($_SESSION['2fa_user_id'])) {
            $this->redirect('/login');
        }
        $this->render('Auth/2fa', ['title' => 'Vérification 2FA', 'csrf_token' => $this->generateCsrfToken('2fa_form')], 'layout/layout_auth');
    }

    public function handle2faVerification(): void
    {
        if (!$this->isPostRequest() || !isset($_SESSION['2fa_user_id'])) {
            $this->redirect('/login');
        }

        $data = $this->getPostData();

        if (!$this->validateCsrfToken('2fa_form', $data['csrf_token'] ?? '')) {
            $this->redirect('/2fa');
        }

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
            $this->addFlashMessage('error', 'Erreur lors de la vérification 2FA. Veuillez réessayer.');
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
        $this->render('Auth/forgot_password', ['title' => 'Mot de passe oublié', 'csrf_token' => $this->generateCsrfToken('forgot_password_form')], 'layout/layout_auth');
    }

    public function handleForgotPassword(): void
    {
        if (!$this->isPostRequest()) {
            $this->redirect('/forgot-password');
        }

        $data = $this->getPostData();

        if (!$this->validateCsrfToken('forgot_password_form', $data['csrf_token'] ?? '')) {
            $this->redirect('/forgot-password');
        }

        $email = $data['email'] ?? '';

        try {
            $this->securiteService->demanderReinitialisationMotDePasse($email, $this->communicationService);
            $this->addFlashMessage('success', 'Si votre adresse email est enregistrée chez nous, un lien de réinitialisation vous a été envoyé.');
            $this->redirect('/login');
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'Une erreur est survenue lors de la demande de réinitialisation.');
            error_log("Erreur demande MDP oublié: " . $e->getMessage());
            $this->redirect('/forgot-password');
        }
    }

    public function showResetPasswordForm(string $token): void
    {
        $this->render('Auth/reset_password', ['title' => 'Réinitialiser le mot de passe', 'token' => $token, 'csrf_token' => $this->generateCsrfToken('reset_password_form')], 'layout/layout_auth');
    }

    public function handleResetPassword(): void
    {
        if (!$this->isPostRequest()) {
            $this->redirect('/login');
        }

        $data = $this->getPostData();

        if (!$this->validateCsrfToken('reset_password_form', $data['csrf_token'] ?? '')) {
            $this->redirect('/reset-password/' . ($data['token'] ?? ''));
        }

        $token = $data['token'] ?? '';
        $nouveauMotDePasse = $data['nouveau_mot_de_passe'] ?? '';
        $confirmationMotDePasse = $data['confirmation_mot_de_passe'] ?? '';

        if ($nouveauMotDePasse !== $confirmationMotDePasse) {
            $this->addFlashMessage('error', 'Les mots de passe ne correspondent pas.');
            $this->redirect('/reset-password/' . $token);
        }

        try {
            // Ordre des catch inversé pour capturer la plus spécifique en premier
            $this->securiteService->reinitialiserMotDePasseViaToken($token, $nouveauMotDePasse);
            $this->addFlashMessage('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
            $this->redirect('/login');
        } catch (TokenExpireException $e) { // Plus spécifique
            $this->addFlashMessage('error', $e->getMessage());
            $this->redirect('/forgot-password');
        } catch (TokenInvalideException $e) { // Moins spécifique
            $this->addFlashMessage('error', $e->getMessage());
            $this->redirect('/forgot-password');
        } catch (MotDePasseInvalideException $e) {
            $this->addFlashMessage('error', $e->getMessage());
            $this->redirect('/reset-password/' . $token);
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'Une erreur inattendue est survenue lors de la réinitialisation.');
            error_log("Erreur réinitialisation MDP: " . $e->getMessage());
            $this->redirect('/reset-password/' . $token);
        }
    }

    public function validateEmail(string $token): void
    {
        try {
            $this->securiteService->validateEmailToken($token);
            $this->addFlashMessage('success', 'Votre adresse email a été validée avec succès ! Vous pouvez maintenant vous connecter.');
            $this->redirect('/login');
        } catch (TokenExpireException $e) { // Plus spécifique
            $this->addFlashMessage('error', $e->getMessage() . " Le lien a expiré. Veuillez demander un nouveau lien de validation.");
            $this->redirect('/login');
        } catch (TokenInvalideException $e) { // Moins spécifique
            $this->addFlashMessage('error', $e->getMessage() . " Le lien est invalide ou a déjà été utilisé.");
            $this->redirect('/login');
        } catch (OperationImpossibleException $e) { // Si l'email est déjà validé
            $this->addFlashMessage('warning', $e->getMessage());
            $this->redirect('/login');
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'Une erreur est survenue lors de la validation de votre email.');
            error_log("Erreur validation email: " . $e->getMessage());
            $this->redirect('/login');
        }
    }
}