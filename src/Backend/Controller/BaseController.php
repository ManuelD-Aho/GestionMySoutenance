<?php

namespace App\Backend\Controller;

use App\Backend\Controller\Security\CsrfProtectedController;
use App\Config\Container;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Exception\PermissionException;

abstract class BaseController
{
    use CsrfProtectedController;

    protected Container $container;
    protected ServicePermissions $permissionsService;

    public function __construct()
    {
        $this->container = new Container();
        $this->permissionsService = $this->container->getPermissionsService();
    }

    protected function render(string $view, array $data = [], string $layout = 'layout/app'): void
    {
        extract($data);
        ob_start();
        require_once ROOT_PATH . "/src/Frontend/views/{$view}.php";
        $content = ob_get_clean();
        require_once ROOT_PATH . "/src/Frontend/views/{$layout}.php";
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit();
    }

    protected function setFlashMessage(string $key, string $message): void
    {
        $_SESSION[$key] = $message;
    }

    protected function getFlashMessage(string $key): ?string
    {
        $message = $_SESSION[$key] ?? null;
        unset($_SESSION[$key]);
        return $message;
    }

    protected function requireLogin(): void
    {
        if (!isset($_SESSION['user']['numero_utilisateur'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            $this->setFlashMessage('login_error_message', 'Vous devez être connecté pour accéder à cette page.');
            $this->redirect('/login');
        }
    }

    /**
     * Vérifie si l'utilisateur connecté a une permission spécifique.
     * En cas d'échec, lève une exception qui affichera une erreur 403 (Accès Interdit).
     */
    protected function requirePermission(string $permission): void
    {
        // On s'assure d'abord que l'utilisateur est connecté
        $this->requireLogin();

        $numeroUtilisateur = $_SESSION['user']['numero_utilisateur'] ?? null;
        if (!$this->permissionsService->aLaPermission($numeroUtilisateur, $permission)) {
            throw new PermissionException("Vous n'avez pas les droits nécessaires pour accéder à cette ressource.");
        }
    }
}