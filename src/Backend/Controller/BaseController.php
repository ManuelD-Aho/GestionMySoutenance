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

abstract class BaseController
{
    protected string $viewDirectory = ROOT_PATH . '/src/Frontend/views/';
    protected ?PDO $db = null;
    protected ?ServiceAuthenticationInterface $authService = null;
    protected ?ServicePermissions $permissionService = null; // Ajout pour la gestion des permissions

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
        // Cela suppose que tous les contrôleurs enfants pourraient potentiellement en avoir besoin.
        // Une meilleure approche serait l'injection de dépendances.
        if ($this->db instanceof PDO) {
            $this->initializeCommonServices();
        }
    }

    protected function initializeCommonServices(): void
    {
        $utilisateurModel = new UtilisateurModel($this->db);
        $historiqueMotDePasseModel = new HistoriqueMotDePasseModel($this->db);
        $etudiantModel = new EtudiantModel($this->db);
        $enseignantModel = new EnseignantModel($this->db);
        $personnelAdministratifModel = new PersonnelAdministratifModel($this->db);
        $serviceEmail = new ServiceEmail();

        // Dépendances pour ServiceSupervisionAdmin
        $rapportEtudiantModel = new RapportEtudiant($this->db);
        $enregistrerModel = new Enregistrer($this->db);
        $pisterModel = new Pister($this->db);
        $compteRenduModel = new CompteRendu($this->db);
        $serviceSupervision = new ServiceSupervisionAdmin(
            $rapportEtudiantModel,
            $enregistrerModel,
            $pisterModel,
            $compteRenduModel,
            $this->db
        );

        // Dépendances pour ServiceGestionAcademique
        $inscrireModel = new Inscrire($this->db);
        $evaluerModel = new Evaluer($this->db);
        $faireStageModel = new FaireStage($this->db);
        $acquerirModel = new Acquerir($this->db);
        $occuperModel = new Occuper($this->db);
        $attribuerModel = new Attribuer($this->db);
        $serviceGestionAcademique = new ServiceGestionAcademique(
            $inscrireModel,
            $evaluerModel,
            $faireStageModel,
            $acquerirModel,
            $occuperModel,
            $attribuerModel
        );

        $this->permissionService = new ServicePermissions($this->db, $serviceSupervision);

        $qrProvider = new BaconQrCodeProvider();
        $tfaProvider = new TwoFactorAuth(($_ENV['APP_NAME'] ?? 'GestionMySoutenance'), 6, 30, 'sha1', $qrProvider);

        $this->authService = new ServiceAuthentification(
            $this->db,
            $serviceEmail,
            $serviceSupervision,
            $serviceGestionAcademique,
            $this->permissionService,
            $tfaProvider,
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
        extract($data, EXTR_SKIP); // EXTR_SKIP pour ne pas écraser les variables existantes comme $this

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
            // Les variables $pageTitle, $menuItems, $currentUser, $userRole, $contentView
            // sont attendues par le layout et doivent être dans $data ou définies avant d'inclure le layout
            // $contentForLayout contient le contenu de la vue $viewName
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
            $errorViewName = 'errors/default_error'; // Un fallback
            $errorViewPath = $this->viewDirectory . $errorViewName . '.php';
            if (!file_exists($errorViewPath)) {
                // Fallback ultime si même la vue d'erreur par défaut n'existe pas
                echo "<h1>Erreur {$httpStatusCode}</h1><p>" . htmlspecialchars($message) . "</p><p>Page d'erreur non trouvée.</p>";
                return;
            }
        }

        // Utiliser la méthode render pour inclure le layout d'erreur si nécessaire
        // Supposons un layout minimal pour les erreurs : 'layout/error_layout'
        $this->render($errorViewName, ['message' => $message, 'title' => "Erreur {$httpStatusCode}"], 'layout/error_layout');
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
        $this->requireLogin(); // Implicitement, une permission ne peut être vérifiée que pour un utilisateur connecté

        $userNum = $_SESSION['numero_utilisateur'] ?? null;
        if ($userNum === null || !$this->permissionService || !$this->permissionService->utilisateurPossedePermission($userNum, $permissionCode)) {
            if ($redirectTo) {
                $this->setFlashMessage('error_message', $errorMessage);
                $this->redirect($redirectTo);
            } else {
                $this->renderError(403, $errorMessage);
                exit; // Important pour arrêter l'exécution après renderError
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