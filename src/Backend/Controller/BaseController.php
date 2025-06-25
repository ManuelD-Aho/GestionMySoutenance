<?php
namespace App\Backend\Controller;

use App\Backend\Service\Authentication\ServiceAuthentification;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Exception\PermissionException; // Importez l'exception de permission
use App\Backend\Exception\AuthenticationException; // Importez l'exception d'authentification
use Dotenv\Exception;

abstract class BaseController
{
    protected ServiceAuthentification $authService;
    protected ServicePermissions $permissionService;
    protected FormValidator $validator; // Injection du FormValidator

    // Constante pour le nom de la clé CSRF en session
    protected const CSRF_TOKEN_KEY = 'csrf_token';
    // Durée de vie du token CSRF en secondes
    protected const CSRF_TOKEN_LIFETIME = 3600; // 1 heure


    public function __construct(
        ServiceAuthentification $authService,
        ServicePermissions $permissionService,
        FormValidator $validator // Injectez le FormValidator
    ) {
        $this->authService = $authService;
        $this->permissionService = $permissionService;
        $this->validator = $validator;
    }

    /**
     * Récupère un paramètre GET de manière sécurisée.
     * @param string $key La clé du paramètre.
     * @param mixed $default La valeur par défaut si la clé n'existe pas.
     * @return mixed La valeur du paramètre.
     */
    protected function get(string $key, mixed $default = null): mixed
    {
        return isset($_GET[$key]) ? htmlspecialchars(stripslashes(trim((string)$_GET[$key]))) : $default;
    }

    /**
     * Récupère un paramètre POST de manière sécurisée.
     * @param string $key La clé du paramètre.
     * @param mixed $default La valeur par défaut si la clé n'existe pas.
     * @return mixed La valeur du paramètre.
     */
    protected function post(string $key, mixed $default = null): mixed
    {
        return isset($_POST[$key]) ? htmlspecialchars(stripslashes(trim((string)$_POST[$key]))) : $default;
    }

    /**
     * Récupère un paramètre de requête (GET ou POST) de manière sécurisée.
     * @param string $key La clé du paramètre.
     * @param mixed $default La valeur par défaut si la clé n'existe pas.
     * @return mixed La valeur du paramètre.
     */
    protected function request(string $key, mixed $default = null): mixed
    {
        return isset($_REQUEST[$key]) ? htmlspecialchars(stripslashes(trim((string)$_REQUEST[$key]))) : $default;
    }

    /**
     * Récupère un fichier uploadé de manière sécurisée.
     * @param string $key La clé du fichier.
     * @return array|null Le tableau du fichier ou null si non trouvé.
     */
    protected function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
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
     * GÉNERATION ET PASSAGE AUTOMATIQUE DU CSRF À LA VUE.
     *
     * @param string $view Le chemin de la vue (ex: 'Auth/login').
     * @param array $data Les données à passer à la vue.
     * @param string $layout Le layout à utiliser (ex: 'layout/app'). 'none' pour pas de layout.
     */
    protected function render(string $view, array $data = [], string $layout = 'layout/app'): void
    {
        // AJOUT : Générer le jeton CSRF et l'ajouter aux données de la vue
        $data['csrf_token'] = $this->generateCsrfToken();

        // Les messages flash doivent être récupérés et passés à la vue/layout
        // REMPLACÉ : Utiliser un système de messages flash plus générique
        $data['flash_messages'] = [
            'success' => $_SESSION['flash_messages']['success'] ?? null,
            'error' => $_SESSION['flash_messages']['error'] ?? null,
            'warning' => $_SESSION['flash_messages']['warning'] ?? null,
            'info' => $_SESSION['flash_messages']['info'] ?? null,
        ];
        // MODIFICATION : Effacer tous les messages flash après les avoir passés à la vue
        unset($_SESSION['flash_messages']);

        // Assurez-vous que l'utilisateur est toujours connecté et ses permissions sont à jour pour le header/menu
        $data['current_user'] = $this->authService->getUtilisateurConnecteComplet();

        // Chemin de base pour les vues
        $viewPath = ROOT_PATH . '/src/Frontend/views/' . $view . '.php'; // Utilisation de ROOT_PATH

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("La vue '{$view}' n'existe pas: {$viewPath}");
        }

        ob_start();
        extract($data);
        require $viewPath;
        $content = ob_get_clean();

        if ($layout !== 'none') {
            $layoutPath = ROOT_PATH . '/src/Frontend/views/' . $layout . '.php'; // Utilisation de ROOT_PATH
            if (!file_exists($layoutPath)) {
                throw new \RuntimeException("Le layout '{$layout}' n'existe pas: {$layoutPath}");
            }
            // IMPORTANT: Dans le layout, 'require $content;' ne fonctionne pas.
            /* Le layout doit contenir `<?= $content ?>' ou `<?php echo $content; ?>`*/
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

    /**
     * Génère un jeton CSRF unique et le stocke en session.
     * Le jeton est associé à une date d'expiration.
     * @return string Le jeton CSRF généré.
     */
    protected function generateCsrfToken(): string
    {
        if (empty($_SESSION[self::CSRF_TOKEN_KEY]) || $_SESSION[self::CSRF_TOKEN_KEY]['expires_at'] < time()) {
            $_SESSION[self::CSRF_TOKEN_KEY] = [
                'value' => bin2hex(random_bytes(32)),
                'expires_at' => time() + self::CSRF_TOKEN_LIFETIME
            ];
        }
        return $_SESSION[self::CSRF_TOKEN_KEY]['value'];
    }

    /**
     * Vérifie la validité d'un jeton CSRF soumis via POST.
     * @param string|null $token Le jeton soumis.
     * @return bool Vrai si le jeton est valide et non expiré.
     */
    protected function verifyCsrfToken(?string $token): bool
    {
        if (empty($token) || empty($_SESSION[self::CSRF_TOKEN_KEY])) {
            return false;
        }
        if ($token !== $_SESSION[self::CSRF_TOKEN_KEY]['value']) {
            return false;
        }
        if ($_SESSION[self::CSRF_TOKEN_KEY]['expires_at'] < time()) {
            unset($_SESSION[self::CSRF_TOKEN_KEY]); // Expired token
            return false;
        }
        // Jeton valide et non expiré, on le consomme (pour usage unique)
        unset($_SESSION[self::CSRF_TOKEN_KEY]);
        return true;
    }
}