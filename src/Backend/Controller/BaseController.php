<?php

namespace App\Backend\Controller;

use App\Backend\Service\Authentication\ServiceAuthenticationInterface;
use App\Backend\Service\Authentication\ServiceAuthentification;
use App\Config\Database;
use App\Backend\Model\Utilisateur as UtilisateurModel;
use App\Backend\Model\HistoriqueMotDePasse as HistoriqueMotDePasseModel;
use App\Backend\Model\Etudiant as EtudiantModel;
use App\Backend\Model\Enseignant as EnseignantModel;
use App\Backend\Model\PersonnelAdministratif as PersonnelAdministratifModel;
use App\Backend\Service\Email\ServiceEmail;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin;
use App\Backend\Service\GestionAcademique\ServiceGestionAcademique;
use App\Backend\Service\Permissions\ServicePermissions;
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;


abstract class BaseController
{
    protected string $viewDirectory = __DIR__ . '/../../Frontend/views/';
    protected ?ServiceAuthenticationInterface $authServiceInstance = null;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function home(): void
    {
        if ($this->getAuthService()->estUtilisateurConnecteEtSessionValide()) {
            header('Location: /dashboard');
        } else {
            header('Location: /login');
        }
        exit;
    }

    protected function getAuthService(): ServiceAuthenticationInterface
    {
        if ($this->authServiceInstance === null) {
            $pdo = Database::getInstance()->getConnection();
            $utilisateurModel = new UtilisateurModel($pdo);
            $historiqueMotDePasseModel = new HistoriqueMotDePasseModel($pdo);
            $etudiantModel = new EtudiantModel($pdo);
            $enseignantModel = new EnseignantModel($pdo);
            $personnelAdministratifModel = new PersonnelAdministratifModel($pdo);

            $serviceEmail = new ServiceEmail();
            $serviceSupervision = new ServiceSupervisionAdmin($pdo, $utilisateurModel);
            $serviceGestionAcademique = new ServiceGestionAcademique($pdo);
            $servicePermissions = new ServicePermissions($pdo, $serviceSupervision, $utilisateurModel);

            $qrProvider = new BaconQrCodeProvider();
            $tfaProvider = new TwoFactorAuth(getenv('APP_NAME_FOR_2FA') ?: 'GestionMySoutenance', 6, 30, 'sha1', $qrProvider);

            $this->authServiceInstance = new ServiceAuthentification(
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
        return $this->authServiceInstance;
    }

    protected function render(string $viewName, array $data = [], string $layout = 'layout/app'): void
    {
        extract($data);

        $viewFilePath = $this->viewDirectory . $viewName . '.php';

        if (!file_exists($viewFilePath)) {
            $this->renderError(500, "Erreur: La vue '$viewFilePath' est introuvable.");
            return;
        }

        ob_start();
        include $viewFilePath;
        $contentForLayout = ob_get_clean();

        if ($layout) {
            $layoutFilePath = $this->viewDirectory . $layout . '.php';
            if (!file_exists($layoutFilePath)) {
                $this->renderError(500, "Erreur: Le layout '$layoutFilePath' est introuvable.");
                return;
            }
            include $layoutFilePath;
        } else {
            echo $contentForLayout;
        }
    }

    protected function renderError(int $httpStatusCode, string $message = "Une erreur est survenue."): void
    {
        http_response_code($httpStatusCode);
        $errorView = 'errors/' . $httpStatusCode;
        if (!file_exists($this->viewDirectory . $errorView . '.php')) {
            $errorView = 'errors/default_error';
            if (!file_exists($this->viewDirectory . $errorView . '.php')) {
                echo "<h1>Erreur $httpStatusCode</h1><p>" . htmlspecialchars($message) . "</p><p>Page d'erreur par défaut non trouvée.</p>";
                return;
            }
        }
        $this->render($errorView, ['message' => $message, 'title' => "Erreur $httpStatusCode"], 'layout/app_minimal'); // Layout minimal pour les erreurs
    }


    protected function requireLogin(): void
    {
        if (!$this->getAuthService()->estUtilisateurConnecteEtSessionValide()) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            $_SESSION['error_message'] = "Vous devez être connecté pour accéder à cette page.";
            header('Location: /login');
            exit;
        }
    }

    protected function requireNoLogin(): void
    {
        if ($this->getAuthService()->estUtilisateurConnecteEtSessionValide()) {
            header('Location: /dashboard');
            exit;
        }
    }

    protected function redirect(string $url, array $sessionMessages = []): void
    {
        foreach ($sessionMessages as $key => $message) {
            $_SESSION[$key] = $message;
        }
        header('Location: ' . $url);
        exit;
    }

    protected function getFlashMessage(string $key): ?string
    {
        if (isset($_SESSION[$key])) {
            $message = $_SESSION[$key];
            unset($_SESSION[$key]);
            return $message;
        }
        return null;
    }
}