<?php
// src/Backend/Controller/BaseController.php

namespace App\Backend\Controller;

use App\Config\Container;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Exception\PermissionException;

/**
 * Contrôleur de base dont tous les autres contrôleurs héritent.
 * Il fournit des fonctionnalités communes comme le rendu des vues, la gestion des redirections,
 * la vérification des permissions et la protection CSRF.
 */
abstract class BaseController
{
    protected Container $container;
    protected ServiceSecuriteInterface $serviceSecurite;

    public function __construct(Container $container, ServiceSecuriteInterface $serviceSecurite)
    {
        $this->container = $container;
        $this->serviceSecurite = $serviceSecurite;
        $this->initializeCsrf();
    }

    /**
     * Rend une vue en l'intégrant dans un layout principal.
     */
    protected function render(string $viewPath, array $data = [], string $layout = 'app'): void
    {
        extract($data);
        if ($layout === 'app') {
            $utilisateurConnecte = $this->serviceSecurite->getUtilisateurConnecte();
            $menuItems = $this->serviceSecurite->construireMenuPourUtilisateurConnecte();
            $estEnModeImpersonation = $this->serviceSecurite->estEnModeImpersonation();
            $impersonatorData = $this->serviceSecurite->getImpersonatorData();
        }

        // Ajoute le jeton CSRF à toutes les vues pour l'utiliser dans les formulaires
        $csrfToken = $_SESSION['csrf_token']['value'] ?? '';

        ob_start();
        // --- CORRECTION DU CHEMIN ---
        require_once __DIR__ . '/../../Frontend/views/' . $viewPath;
        $content = ob_get_clean();

        // --- CORRECTION DU CHEMIN ---
        $layoutFile = __DIR__ . '/../../Frontend/views/layout/' . ($layout === 'app' ? 'app.php' : 'layout_auth.php');
        if (file_exists($layoutFile)) {
            require_once $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Effectue une redirection vers une URL spécifiée.
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit();
    }

    /**
     * Stocke un message "flash" dans la session.
     */
    protected function setFlash(string $key, string $message): void
    {
        $_SESSION['flash_messages'][$key] = $message;
    }

    /**
     * Récupère et supprime les messages "flash" de la session.
     */
    protected function getFlashMessages(): array
    {
        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $messages;
    }

    /**
     * Retourne une réponse JSON.
     */
    protected function jsonResponse($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    /**
     * Vérifie si l'utilisateur connecté possède une permission.
     * Lance une PermissionException si ce n'est pas le cas.
     * Cette exception est tracée dans les logs d'audit.
     */
    protected function checkPermission(string $permissionCode): void
    {
        if (!$this->serviceSecurite->utilisateurPossedePermission($permissionCode)) {
            // Utilise le conteneur pour obtenir le service de supervision à la demande
            $supervisionService = $this->container->get(ServiceSupervisionInterface::class);
            $supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'ANONYMOUS',
                'ACCES_ASSET_ECHEC', // Action d'échec d'accès
                $permissionCode,
                'Traitement'
            );
            throw new PermissionException("Accès refusé. Vous n'avez pas la permission '{$permissionCode}'.");
        }
    }

    /**
     * Initialise le jeton CSRF en session s'il n'existe pas ou a expiré.
     */
    private function initializeCsrf(): void
    {
        if (session_status() === PHP_SESSION_NONE) return;

        if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token']['expires_at']) || $_SESSION['csrf_token']['expires_at'] < time()) {
            $_SESSION['csrf_token'] = [
                'value' => bin2hex(random_bytes(32)),
                'expires_at' => time() + 3600 // Durée de vie de 1 heure
            ];
        }
    }

    /**
     * Vérifie la validité d'un jeton CSRF soumis.
     */
    protected function verifyCsrfToken(?string $submittedToken): bool
    {
        if (empty($submittedToken) || empty($_SESSION['csrf_token']['value'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token']['value'], $submittedToken);
    }
}