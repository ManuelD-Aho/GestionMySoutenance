<?php
namespace App\Backend\Controller;

use App\Backend\Service\Authentication\ServiceAuthentification;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Exception\PermissionException; // Importez l'exception de permission
use App\Backend\Exception\AuthenticationException; // Importez l'exception d'authentification

abstract class BaseController
{
    protected ServiceAuthentification $authService;
    protected ServicePermissions $permissionService;
    protected FormValidator $validator; // Injection du FormValidator
    protected array $requestData; // Pour stocker les données de la requête GET/POST

    public function __construct(
        ServiceAuthentification $authService,
        ServicePermissions $permissionService,
        FormValidator $validator // Injectez le FormValidator
    ) {
        $this->authService = $authService;
        $this->permissionService = $permissionService;
        $this->validator = $validator;
        $this->requestData = $this->parseRequestData(); // Parse les données dès la construction
    }

    /**
     * Parse les données de la requête (GET et POST) en les nettoyant.
     * @return array
     */
    protected function parseRequestData(): array
    {
        $data = [];
        // Nettoyage des données GET
        foreach ($_GET as $key => $value) {
            $data[$key] = htmlspecialchars(stripslashes(trim($value)));
        }
        // Nettoyage des données POST
        foreach ($_POST as $key => $value) {
            $data[$key] = htmlspecialchars(stripslashes(trim($value)));
        }
        return $data;
    }

    /**
     * Récupère une donnée spécifique de la requête (GET ou POST).
     * @param string $key La clé de la donnée.
     * @param mixed $default La valeur par défaut si la clé n'existe pas.
     * @return mixed La valeur de la donnée ou la valeur par défaut.
     */
    protected function getRequestData(string $key, mixed $default = null): mixed
    {
        return $this->requestData[$key] ?? $default;
    }

    /**
     * Vérifie si la requête est de type POST.
     * @return bool
     */
    protected function isPostRequest(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Rend une vue avec les données fournies et un layout optionnel.
     * @param string $view Le chemin de la vue (ex: 'Auth/login').
     * @param array $data Les données à passer à la vue.
     * @param string $layout Le layout à utiliser (ex: 'layout/app'). 'none' pour pas de layout.
     */
    protected function render(string $view, array $data = [], string $layout = 'layout/app'): void
    {
        // Les messages flash doivent être récupérés et passés à la vue/layout
        $data['flash_messages'] = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']); // Une fois lus, on les efface

        // Assurez-vous que l'utilisateur est toujours connecté et ses permissions sont à jour pour le header/menu
        $data['current_user'] = $this->authService->getUtilisateurConnecteComplet();

        // Inclure les assets CSS/JS ici ou via le layout
        // $data['assets_css'] = [];
        // $data['assets_js'] = [];

        // Chemin de base pour les vues
        $viewPath = __DIR__ . '/../../Frontend/views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            // Gérer l'erreur, par exemple charger une vue 404 ou lancer une exception
            // Pour l'instant, on lance une exception, le routeur doit la catcher
            throw new \RuntimeException("La vue '{$view}' n'existe pas: {$viewPath}");
        }

        // Démarrer la mise en mémoire tampon de la sortie
        ob_start();
        extract($data); // Extrait le tableau $data en variables
        require $viewPath;
        $content = ob_get_clean(); // Récupérer le contenu de la vue

        // Si un layout est spécifié, l'inclure
        if ($layout !== 'none') {
            $layoutPath = __DIR__ . '/../../Frontend/views/' . $layout . '.php';
            if (!file_exists($layoutPath)) {
                throw new \RuntimeException("Le layout '{$layout}' n'existe pas: {$layoutPath}");
            }
            ob_start();
            require $layoutPath;
            echo ob_get_clean();
        } else {
            echo $content;
        }
    }

    /**
     * Redirige vers une URL donnée.
     * @param string $url L'URL de destination.
     */
    protected function redirect(string $url): void
    {
        header("Location: " . $url);
        exit();
    }

    /**
     * Définit un message flash à afficher à l'utilisateur sur la prochaine requête.
     * @param string $key La clé du message (ex: 'success', 'error', 'warning').
     * @param string $message Le contenu du message.
     */
    protected function setFlashMessage(string $key, string $message): void
    {
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }
        $_SESSION['flash_messages'][$key] = $message;
    }

    /**
     * Récupère un message flash s'il existe.
     * @param string $key La clé du message.
     * @return string|null Le message ou null si non trouvé.
     */
    protected function getFlashMessage(string $key): ?string
    {
        $message = $_SESSION['flash_messages'][$key] ?? null;
        if ($message) {
            unset($_SESSION['flash_messages'][$key]);
        }
        return $message;
    }

    /**
     * Exige que l'utilisateur soit connecté. Redirige vers la page de connexion si non connecté.
     * @throws AuthenticationException Si l'utilisateur n'est pas connecté.
     */
    protected function requireLogin(): void
    {
        if (!$this->authService->estUtilisateurConnecteEtSessionValide()) {
            $this->setFlashMessage('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->redirect('/login'); // Redirige vers la page de connexion
        }
    }

    /**
     * Exige que l'utilisateur connecté possède une permission spécifique.
     * Redirige vers une page d'erreur 403 si la permission est manquante.
     * @param string $permissionCode Le code de la permission requise (ex: 'TRAIT_ADMIN_GERER_UTILISATEURS').
     * @throws PermissionException Si l'utilisateur ne possède pas la permission.
     */
    protected function requirePermission(string $permissionCode): void
    {
        $this->requireLogin(); // S'assurer que l'utilisateur est d'abord connecté

        if (!$this->permissionService->utilisateurPossedePermission($permissionCode)) {
            $loggedInUser = $this->authService->getUtilisateurConnecteComplet();
            $userId = $loggedInUser['numero_utilisateur'] ?? 'UNKNOWN_USER';
            $this->setFlashMessage('error', "Vous n'avez pas la permission d'accéder à cette ressource ({$permissionCode}).");
            // Journaliser la tentative d'accès non autorisée
            $this->authService->journaliserActionAuthentification(
                $userId,
                'ACCES_NON_AUTORISE',
                "Tentative d'accès à la permission '{$permissionCode}' refusée."
            );
            $this->redirect('/403'); // Redirige vers une page d'erreur 403
        }
    }

    /**
     * Récupère l'utilisateur actuellement connecté.
     * Utile pour passer les données de l'utilisateur à la vue.
     * @return array|null Les données complètes de l'utilisateur ou null.
     */
    protected function getCurrentUser(): ?array
    {
        return $this->authService->getUtilisateurConnecteComplet();
    }
}