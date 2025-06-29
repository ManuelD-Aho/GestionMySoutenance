<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Config\Container;
use App\Backend\Service\Interface\AuthenticationServiceInterface;
use App\Backend\Exception\PermissionException;

abstract class BaseController
{
    protected Container $container;
    protected AuthenticationServiceInterface $authService;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->authService = $container->get(AuthenticationServiceInterface::class);
    }

    protected function render(string $view, array $data = [], string $layout = 'layout/app'): void
    {
        $viewPath = ROOT_PATH . '/src/Frontend/views/' . $view . '.php';
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("La vue '{$viewPath}' est introuvable.");
        }

        $data['page_title'] = $data['page_title'] ?? 'GestionMySoutenance';
        $data['flash_messages'] = $this->getFlashMessages();
        $data['current_user'] = $this->authService->getUtilisateurConnecte();

        extract($data, EXTR_SKIP);

        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        $layoutPath = ROOT_PATH . '/src/Frontend/views/' . $layout . '.php';
        if (!file_exists($layoutPath)) {
            throw new \RuntimeException("Le layout '{$layoutPath}' est introuvable.");
        }

        include $layoutPath;
    }

    protected function addFlashMessage(string $type, string $message): void
    {
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }
        $_SESSION['flash_messages'][$type] = $message;
    }

    protected function getFlashMessages(): array
    {
        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $messages;
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . $url, true, 303);
        exit;
    }

    protected function checkAuthentication(): void
    {
        if (!$this->authService->estConnecte()) {
            $this->addFlashMessage('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->redirect('/login');
        }
    }

    protected function checkPermission(string $permissionCode): void
    {
        $this->checkAuthentication();

        $userPermissions = $_SESSION['user_permissions'] ?? [];

        if (!in_array($permissionCode, $userPermissions)) {
            throw new PermissionException("Accès refusé. Vous ne disposez pas de la permission requise ('{$permissionCode}').");
        }
    }

    protected function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function validateCsrfToken(?string $token): bool
    {
        if ($token === null || empty($_SESSION['csrf_token'])) {
            return false;
        }

        if (hash_equals($_SESSION['csrf_token'], $token)) {
            unset($_SESSION['csrf_token']);
            return true;
        }

        return false;
    }
}