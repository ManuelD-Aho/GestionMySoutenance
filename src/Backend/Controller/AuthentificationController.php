<?php

namespace App\Backend\Controller;

use PDO;
// ... (gardez tous vos autres 'use' statements existants)
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
        $tfaProvider = new TwoFactorAuth(
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
            $tfaProvider,
            $utilisateurModel,
            $historiqueMotDePasseModel,
            $etudiantModel,
            $enseignantModel,
            $personnelAdministratifModel
        );
    }

    protected function requireNoLogin(): void
    {
        echo "<p style='background: yellow; color: black; padding: 5px;'>DEBUG AuthentificationController: Entrée dans requireNoLogin().</p>";
        // La méthode estUtilisateurConnecteEtSessionValide() dans $this->authService contient déjà votre var_dump de $_SESSION
        if ($this->authService && $this->authService->estUtilisateurConnecteEtSessionValide()) {
            echo "<p style='background: orange; color: white; padding: 5px;'>DEBUG AuthentificationController: Dans requireNoLogin() - Condition VRAIE (utilisateur considéré connecté). Redirection vers /dashboard...</p>";
            header('Location: /dashboard');
            exit;
        } else {
            echo "<p style='background: lightgreen; color: black; padding: 5px;'>DEBUG AuthentificationController: Dans requireNoLogin() - Condition FAUSSE (utilisateur considéré NON connecté). PAS de redirection depuis requireNoLogin.</p>";
        }
    }

    public function showLoginForm(): void
    {
        echo "<p style='background: cyan; color: black; padding: 5px;'>DEBUG AuthentificationController: Entrée dans showLoginForm().</p>";
        $this->requireNoLogin(); // Appel à la méthode qui contient maintenant du débogage
        echo "<p style='background: cyan; color: black; padding: 5px;'>DEBUG AuthentificationController: Sortie de requireNoLogin(). Préparation du rendu pour Auth/login...</p>";

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
        // Si vous voyez ce message, cela signifie que render() a été appelé.
        // Si render() redirige ensuite, le problème est dans render() ou dans le layout.
        echo "<p style='background: cyan; color: black; padding: 5px;'>DEBUG AuthentificationController: Après appel à render('Auth/login') dans showLoginForm().</p>";
    }

    // ... Collez ici TOUTES les autres méthodes de votre AuthentificationController ...
    // (handleLogin, logout, handleValidateEmailToken, etc.)
    // Je vais ajouter les placeholders pour la structure, mais assurez-vous de mettre votre code complet.

    public function handleLogin(): void
    {
        // Votre code pour handleLogin
        $this->requireNoLogin();
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
                // Gérer la redirection vers 2FA. Le service devrait stocker l'état.
                $_SESSION['2fa_user_num_pending_verification'] = $e->getUserIdPending2FA(); // Assurez-vous que l'exception peut fournir cela
                $_SESSION['2fa_authentication_pending'] = true;
                $this->redirect('/login-2fa');
            }
            $this->setFlashMessage('login_error_message', $e->getMessage());
        } catch (IdentifiantsInvalidesException | CompteBloqueException | CompteNonValideException | UtilisateurNonTrouveException $e) {
            $this->setFlashMessage('login_error_message', $e->getMessage());
        } catch (PDOException $e) {
            error_log("PDOException dans handleLogin: " . $e->getMessage());
            $this->setFlashMessage('login_error_message', "Erreur de base de données. Veuillez réessayer.");
        } catch (\Exception $e) {
            error_log("Exception dans handleLogin: " . $e->getMessage());
            $this->setFlashMessage('login_error_message', "Une erreur inattendue s'est produite.");
        }

        $_SESSION['login_form_data']['identifiant'] = htmlspecialchars($identifiant);
        $this->redirect('/login');
    }

    public function logout(): void
    {
        echo "<p style='background: pink; color: black; padding: 5px;'>DEBUG AuthentificationController: Entrée dans logout().</p>";
        if ($this->authService) {
            $this->authService->terminerSessionUtilisateur(); // Cette méthode a aussi besoin de var_dump($_SESSION) avant et après session_destroy
        } else {
            session_start(); // Assurez-vous que la session est démarrée avant de la détruire
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            session_destroy();
            echo "<p style='background: pink; color: black; padding: 5px;'>DEBUG AuthentificationController: Session détruite manuellement dans logout (authService non dispo).</p>";
        }
        echo "<p style='background: pink; color: black; padding: 5px;'>DEBUG AuthentificationController: Redirection vers /login depuis logout().</p>";
        $this->redirect('/login');
    }

    public function handleValidateEmailToken(): void { /* Votre code */ }
    public function showForgotPasswordForm(): void { /* Votre code */ }
    public function handleForgotPasswordRequest(): void { /* Votre code */ }
    public function showResetPasswordForm(): void { /* Votre code */ }
    public function handleResetPasswordSubmission(): void { /* Votre code */ }

    public function show2FAForm(): void
    {
        if (!isset($_SESSION['2fa_user_num_pending_verification'])) {
            $this->setFlashMessage('login_error_message', "Aucune tentative de connexion 2FA en attente.");
            $this->redirect('/login');
        }
        $errorMessage = $this->getFlashMessage('2fa_error_message');
        $this->render('Auth/form_2fa', ['error' => $errorMessage, 'title' => 'Vérification 2FA']);
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
            // verifierCodeAuthentificationDeuxFacteurs devrait retourner l'objet utilisateur si valide
            $utilisateurObjet = $this->authService->verifierCodeAuthentificationDeuxFacteurs($numUser, $code2FA);

            $this->authService->demarrerSessionUtilisateur($utilisateurObjet);

            unset($_SESSION['2fa_user_num_pending_verification']);
            unset($_SESSION['2fa_authentication_pending']);


            $redirectUrl = $_SESSION['redirect_after_login'] ?? '/dashboard';
            unset($_SESSION['redirect_after_login']);
            $this->redirect($redirectUrl);

        } catch (MotDePasseInvalideException $e) {
            $this->setFlashMessage('2fa_error_message', $e->getMessage());
        } catch (UtilisateurNonTrouveException | OperationImpossibleException $e) {
            error_log("Erreur 2FA (handle2FASubmission): " . $e->getMessage());
            $this->setFlashMessage('2fa_error_message', "Erreur lors de la vérification 2FA.");
        } catch (\Exception $e) {
            error_log("Exception 2FA (handle2FASubmission): " . $e->getMessage());
            $this->setFlashMessage('2fa_error_message', "Une erreur inattendue s'est produite avec la 2FA.");
        }
        $this->redirect('/login-2fa');
    }

    public function showChangePasswordForm(): void { /* Votre code */ }
    public function handleChangePassword(): void { /* Votre code */ }
    public function showSetup2FAForm(): void { /* Votre code */ }
    public function handleActivate2FA(): void { /* Votre code */ }
    public function handleDisable2FA(): void { /* Votre code */ }

}
