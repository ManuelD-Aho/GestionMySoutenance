<?php

namespace App\Backend\Controller;

use PDO;
use App\Backend\Service\Authentication\ServiceAuthenticationInterface;
use App\Backend\Service\Authentication\ServiceAuthentification;
use App\Backend\Model\Utilisateur as UtilisateurModel;
use App\Backend\Model\HistoriqueMotDePasse as HistoriqueMotDePasseModel;
use App\Backend\Model\Etudiant as EtudiantModel;
use App\Backend\Model\Enseignant as EnseignantModel;
use App\Backend\Model\PersonnelAdministratif as PersonnelAdministratifModel;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\Enregistrer;
use App\Backend\Model\Pister;
use App\Backend\Model\CompteRendu;
use App\Backend\Model\Inscrire;
use App\Backend\Model\Evaluer;
use App\Backend\Model\FaireStage;
use App\Backend\Model\Acquerir;
use App\Backend\Model\Occuper;
use App\Backend\Model\Attribuer;
use App\Backend\Service\Email\ServiceEmail;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin;
use App\Backend\Service\GestionAcademique\ServiceGestionAcademique;
use App\Backend\Service\GestionAcademique\ServiceGestionAcademiqueInterface;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Service\Permissions\ServicePermissionsInterface;
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
use RobThree\Auth\Algorithm;


class AuthentificationController extends BaseController
{
    protected ?ServiceAuthenticationInterface $authService;
    private ServiceSupervisionAdmin $serviceSupervision;
    private ServiceGestionAcademiqueInterface $serviceGestionAcademique;


    public function __construct()
    {
        parent::__construct();

        if (!$this->db instanceof PDO) {
            throw new \LogicException("La connexion PDO n'est pas disponible depuis BaseController dans AuthentificationController.");
        }
        $pdo = $this->db;

        // --- Instanciation pour ServiceSupervisionAdmin ---
        $rapportEtudiantModel = new RapportEtudiant($pdo);
        $enregistrerModel = new Enregistrer($pdo);
        $pisterModel = new Pister($pdo);
        $compteRenduModel = new CompteRendu($pdo);
        $this->serviceSupervision = new ServiceSupervisionAdmin(
            $rapportEtudiantModel,
            $enregistrerModel,
            $pisterModel,
            $compteRenduModel,
            $pdo
        );

        // --- Instanciation pour ServiceGestionAcademique ---
        $inscrireModel = new Inscrire($pdo);
        $evaluerModel = new Evaluer($pdo);
        $faireStageModel = new FaireStage($pdo);
        $acquerirModel = new Acquerir($pdo);
        $occuperModel = new Occuper($pdo);
        $attribuerModel = new Attribuer($pdo);

        $this->serviceGestionAcademique = new ServiceGestionAcademique(
            $inscrireModel,
            $evaluerModel,
            $faireStageModel,
            $acquerirModel,
            $occuperModel,
            $attribuerModel,
            $pdo
        );

        // --- Instanciation des autres modèles et services pour ServiceAuthentification ---
        $utilisateurModel = new UtilisateurModel($pdo);
        $historiqueMotDePasseModel = new HistoriqueMotDePasseModel($pdo);
        $etudiantModel = new EtudiantModel($pdo);
        $enseignantModel = new EnseignantModel($pdo);
        $personnelAdministratifModel = new PersonnelAdministratifModel($pdo);

        $serviceEmail = new ServiceEmail();
        $servicePermissions = new ServicePermissions($pdo, $this->serviceSupervision);

        $qrProvider = new BaconQrCodeProvider();
        $issuer = $_ENV['APP_NAME'] ?? 'GestionMySoutenance';
        $tfaInstance = $this->tfa ?? new TwoFactorAuth(
            $issuer,
            6,
            30,
            Algorithm::Sha1,
            $qrProvider
        );

        $this->authService = new ServiceAuthentification(
            $pdo,
            $serviceEmail,
            $this->serviceSupervision,
            $this->serviceGestionAcademique,
            $servicePermissions,
            $tfaInstance,
            $utilisateurModel,
            $historiqueMotDePasseModel,
            $etudiantModel,
            $enseignantModel,
            $personnelAdministratifModel
        );
    }

    protected function requireNoLogin(): void
    {
        // ---- DEBUT DEBUG (Commenté car headers already sent si activé ici) ----
        // echo "<p style='background: yellow; color: black; padding: 5px; border: 1px dashed red; margin: 2px;'>DEBUG AuthentificationController: Entrée dans requireNoLogin().</p>";
        // if ($this->authService && $this->authService->estUtilisateurConnecteEtSessionValide()) { // estUtilisateurConnecteEtSessionValide a ses propres echos commentés
        //     echo "<p style='background: orange; color: white; padding: 5px; border: 1px dashed red; margin: 2px;'>DEBUG AuthentificationController: Dans requireNoLogin() - Condition VRAIE. Redirection vers /dashboard...</p>";
        //     $this->redirect('/dashboard');
        // } else {
        //     echo "<p style='background: lightgreen; color: black; padding: 5px; border: 1px dashed red; margin: 2px;'>DEBUG AuthentificationController: Dans requireNoLogin() - Condition FAUSSE. PAS de redirection.</p>";
        // }
        // ---- FIN DEBUG ----
        // Logique normale sans les echos pour éviter "headers already sent"
        if ($this->authService && $this->authService->estUtilisateurConnecteEtSessionValide()) {
            $this->redirect('/dashboard');
        }
    }

    public function showLoginForm(): void
    {
        // ---- DEBUT DEBUG (Commenté) ----
        // echo "<p style='background: cyan; color: black; padding: 5px; border: 1px dashed blue; margin: 2px;'>DEBUG AuthentificationController: Entrée dans showLoginForm().</p>";
        $this->requireNoLogin();
        // echo "<p style='background: cyan; color: black; padding: 5px; border: 1px dashed blue; margin: 2px;'>DEBUG AuthentificationController: Sortie de requireNoLogin(). Préparation du rendu pour Auth/login...</p>";
        // ---- FIN DEBUG ----

        $errorMessage = $this->getFlashMessage('login_error_message');
        $loginData = $_SESSION['login_form_data'] ?? [];
        $successMessage = $this->getFlashMessage('login_message_succes');

        unset($_SESSION['login_form_data']);

        $this->render('Auth/login', [
            'error' => $errorMessage,
            'login_data' => $loginData,
            'success_message' => $successMessage,
            'title' => 'Connexion'
        ]);
        // ---- DEBUT DEBUG (Commenté) ----
        // echo "<p style='background: cyan; color: black; padding: 5px; border: 1px dashed blue; margin: 2px;'>DEBUG AuthentificationController: Après appel à render('Auth/login') dans showLoginForm().</p>";
        // ---- FIN DEBUG ----
    }

    public function handleLogin(): void
    {
        $this->requireNoLogin(); // Ne devrait pas rediriger si la session est vide
        $identifiant = $_POST['identifiant'] ?? '';
        $motDePasse = $_POST['mot_de_passe'] ?? '';

        if (empty($identifiant) || empty($motDePasse)) {
            $this->setFlashMessage('login_error_message', "L'identifiant et le mot de passe sont requis.");
            $_SESSION['login_form_data']['identifiant'] = htmlspecialchars($identifiant);
            $this->redirect('/login');
        }

        try {
            $utilisateurObjet = $this->authService->tenterConnexion($identifiant, $motDePasse);
            $this->authService->demarrerSessionUtilisateur($utilisateurObjet);

            $redirectUrl = $_SESSION['redirect_after_login'] ?? '/dashboard';
            unset($_SESSION['redirect_after_login']);
            $this->redirect($redirectUrl);

        } catch (AuthenticationException $e) {
            if ($e->getCode() === 1001) {
                $this->redirect('/login-2fa');
            }
            $this->setFlashMessage('login_error_message', $e->getMessage());
        } catch (IdentifiantsInvalidesException | CompteBloqueException | CompteNonValideException | UtilisateurNonTrouveException $e) {
            $this->setFlashMessage('login_error_message', $e->getMessage());
        } catch (PDOException $e) {
            error_log("PDOException dans handleLogin: " . $e->getMessage());
            $this->setFlashMessage('login_error_message', "Erreur de base de données. Veuillez réessayer.");
        } catch (\Exception $e) { // Capture toutes les autres exceptions
            error_log("Exception Générale dans handleLogin: " . $e->getMessage() . " dans " . $e->getFile() . " à la ligne " . $e->getLine() . "\nTrace: " . $e->getTraceAsString());
            // **** MODIFICATION POUR DÉBOGAGE ****
            // Affiche plus de détails sur l'erreur. À retirer en production.
            $detailedError = "Une erreur inattendue s'est produite.";
            if (($_ENV['APP_ENV'] ?? 'production') === 'development') { // Afficher plus de détails en mode développement
                $detailedError .= "<br><small>Détails : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') .
                    "<br>Fichier : " . htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8') .
                    " (ligne " . $e->getLine() . ")</small>";
            }
            $this->setFlashMessage('login_error_message', $detailedError);
            // **** FIN MODIFICATION POUR DÉBOGAGE ****
        }

        $_SESSION['login_form_data']['identifiant'] = htmlspecialchars($identifiant);
        $this->redirect('/login');
    }

    public function logout(): void
    {
        // ---- DEBUT DEBUG (Commenté) ----
        // echo "<p style='background: pink; color: black; padding: 5px; border: 1px dashed purple; margin: 2px;'>DEBUG AuthentificationController: Entrée dans logout().</p>";
        // ---- FIN DEBUG ----
        if ($this->authService) {
            $this->authService->terminerSessionUtilisateur();
        } else {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            // ---- DEBUT DEBUG (Commenté) ----
            /*
            echo "<pre style='background: pink; color: black; padding: 10px; margin: 10px;'>DEBUG AuthentificationController (logout fallback): \$_SESSION AVANT destruction:<br>";
            var_dump($_SESSION);
            echo "</pre>";
            */
            // ---- FIN DEBUG ----
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            session_destroy();
            // ---- DEBUT DEBUG (Commenté) ----
            /*
            echo "<pre style='background: lightcoral; color: white; padding: 10px; margin: 10px;'>DEBUG AuthentificationController (logout fallback): \$_SESSION APRES destruction:<br>";
            var_dump($_SESSION);
            echo "</pre>";
            */
            // ---- FIN DEBUG ----
        }
        // ---- DEBUT DEBUG (Commenté) ----
        // echo "<p style='background: pink; color: black; padding: 5px; border: 1px dashed purple; margin: 2px;'>DEBUG AuthentificationController: Redirection vers /login depuis logout().</p>";
        // ---- FIN DEBUG ----
        $this->redirect('/login');
    }

    public function handleValidateEmailToken(): void
    {
        $token = $_GET['token'] ?? '';
        $pageData = ['title' => "Validation d'Email"];

        if (empty($token)) {
            $pageData['success'] = false;
            $pageData['message'] = "Token de validation manquant ou invalide.";
            $this->render('Auth/email_validation_result', $pageData, 'layout/minimal'); // Utiliser un layout minimal pour cette page
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
        $this->render('Auth/email_validation_result', $pageData, 'layout/minimal'); // Utiliser un layout minimal
    }

    public function showForgotPasswordForm(): void
    {
        $this->requireNoLogin();
        $successMessage = $this->getFlashMessage('forgot_password_message_succes');
        $errorMessage = $this->getFlashMessage('forgot_password_message_erreur');
        $formData = $_SESSION['forgot_password_form_data'] ?? [];
        unset($_SESSION['forgot_password_form_data']);

        $this->render('Auth/forgot_password_form', [
            'success_message' => $successMessage,
            'error_message' => $errorMessage,
            'form_data' => $formData,
            'title' => 'Mot de Passe Oublié'
        ], 'layout/minimal'); // Utiliser un layout minimal
    }

    public function handleForgotPasswordRequest(): void
    {
        $this->requireNoLogin();
        $email = $_POST['email_principal'] ?? '';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlashMessage('forgot_password_message_erreur', "Veuillez fournir une adresse email valide.");
            $_SESSION['forgot_password_form_data']['email_principal'] = htmlspecialchars($email);
            $this->redirect('/forgot-password');
        }

        try {
            $this->authService->demanderReinitialisationMotDePasse($email);
            $this->setFlashMessage('forgot_password_message_succes', "Si un compte est associé à cet email, un lien de réinitialisation a été envoyé.");
        } catch (UtilisateurNonTrouveException | CompteNonValideException $e) {
            $this->setFlashMessage('forgot_password_message_succes', "Si un compte est associé à cet email, un lien de réinitialisation a été envoyé.");
        } catch (OperationImpossibleException | \Exception $e) {
            error_log("Erreur dans handleForgotPasswordRequest: " . $e->getMessage());
            $this->setFlashMessage('forgot_password_message_erreur', "Une erreur s'est produite lors de la demande. Veuillez réessayer.");
        }
        $this->redirect('/forgot-password');
    }

    public function showResetPasswordForm(): void
    {
        $this->requireNoLogin();
        $token = $_GET['token'] ?? '';
        $pageData = ['title' => 'Réinitialiser le Mot de Passe', 'token' => $token];
        $pageData['error_message'] = $this->getFlashMessage('reset_password_error');

        if (empty($token)) {
            $pageData['error_message'] = $pageData['error_message'] ?: "Token de réinitialisation manquant ou invalide.";
        } else {
            try {
                $this->authService->validerTokenReinitialisationMotDePasse($token);
                $_SESSION['reset_password_token_valide'] = $token;
            } catch (TokenInvalideException | TokenExpireException $e) {
                $pageData['error_message'] = $e->getMessage();
                $pageData['token'] = null;
            } catch (\Exception $e) {
                error_log("Erreur dans showResetPasswordForm: " . $e->getMessage());
                $pageData['error_message'] = "Une erreur inattendue s'est produite.";
                $pageData['token'] = null;
            }
        }
        $this->render('Auth/reset_password_form', $pageData, 'layout/minimal'); // Utiliser un layout minimal
    }

    public function handleResetPasswordSubmission(): void
    {
        $this->requireNoLogin();
        $token = $_POST['token'] ?? '';
        $nouveauMdp = $_POST['nouveau_mot_de_passe'] ?? '';
        $confirmerMdp = $_POST['confirmer_mot_de_passe'] ?? '';

        if (empty($token) || empty($_SESSION['reset_password_token_valide']) || $token !== $_SESSION['reset_password_token_valide']) {
            $this->setFlashMessage('login_error_message', "Session de réinitialisation invalide ou expirée. Veuillez refaire la demande.");
            unset($_SESSION['reset_password_token_valide']);
            $this->redirect('/forgot-password');
        }

        if (empty($nouveauMdp) || empty($confirmerMdp)) {
            $this->setFlashMessage('reset_password_error', "Tous les champs de mot de passe sont requis.");
            $this->redirect('/reset-password?token=' . urlencode($token));
        }

        if ($nouveauMdp !== $confirmerMdp) {
            $this->setFlashMessage('reset_password_error', "Les mots de passe ne correspondent pas.");
            $this->redirect('/reset-password?token=' . urlencode($token));
        }

        try {
            $this->authService->reinitialiserMotDePasseApresValidationToken($token, $nouveauMdp);
            $this->setFlashMessage('login_message_succes', "Votre mot de passe a été réinitialisé avec succès. Veuillez vous connecter.");
            unset($_SESSION['reset_password_token_valide']);
            $this->redirect('/login');
        } catch (ValidationException | MotDePasseInvalideException $e) {
            $this->setFlashMessage('reset_password_error', $e->getMessage());
        } catch (TokenInvalideException | TokenExpireException $e) {
            $this->setFlashMessage('reset_password_error', $e->getMessage());
        } catch (UtilisateurNonTrouveException $e){
            $this->setFlashMessage('reset_password_error', "Erreur: Utilisateur non trouvé pour ce token.");
        }catch (OperationImpossibleException | \Exception $e) {
            error_log("Erreur dans handleResetPasswordSubmission: " . $e->getMessage());
            $this->setFlashMessage('reset_password_error', "Une erreur inattendue s'est produite lors de la réinitialisation.");
        }
        $this->redirect('/reset-password?token=' . urlencode($token));
    }

    public function show2FAForm(): void
    {
        if (!isset($_SESSION['2fa_user_num_pending_verification'])) {
            $this->setFlashMessage('login_error_message', "Aucune tentative de connexion 2FA en attente.");
            $this->redirect('/login');
        }
        $errorMessage = $this->getFlashMessage('2fa_error_message');
        $this->render('Auth/form_2fa', ['error' => $errorMessage, 'title' => 'Vérification 2FA'], 'layout/minimal'); // Utiliser un layout minimal
    }

    public function handle2FASubmission(): void
    {
        if (!isset($_SESSION['2fa_user_num_pending_verification'])) {
            $this->setFlashMessage('login_error_message', "Session 2FA invalide ou expirée.");
            $this->redirect('/login');
        }
        $code2FA = $_POST['code_2fa'] ?? '';
        $numUser = $_SESSION['2fa_user_num_pending_verification'];

        if (empty($code2FA)) {
            $this->setFlashMessage('2fa_error_message', "Le code 2FA est requis.");
            $this->redirect('/login-2fa');
        }

        try {
            $isValid = $this->authService->verifierCodeAuthentificationDeuxFacteurs($numUser, $code2FA);

            if ($isValid) {
                $utilisateurObjet = $this->authService->recupererUtilisateurCompletParNumero($numUser);
                if (!$utilisateurObjet) {
                    throw new OperationImpossibleException("Impossible de récupérer l'utilisateur après vérification 2FA valide.");
                }
                $this->authService->demarrerSessionUtilisateur($utilisateurObjet);

                // Nettoyage des variables de session 2FA est maintenant dans demarrerSessionUtilisateur

                $redirectUrl = $_SESSION['redirect_after_login'] ?? '/dashboard';
                unset($_SESSION['redirect_after_login']);
                $this->redirect($redirectUrl);
            } else {
                // Ce bloc ne devrait pas être atteint si verifierCode... lève une exception pour code invalide.
                $this->setFlashMessage('2fa_error_message', "Code d'authentification à deux facteurs invalide (vérification interne).");
            }

        } catch (MotDePasseInvalideException $e) {
            $this->setFlashMessage('2fa_error_message', $e->getMessage());
        } catch (UtilisateurNonTrouveException | OperationImpossibleException $e) {
            error_log("Erreur 2FA (handle2FASubmission): " . $e->getMessage());
            $this->setFlashMessage('2fa_error_message', "Erreur lors de la vérification 2FA: " . $e->getMessage());
        } catch (\Exception $e) {
            error_log("Exception 2FA (handle2FASubmission): " . $e->getMessage());
            $this->setFlashMessage('2fa_error_message', "Une erreur inattendue s'est produite avec la 2FA.");
        }
        $this->redirect('/login-2fa');
    }

    public function showChangePasswordForm(): void
    {
        $this->requireLogin(); // Cette méthode utilise le layout 'app' par défaut
        $successMessage = $this->getFlashMessage('profile_message_succes');
        $errorMessage = $this->getFlashMessage('profile_error_message');

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
            $this->setFlashMessage('profile_error_message', "Tous les champs sont requis.");
            $this->redirect('/profile/change-password'); // La route définie dans web.php
        }
        if ($nouveauMdp !== $confirmerMdp) {
            $this->setFlashMessage('profile_error_message', "Les nouveaux mots de passe ne correspondent pas.");
            $this->redirect('/profile/change-password');
        }

        try {
            $this->authService->modifierMotDePasse($numUser, $nouveauMdp, $ancienMdp);
            $this->setFlashMessage('profile_message_succes', "Votre mot de passe a été changé avec succès.");
        } catch (ValidationException | MotDePasseInvalideException $e) {
            $this->setFlashMessage('profile_error_message', $e->getMessage());
        } catch (UtilisateurNonTrouveException | OperationImpossibleException | \Exception $e) {
            error_log("Erreur handleChangePassword: " . $e->getMessage());
            $this->setFlashMessage('profile_error_message', "Une erreur s'est produite lors du changement de mot de passe.");
        }
        $this->redirect('/profile/change-password');
    }

    public function showSetup2FAForm(): void
    {
        $this->requireLogin(); // Utilise le layout 'app'
        $numUser = $_SESSION['numero_utilisateur'];
        $pageData = ['title' => 'Configurer l\'Authentification à Deux Facteurs'];
        $pageData['error_message'] = $this->getFlashMessage('setup_2fa_error');
        $pageData['success_message'] = $this->getFlashMessage('setup_2fa_success');

        try {
            $utilisateur = $this->authService->recupererUtilisateurCompletParNumero($numUser);
            if (!$utilisateur) {
                throw new UtilisateurNonTrouveException("Utilisateur non trouvé pour la configuration 2FA.");
            }

            $is2FAActive = false;
            if (isset($utilisateur->preferences_2fa_active)) {
                if (is_string($utilisateur->preferences_2fa_active)) $is2FAActive = ($utilisateur->preferences_2fa_active === '1');
                elseif (is_int($utilisateur->preferences_2fa_active)) $is2FAActive = ($utilisateur->preferences_2fa_active === 1);
                elseif (is_bool($utilisateur->preferences_2fa_active)) $is2FAActive = $utilisateur->preferences_2fa_active;
            }
            $pageData['is_2fa_active'] = $is2FAActive;

            if (!$pageData['is_2fa_active']) {
                $pageData['qrCodeUri'] = $this->authService->genererEtStockerSecret2FA($numUser);
            }
        } catch (UtilisateurNonTrouveException $e) {
            error_log("Erreur showSetup2FAForm (UtilisateurNonTrouveException): " . $e->getMessage());
            $this->setFlashMessage('profile_error_message', "Utilisateur non trouvé. Impossible de configurer la 2FA."); // Message pour page de profil générale
            $this->redirect('/profile');
            return;
        }
        catch (OperationImpossibleException | \Exception $e) {
            error_log("Erreur showSetup2FAForm: " . $e->getMessage());
            $this->setFlashMessage('setup_2fa_error', "Erreur lors de la préparation de la configuration 2FA.");
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
            $this->setFlashMessage('setup_2fa_error', "Veuillez entrer le code d'authentification.");
            $this->redirect('/profile/setup-2fa');
        }

        try {
            $this->authService->activerAuthentificationDeuxFacteurs($numUser, $code2FA);
            $this->setFlashMessage('setup_2fa_success', "L'authentification à deux facteurs a été activée avec succès.");
        } catch (MotDePasseInvalideException $e) {
            $this->setFlashMessage('setup_2fa_error', $e->getMessage());
        } catch (UtilisateurNonTrouveException | OperationImpossibleException | \Exception $e) {
            error_log("Erreur handleActivate2FA: " . $e->getMessage());
            $this->setFlashMessage('setup_2fa_error', "Une erreur s'est produite lors de l'activation de la 2FA.");
        }
        $this->redirect('/profile/setup-2fa');
    }

    public function handleDisable2FA(): void
    {
        $this->requireLogin();
        $numUser = $_SESSION['numero_utilisateur'];
        try {
            $this->authService->desactiverAuthentificationDeuxFacteurs($numUser);
            $this->setFlashMessage('setup_2fa_success', "L'authentification à deux facteurs a été désactivée.");
        } catch (UtilisateurNonTrouveException | OperationImpossibleException | \Exception $e) {
            error_log("Erreur handleDisable2FA: " . $e->getMessage());
            $this->setFlashMessage('setup_2fa_error', "Erreur lors de la désactivation de la 2FA.");
        }
        $this->redirect('/profile/setup-2fa');
    }
}
