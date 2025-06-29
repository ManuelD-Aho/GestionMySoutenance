<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Service\Interface\AuthenticationServiceInterface;
use App\Backend\Service\Interface\PermissionsServiceInterface;
use App\Backend\Util\FormValidator;
use App\Backend\Exception\PermissionException;
use App\Backend\Exception\ValidationException;

/**
 * BaseController - Fondation "Pure PHP" de Qualité Production.
 *
 * Rédigé le : 2025-06-29 13:55:05 UTC par ManuelD-Aho
 *
 * Ce contrôleur est le cœur de l'architecture. Il a été délibérément conçu sans dépendances externes
 * (comme Twig ou Symfony HttpFoundation) pour démontrer une maîtrise des mécanismes fondamentaux de PHP et HTTP.
 * Il fournit un ensemble d'outils sécurisés et robustes pour tous les contrôleurs enfants.
 */
abstract class BaseController
{
    protected AuthenticationServiceInterface $authService;
    protected PermissionsServiceInterface $permissionsService;
    protected FormValidator $validator;

    private ?array $currentUser = null;

    public function __construct(
        AuthenticationServiceInterface $authService,
        PermissionsServiceInterface $permissionsService,
        FormValidator $validator
    ) {
        $this->authService = $authService;
        $this->permissionsService = $permissionsService;
        $this->validator = $validator;

        $this->startSession();
    }

    /**
     * Point d'entrée sécurisé pour toutes les actions des contrôleurs.
     * Il orchestre l'authentification, l'autorisation et la gestion des erreurs.
     */
    public function execute(string $action, string $permissionRequired, array $vars = []): void
    {
        try {
            $this->isAccessGranted($permissionRequired);
            call_user_func_array([$this, $action], $vars);
        } catch (PermissionException $e) {
            $this->render('error/error403', ['message' => $e->getMessage()], 403);
        } catch (ValidationException $e) {
            $this->json(['errors' => $e->getErrors()], 422);
        } catch (\Exception $e) {
            // TODO: Intégrer un LoggerService pour enregistrer $e->getMessage() et sa trace.
            $this->render('error/error500', ['message' => 'Une erreur interne est survenue.'], 500);
        }
    }

    // ========================================================================
    // SECTION : GESTION DE LA SÉCURITÉ
    // ========================================================================

    /**
     * Vérifie si l'utilisateur a le droit d'accéder à l'action.
     * C'est le portail de sécurité central.
     */
    final protected function isAccessGranted(string $permission): void
    {
        if (!$this->authService->estConnecte()) {
            $this->redirect('/login');
        }

        $user = $this->getCurrentUser();
        if ($user === null) {
            $this->redirect('/login'); // Double sécurité
        }

        if (!$this->permissionsService->utilisateurPossedePermission($user['numero_utilisateur'], $permission)) {
            throw new PermissionException("Accès refusé. La permission '{$permission}' est requise.");
        }
    }

    /**
     * Génère et stocke un token CSRF dans la session s'il n'existe pas.
     */
    final protected function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Vérifie le token CSRF soumis via un formulaire POST.
     * Doit être appelé pour toute action modifiant l'état (POST, PUT, DELETE).
     */
    final protected function verifyCsrfToken(): void
    {
        $submittedToken = $this->post('_csrf_token');
        if (empty($submittedToken) || !hash_equals($this->generateCsrfToken(), $submittedToken)) {
            throw new PermissionException('Token de sécurité invalide ou manquant. L\'action a été bloquée.');
        }
    }

    // ========================================================================
    // SECTION : GESTION DES REQUÊTES (ACCESSEURS SÉCURISÉS)
    // ========================================================================

    final protected function getMethod(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    final protected function get(string $key, $default = null): ?string
    {
        return isset($_GET[$key]) ? htmlspecialchars(trim($_GET[$key]), ENT_QUOTES, 'UTF-8') : $default;
    }

    final protected function post(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    final protected function postAll(): array
    {
        return $_POST;
    }

    // ========================================================================
    // SECTION : GESTION DES RÉPONSES (MÉTHODES DE SORTIE CONTRÔLÉE)
    // ========================================================================

    /**
     * Rend une vue PHP native de manière sécurisée.
     * Extrait les variables dans un scope limité et fournit une fonction d'échappement.
     */
    final protected function render(string $templatePath, array $data = [], int $httpCode = 200): void
    {
        $templateFile = __DIR__ . "/../../Frontend/views/{$templatePath}.php";

        if (!is_readable($templateFile)) {
            throw new \RuntimeException("Le template '{$templateFile}' est introuvable ou illisible.");
        }

        // Fonction d'échappement qui sera disponible dans la vue.
        // C'est notre principale défense contre les failles XSS.
        $e = function (string $value): string {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        };

        // Fonction pour générer un input CSRF.
        $csrf_input = function (): string {
            return '<input type="hidden" name="_csrf_token" value="' . $this->generateCsrfToken() . '">';
        };

        http_response_code($httpCode);

        // La magie de l'isolation : `extract` ne polluera pas le scope du contrôleur.
        // La vue s'exécute dans le scope de cette méthode.
        extract($data);

        // Inclusion de la vue.
        include $templateFile;
    }

    /**
     * Envoie une réponse JSON et termine le script.
     */
    final protected function json(array $data, int $httpCode = 200): void
    {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    /**
     * Effectue une redirection HTTP et termine le script.
     */
    final protected function redirect(string $url, int $httpCode = 302): void
    {
        header("Location: {$url}", true, $httpCode);
        exit;
    }

    // ========================================================================
    // SECTION : UTILITAIRES
    // ========================================================================

    /**
     * Démarre une session de manière sécurisée.
     */
    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 3600,
                'path' => '/',
                'domain' => '', // Mettre votre domaine en production
                'secure' => $this->getMethod() === 'https', // True en production
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }

    /**
     * Récupère l'utilisateur connecté et le met en cache pour la durée de la requête.
     */
    final protected function getCurrentUser(): ?array
    {
        if ($this->currentUser === null && $this->authService->estConnecte()) {
            $this->currentUser = $this->authService->getUtilisateurConnecte();
        }
        return $this->currentUser;
    }

    /**
     * Valide les données en utilisant le service injecté.
     */
    final protected function validate(array $data, array $rules): array
    {
        return $this->validator->validate($data, $rules);
    }
}