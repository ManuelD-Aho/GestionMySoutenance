<?php

namespace App\Backend\Controller;

use PDO;
use App\Config\Database;
use App\Backend\Service\Authentication\ServiceAuthenticationInterface;
use App\Backend\Service\Authentication\ServiceAuthentification;
use App\Backend\Model\Utilisateur as UtilisateurModel;
use App\Backend\Model\HistoriqueMotDePasse as HistoriqueMotDePasseModel;
use App\Backend\Model\Etudiant as EtudiantModel;
use App\Backend\Model\Enseignant as EnseignantModel;
use App\Backend\Model\PersonnelAdministratif as PersonnelAdministratifModel;
use App\Backend\Service\Email\ServiceEmail;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin;
use App\Backend\Service\GestionAcademique\ServiceGestionAcademique;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Exception\AuthenticationException;
use App\Backend\Exception\CompteBloqueException;
use App\Backend\Exception\IdentifiantsInvalidesException;
use App\Backend\Exception\TokenInvalideException;
use App\Backend\Exception\TokenExpireException;
use App\Backend\Exception\UtilisateurNonTrouveException;
use App\Backend\Exception\EmailNonValideException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\MotDePasseInvalideException;
use App\Backend\Exception\CompteNonValideException;
use App\Backend\Exception\ValidationException;
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;


class AuthentificationController extends BaseController
{
    private ServiceAuthenticationInterface $authService;

    public function __construct()
    {
        parent::__construct();
        $pdo = Database::getInstance()->getConnection();

        $utilisateurModel = new UtilisateurModel($pdo);
        $historiqueMotDePasseModel = new HistoriqueMotDePasseModel($pdo);
        $etudiantModel = new EtudiantModel($pdo);
        $enseignantModel = new EnseignantModel($pdo);
        $personnelAdministratifModel = new PersonnelAdministratifModel($pdo);

        $serviceEmail = new ServiceEmail();
        $serviceSupervision = new ServiceSupervisionAdmin($pdo);
        $serviceGestionAcademique = new ServiceGestionAcademique($pdo);
        $servicePermissions = new ServicePermissions($pdo, $serviceSupervision);

        $qrProvider = new BaconQrCodeProvider();
        $tfaProvider = new TwoFactorAuth('GestionMySoutenance', 6, 30, 'sha1', $qrProvider);


        $this->authService = new ServiceAuthentification(
            $pdo,
            $serviceEmail,
            $serviceSupervision,
            $serviceGestionAcademique,
            $servicePermissions,
            $tfaProvider,
            $utilisateurModel,
            $historiqueMotDePasseModel,
            $etudiantModel,
            $enseignantModel,
            $personnelAdministratifModel
        );
    }

    protected function requireLogin(): void
    {
        if (!$this->authService->estUtilisateurConnecteEtSessionValide()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            $_SESSION['error_message'] = "Vous devez être connecté pour accéder à cette page.";
            header('Location: /login');
            exit;
        }
    }

    protected function requireNoLogin(): void
    {
        if ($this->authService->estUtilisateurConnecteEtSessionValide()) {
            header('Location: /dashboard');
            exit;
        }
    }

    public function showLoginForm(): void
    {
        $this->requireNoLogin();
        $errorMessage = $_SESSION['login_error_message'] ?? null;
        $loginData = $_SESSION['login_form_data'] ?? [];
        $successMessage = $_SESSION['login_message_succes'] ?? null;

        unset($_SESSION['login_error_message'], $_SESSION['login_form_data'], $_SESSION['login_message_succes']);

        $this->render('Auth/login', [
            'error' => $errorMessage,
            'login_data' => $loginData,
            'success_message' => $successMessage,
            'title' => 'Connexion'
        ]);
    }

    public function handleLogin(): void
    {
        $this->requireNoLogin();
        $identifiant = $_POST['identifiant'] ?? '';
        $motDePasse = $_POST['mot_de_passe'] ?? '';

        if (empty($identifiant) || empty($motDePasse)) {
            $_SESSION['login_error_message'] = "L'identifiant et le mot de passe sont requis.";
            $_SESSION['login_form_data']['identifiant'] = htmlspecialchars($identifiant);
            header('Location: /login');
            exit;
        }

        try {
            $utilisateurObjet = $this->authService->tenterConnexion($identifiant, $motDePasse);
            $this->authService->demarrerSessionUtilisateur($utilisateurObjet);

            $redirectUrl = $_SESSION['redirect_after_login'] ?? '/dashboard';
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $redirectUrl);
            exit;
        } catch (AuthenticationException $e) {
            if ($e->getCode() === 1001) {
                header('Location: /login-2fa');
                exit;
            }
            $_SESSION['login_error_message'] = $e->getMessage();
        } catch (IdentifiantsInvalidesException | CompteBloqueException | CompteNonValideException | UtilisateurNonTrouveException $e) {
            $_SESSION['login_error_message'] = $e->getMessage();
        } catch (PDOException $e) {
            error_log("PDOException dans handleLogin: " . $e->getMessage());
            $_SESSION['login_error_message'] = "Erreur de base de données. Veuillez réessayer.";
        } catch (\Exception $e) {
            error_log("Exception dans handleLogin: " . $e->getMessage());
            $_SESSION['login_error_message'] = "Une erreur inattendue s'est produite.";
        }

        $_SESSION['login_form_data']['identifiant'] = htmlspecialchars($identifiant);
        header('Location: /login');
        exit;
    }

    public function logout(): void
    {
        $this->authService->terminerSessionUtilisateur();
        header('Location: /login');
        exit;
    }

    public function handleValidateEmailToken(): void
    {
        $token = $_GET['token'] ?? '';
        $pageData = ['title' => "Validation d'Email"];

        if (empty($token)) {
            $pageData['success'] = false;
            $pageData['message'] = "Token de validation manquant ou invalide.";
            $this->render('Auth/email_validation_result', $pageData);
            return;
        }

        try {
            $success = $this->authService->validerCompteEmailViaToken($token);
            $pageData['success'] = $success;
            $pageData['message'] = $success ? "Votre adresse email a été validée avec succès. Vous pouvez maintenant vous connecter." : "La validation de l'email a échoué. Le token est peut-être invalide ou déjà utilisé.";
        } catch (TokenInvalideException | TokenExpireException $e) {
            $pageData['success'] = false;
            $pageData['message'] = $e->getMessage();
        } catch (PDOException $e) {
            error_log("PDOException dans handleValidateEmailToken: " . $e->getMessage());
            $pageData['success'] = false;
            $pageData['message'] = "Une erreur de base de données s'est produite lors de la validation de l'email.";
        } catch (\Exception $e) {
            error_log("Exception dans handleValidateEmailToken: " . $e->getMessage());
            $pageData['success'] = false;
            $pageData['message'] = "Une erreur inattendue s'est produite lors de la validation de l'email.";
        }
        $this->render('Auth/email_validation_result', $pageData);
    }

    public function showForgotPasswordForm(): void
    {
        $this->requireNoLogin();
        $successMessage = $_SESSION['forgot_password_message_succes'] ?? null;
        $errorMessage = $_SESSION['forgot_password_message_erreur'] ?? null;
        $formData = $_SESSION['forgot_password_form_data'] ?? [];

        unset($_SESSION['forgot_password_message_succes'], $_SESSION['forgot_password_message_erreur'], $_SESSION['forgot_password_form_data']);

        $this->render('Auth/forgot_password_form', [
            'success_message' => $successMessage,
            'error_message' => $errorMessage,
            'form_data' => $formData,
            'title' => 'Mot de Passe Oublié'
        ]);
    }

    public function handleForgotPasswordRequest(): void
    {
        $this->requireNoLogin();
        $email = $_POST['email_principal'] ?? '';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['forgot_password_message_erreur'] = "Veuillez fournir une adresse email valide.";
            $_SESSION['forgot_password_form_data']['email_principal'] = htmlspecialchars($email);
            header('Location: /forgot-password');
            exit;
        }

        try {
            $this->authService->demanderReinitialisationMotDePasse($email);
            $_SESSION['forgot_password_message_succes'] = "Si un compte est associé à cet email, un lien de réinitialisation a été envoyé.";
        } catch (UtilisateurNonTrouveException | CompteNonValideException $e) {
            $_SESSION['forgot_password_message_succes'] = "Si un compte est associé à cet email, un lien de réinitialisation a été envoyé (même si l'utilisateur n'est pas trouvé ou le compte non valide, pour ne pas donner d'indices).";
        } catch (OperationImpossibleException | \Exception $e) {
            error_log("Erreur dans handleForgotPasswordRequest: " . $e->getMessage());
            $_SESSION['forgot_password_message_erreur'] = "Une erreur s'est produite lors de la demande. Veuillez réessayer.";
        }
        header('Location: /forgot-password');
        exit;
    }

    public function showResetPasswordForm(): void
    {
        $this->requireNoLogin();
        $token = $_GET['token'] ?? '';
        $pageData = ['title' => 'Réinitialiser le Mot de Passe', 'token' => $token];

        if (empty($token)) {
            $pageData['error_message'] = "Token de réinitialisation manquant ou invalide.";
            $this->render('Auth/reset_password_form', $pageData);
            return;
        }

        try {
            $this->authService->validerTokenReinitialisationMotDePasse($token);
            $_SESSION['reset_password_token_valide'] = $token;
        } catch (TokenInvalideException | TokenExpireException $e) {
            $pageData['error_message'] = $e->getMessage();
        } catch (\Exception $e) {
            error_log("Erreur dans showResetPasswordForm: " . $e->getMessage());
            $pageData['error_message'] = "Une erreur inattendue s'est produite.";
        }
        $this->render('Auth/reset_password_form', $pageData);
    }

    public function handleResetPasswordSubmission(): void
    {
        $this->requireNoLogin();
        $token = $_POST['token'] ?? '';
        $nouveauMdp = $_POST['nouveau_mot_de_passe'] ?? '';
        $confirmerMdp = $_POST['confirmer_mot_de_passe'] ?? '';

        if (empty($token) || empty($_SESSION['reset_password_token_valide']) || $token !== $_SESSION['reset_password_token_valide']) {
            $_SESSION['login_error_message'] = "Session de réinitialisation invalide ou expirée. Veuillez refaire la demande.";
            unset($_SESSION['reset_password_token_valide']);
            header('Location: /forgot-password');
            exit;
        }

        if (empty($nouveauMdp) || empty($confirmerMdp)) {
            $_SESSION['reset_password_error'] = "Tous les champs de mot de passe sont requis.";
            header('Location: /reset-password?token=' . urlencode($token));
            exit;
        }

        if ($nouveauMdp !== $confirmerMdp) {
            $_SESSION['reset_password_error'] = "Les mots de passe ne correspondent pas.";
            header('Location: /reset-password?token=' . urlencode($token));
            exit;
        }

        try {
            $this->authService->reinitialiserMotDePasseApresValidationToken($token, $nouveauMdp);
            $_SESSION['login_message_succes'] = "Votre mot de passe a été réinitialisé avec succès. Veuillez vous connecter.";
            unset($_SESSION['reset_password_token_valide']);
            header('Location: /login');
            exit;
        } catch (ValidationException | MotDePasseInvalideException $e) {
            $_SESSION['reset_password_error'] = $e->getMessage();
        } catch (TokenInvalideException | TokenExpireException $e) {
            $_SESSION['reset_password_error'] = $e->getMessage();
        } catch (UtilisateurNonTrouveException $e){
            $_SESSION['reset_password_error'] = "Erreur: Utilisateur non trouvé pour ce token.";
        }catch (OperationImpossibleException | \Exception $e) {
            error_log("Erreur dans handleResetPasswordSubmission: " . $e->getMessage());
            $_SESSION['reset_password_error'] = "Une erreur inattendue s'est produite lors de la réinitialisation.";
        }
        header('Location: /reset-password?token=' . urlencode($token));
        exit;
    }

    public function show2FAForm(): void
    {
        if (!isset($_SESSION['2fa_authentication_pending']) || !isset($_SESSION['2fa_user_num_pending_verification'])) {
            header('Location: /login');
            exit;
        }
        $errorMessage = $_SESSION['2fa_error_message'] ?? null;
        unset($_SESSION['2fa_error_message']);
        $this->render('Auth/form_2fa', ['error' => $errorMessage, 'title' => 'Vérification 2FA']);
    }

    public function handle2FASubmission(): void
    {
        if (!isset($_SESSION['2fa_authentication_pending']) || !isset($_SESSION['2fa_user_num_pending_verification'])) {
            header('Location: /login');
            exit;
        }
        $code2FA = $_POST['code_2fa'] ?? '';
        $numUser = $_SESSION['2fa_user_num_pending_verification'];

        if (empty($code2FA)) {
            $_SESSION['2fa_error_message'] = "Le code 2FA est requis.";
            header('Location: /login-2fa');
            exit;
        }

        try {
            $isValid = $this->authService->verifierCodeAuthentificationDeuxFacteurs($numUser, $code2FA);
            if ($isValid) {
                $userComplet = $this->authService->recupererUtilisateurCompletParNumero($numUser);
                if(!$userComplet) {
                    throw new OperationImpossibleException("Impossible de charger l'utilisateur après vérification 2FA.");
                }
                $this->authService->demarrerSessionUtilisateur($userComplet);

                $redirectUrl = $_SESSION['redirect_after_login'] ?? '/dashboard';
                unset($_SESSION['redirect_after_login']);
                header('Location: ' . $redirectUrl);
                exit;
            }
        } catch (MotDePasseInvalideException $e) {
            $_SESSION['2fa_error_message'] = $e->getMessage();
        } catch (UtilisateurNonTrouveException | OperationImpossibleException $e) {
            error_log("Erreur 2FA (handle2FASubmission): " . $e->getMessage());
            $_SESSION['2fa_error_message'] = "Erreur lors de la vérification 2FA.";
        } catch (\Exception $e) {
            error_log("Exception 2FA (handle2FASubmission): " . $e->getMessage());
            $_SESSION['2fa_error_message'] = "Une erreur inattendue s'est produite avec la 2FA.";
        }
        header('Location: /login-2fa');
        exit;
    }

    public function showChangePasswordForm(): void
    {
        $this->requireLogin();
        $successMessage = $_SESSION['profile_message_succes'] ?? null;
        $errorMessage = $_SESSION['profile_error_message'] ?? null;
        unset($_SESSION['profile_message_succes'], $_SESSION['profile_error_message']);

        $this->render('Profile/change_password_form', [
            'success_message' => $successMessage,
            'error_message' => $errorMessage,
            'title' => 'Changer Votre Mot de Passe'
        ]);
    }

    public function handleChangePassword(): void
    {
        $this->requireLogin();
        $numUser = $_SESSION['numero_utilisateur'];
        $ancienMdp = $_POST['ancien_mot_de_passe'] ?? '';
        $nouveauMdp = $_POST['nouveau_mot_de_passe'] ?? '';
        $confirmerMdp = $_POST['confirmer_nouveau_mot_de_passe'] ?? '';

        if (empty($ancienMdp) || empty($nouveauMdp) || empty($confirmerMdp)) {
            $_SESSION['profile_error_message'] = "Tous les champs sont requis.";
            header('Location: /profile/change-password');
            exit;
        }
        if ($nouveauMdp !== $confirmerMdp) {
            $_SESSION['profile_error_message'] = "Les nouveaux mots de passe ne correspondent pas.";
            header('Location: /profile/change-password');
            exit;
        }

        try {
            $this->authService->modifierMotDePasse($numUser, $nouveauMdp, $ancienMdp);
            $_SESSION['profile_message_succes'] = "Votre mot de passe a été changé avec succès.";
        } catch (ValidationException | MotDePasseInvalideException $e) {
            $_SESSION['profile_error_message'] = $e->getMessage();
        } catch (UtilisateurNonTrouveException | OperationImpossibleException | \Exception $e) {
            error_log("Erreur handleChangePassword: " . $e->getMessage());
            $_SESSION['profile_error_message'] = "Une erreur s'est produite lors du changement de mot de passe.";
        }
        header('Location: /profile/change-password');
        exit;
    }

    public function showSetup2FAForm(): void
    {
        $this->requireLogin();
        $numUser = $_SESSION['numero_utilisateur'];
        $pageData = ['title' => 'Configurer l\'Authentification à Deux Facteurs'];
        $errorMessage = $_SESSION['setup_2fa_error'] ?? null;
        unset($_SESSION['setup_2fa_error']);
        $pageData['error_message'] = $errorMessage;

        try {
            $utilisateur = $this->authService->recupererUtilisateurCompletParNumero($numUser);
            if ($utilisateur && ($utilisateur->preferences_2fa_active == 1 || $utilisateur->preferences_2fa_active === true)) {
                $pageData['is_2fa_active'] = true;
            } else {
                $pageData['is_2fa_active'] = false;
                $pageData['qrCodeUri'] = $this->authService->genererEtStockerSecret2FA($numUser);
                // Le secret base32 est dans l'URI, pas besoin de le passer séparément si le QR code est la seule méthode.
                // Si vous voulez afficher le secret en texte : $secret = $this->authService->getSecretForUser($numUser); (méthode à ajouter)
            }
        } catch (UtilisateurNonTrouveException | OperationImpossibleException | \Exception $e) {
            error_log("Erreur showSetup2FAForm: " . $e->getMessage());
            $pageData['error_message'] = "Erreur lors de la préparation de la configuration 2FA.";
            $pageData['qrCodeUri'] = null;
        }
        $this->render('Profile/setup_2fa_form', $pageData);
    }

    public function handleActivate2FA(): void
    {
        $this->requireLogin();
        $numUser = $_SESSION['numero_utilisateur'];
        $code2FA = $_POST['code_2fa'] ?? '';

        if (empty($code2FA)) {
            $_SESSION['setup_2fa_error'] = "Veuillez entrer le code d'authentification.";
            header('Location: /profile/setup-2fa');
            exit;
        }

        try {
            $this->authService->activerAuthentificationDeuxFacteurs($numUser, $code2FA);
            $_SESSION['profile_message_succes'] = "L'authentification à deux facteurs a été activée avec succès.";
            header('Location: /profile');
            exit;
        } catch (MotDePasseInvalideException $e) {
            $_SESSION['setup_2fa_error'] = $e->getMessage();
        } catch (UtilisateurNonTrouveException | OperationImpossibleException | \Exception $e) {
            error_log("Erreur handleActivate2FA: " . $e->getMessage());
            $_SESSION['setup_2fa_error'] = "Une erreur s'est produite lors de l'activation de la 2FA.";
        }
        header('Location: /profile/setup-2fa');
        exit;
    }

    public function handleDisable2FA(): void
    {
        $this->requireLogin();
        $numUser = $_SESSION['numero_utilisateur'];
        // Optionnel: demander le mot de passe ou un code 2FA pour confirmer la désactivation
        // $motDePasseConfirmation = $_POST['mot_de_passe_confirmation_disable_2fa'] ?? '';
        // if(!$this->authService->verifierMotDePasseActuel($numUser, $motDePasseConfirmation)) { ... }

        try {
            $this->authService->desactiverAuthentificationDeuxFacteurs($numUser);
            $_SESSION['profile_message_succes'] = "L'authentification à deux facteurs a été désactivée.";
        } catch (UtilisateurNonTrouveException | OperationImpossibleException | \Exception $e) {
            error_log("Erreur handleDisable2FA: " . $e->getMessage());
            $_SESSION['profile_error_message'] = "Erreur lors de la désactivation de la 2FA.";
        }
        header('Location: /profile/setup-2fa');
        exit;
    }
}