<?php

namespace App\Backend\Controller;

use App\Config\Database;
use PDO;
use App\Backend\Service\Authentication\ServiceAuthenticationInterface;
use App\Backend\Service\Authentication\ServiceAuthentification; // Pour l'instanciation par défaut
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
use App\Backend\Service\Permissions\ServicePermissions;
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;
use RobThree\Auth\Algorithm; // <-- AJOUTÉ : Importer la classe Algorithm

abstract class BaseController
{
    protected string $viewDirectory = ROOT_PATH . '/src/Frontend/views/';
    protected ?PDO $db = null;
    protected ?ServiceAuthenticationInterface $authService = null;
    protected ?ServicePermissions $permissionService = null; // Ajout pour la gestion des permissions
    protected ?TwoFactorAuth $tfa = null; // Ajout pour stocker l'instance de TFA si besoin ailleurs

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            $databaseInstance = Database::getInstance();
            $this->db = $databaseInstance->getConnection();
        } catch (\PDOException $e) {
            error_log("ERREUR CRITIQUE de connexion BDD dans BaseController: " . $e->getMessage());
            // En mode développement, afficher l'erreur peut être utile, sinon une page d'erreur générique.
            if (($_ENV['APP_ENV'] ?? 'production') === 'development') {
                die("Erreur critique BDD: " . $e->getMessage());
            } else {
                die("Une erreur critique est survenue. Veuillez contacter l'administrateur.");
            }
        }

        // Instanciation des services ici pour qu'ils soient disponibles pour les contrôleurs enfants
        if ($this->db instanceof PDO) {
            $this->initializeCommonServices();
        }
    }

    protected function initializeCommonServices(): void
    {
        // Instanciation des Modèles
        $utilisateurModel = new UtilisateurModel($this->db);
        $historiqueMotDePasseModel = new HistoriqueMotDePasseModel($this->db);
        $etudiantModel = new EtudiantModel($this->db);
        $enseignantModel = new EnseignantModel($this->db);
        $personnelAdministratifModel = new PersonnelAdministratifModel($this->db);
        $rapportEtudiantModel = new RapportEtudiant($this->db);
        $enregistrerModel = new Enregistrer($this->db);
        $pisterModel = new Pister($this->db);
        $compteRenduModel = new CompteRendu($this->db);
        $inscrireModel = new Inscrire($this->db);
        $evaluerModel = new Evaluer($this->db);
        $faireStageModel = new FaireStage($this->db);
        $acquerirModel = new Acquerir($this->db);
        $occuperModel = new Occuper($this->db);
        $attribuerModel = new Attribuer($this->db);

        // Instanciation des Services
        $serviceEmail = new ServiceEmail();

        $serviceSupervision = new ServiceSupervisionAdmin(
            $rapportEtudiantModel,
            $enregistrerModel,
            $pisterModel,
            $compteRenduModel,
            $this->db
        );

        $serviceGestionAcademique = new ServiceGestionAcademique(
            $inscrireModel,
            $evaluerModel,
            $faireStageModel,
            $acquerirModel,
            $occuperModel,
            $attribuerModel,
            $this->db // Assurez-vous que le constructeur de ServiceGestionAcademique attend la BDD si nécessaire
        );

        $this->permissionService = new ServicePermissions($this->db, $serviceSupervision);

        // Configuration du TwoFactorAuth
        $qrProvider = new BaconQrCodeProvider();
        // Utilisation de la variable d'environnement pour le nom de l'application, avec un fallback.
        $issuer = $_ENV['APP_NAME'] ?? 'GestionMySoutenance';
        // Correction de l'instanciation de TwoFactorAuth
        $this->tfa = new TwoFactorAuth(
            $issuer,
            6,          // Nombre de chiffres pour le code OTP
            30,         // Période de validité du code OTP en secondes
            Algorithm::Sha1, // <-- CORRIGÉ : Utilisation de la constante/enum pour l'algorithme
            $qrProvider // Fournisseur pour la génération de QR Code
        // Le 6ème argument (rngprovider) est optionnel et peut être omis si non nécessaire.
        );

        // Instanciation du ServiceAuthentification avec toutes ses dépendances
        $this->authService = new ServiceAuthentification(
            $this->db,
            $serviceEmail,
            $serviceSupervision,
            $serviceGestionAcademique,
            $this->permissionService,
            $this->tfa, // Passer l'instance de TwoFactorAuth configurée
            $utilisateurModel,
            $historiqueMotDePasseModel,
            $etudiantModel,
            $enseignantModel,
            $personnelAdministratifModel
        );
    }


    public function home(): void
    {
        if ($this->authService && $this->authService->estUtilisateurConnecteEtSessionValide()) {
            $this->redirect('/dashboard');
        } else {
            $this->redirect('/login');
        }
    }

    protected function render(string $viewName, array $data = [], string $layout = 'layout/app'): void
    {
        extract($data, EXTR_SKIP);

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

    protected function renderError(int $httpStatusCode, string $message = "Une erreur est survenue.", ?string $customView = null): void
    {
        http_response_code($httpStatusCode);

        $errorViewName = $customView ?? 'errors/' . $httpStatusCode;
        $errorViewPath = $this->viewDirectory . $errorViewName . '.php';

        if (!file_exists($errorViewPath)) {
            $errorViewName = 'errors/default_error';
            $errorViewPath = $this->viewDirectory . $errorViewName . '.php';
            if (!file_exists($errorViewPath)) {
                echo "<h1>Erreur {$httpStatusCode}</h1><p>" . htmlspecialchars($message) . "</p><p>Page d'erreur non trouvée.</p>";
                return;
            }
        }

        // Pour le rendu d'erreur, on peut supposer un layout minimal ou pas de layout complexe.
        // Si vous avez un layout spécifique pour les erreurs (ex: 'layout/error_layout.php'), utilisez-le.
        // Sinon, incluez directement la vue d'erreur.
        $dataToPass = ['message' => $message, 'title' => "Erreur {$httpStatusCode}"];
        extract($dataToPass, EXTR_SKIP);
        include $errorViewPath; // Affichage direct de la vue d'erreur sans le layout principal 'app'
    }

    protected function requireLogin(): void
    {
        if (!$this->authService || !$this->authService->estUtilisateurConnecteEtSessionValide()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            $this->setFlashMessage('error_message', "Vous devez être connecté pour accéder à cette page.");
            $this->redirect('/login');
        }
    }

    protected function requireNoLogin(): void
    {
        if ($this->authService && $this->authService->estUtilisateurConnecteEtSessionValide()) {
            $this->redirect('/dashboard');
        }
    }

    protected function checkPermission(string $permissionCode, ?string $redirectTo = null, string $errorMessage = "Accès refusé."): void
    {
        $this->requireLogin();

        $userNum = $_SESSION['numero_utilisateur'] ?? null;
        if ($userNum === null || !$this->permissionService || !$this->permissionService->utilisateurPossedePermission($userNum, $permissionCode)) {
            if ($redirectTo) {
                $this->setFlashMessage('error_message', $errorMessage);
                $this->redirect($redirectTo);
            } else {
                $this->renderError(403, $errorMessage); // renderError gère l'affichage et devrait appeler exit si nécessaire
            }
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

    protected function setFlashMessage(string $key, string $message): void
    {
        $_SESSION[$key] = $message;
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
