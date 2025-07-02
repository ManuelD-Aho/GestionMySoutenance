<?php
// src/Backend/Controller/BaseController.php

namespace App\Backend\Controller;

use App\Config\Container;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Exception\ElementNonTrouveException;
use JetBrains\PhpStorm\NoReturn;
use Random\RandomException;

abstract class BaseController
{
    protected Container $container;
    protected ServiceSecuriteInterface $securiteService;
    protected ServiceSupervisionInterface $supervisionService;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->securiteService = $container->get(ServiceSecuriteInterface::class);
        $this->supervisionService = $container->get(ServiceSupervisionInterface::class);

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user_id'])) {
            $_SESSION['last_activity'] = time();
        }
    }

    protected function render(string $viewPath, array $data = [], string $layout = 'layout/app'): void
    {
        $data['flash_messages'] = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);

        $data['user'] = $this->securiteService->getUtilisateurConnecte();
        $data['is_impersonating'] = $this->securiteService->estEnModeImpersonation();
        $data['impersonator_data'] = $this->securiteService->getImpersonatorData();
        $data['menu_items'] = $this->securiteService->construireMenuPourUtilisateurConnecte();

        $viewFullPath = ROOT_PATH . '/src/Frontend/views/' . $viewPath . '.php';
        if (!file_exists($viewFullPath)) {
            throw new ElementNonTrouveException("Fichier de vue non trouvé : " . $viewFullPath);
        }

        extract($data);

        ob_start();
        require $viewFullPath;
        $content = ob_get_clean();

        $layoutPath = ROOT_PATH . '/src/Frontend/views/' . $layout . '.php';
        if (!file_exists($layoutPath)) {
            throw new ElementNonTrouveException("Fichier de layout non trouvé : " . $layoutPath);
        }
        require_once $layoutPath;
    }

    #[NoReturn]
    public function renderError(int $statusCode, string $message = ''): void
    {
        http_response_code($statusCode);
        $viewPath = 'errors/' . $statusCode;
        $data = ['title' => "Erreur {$statusCode}", 'error_message' => $message];
        $this->render($viewPath, $data, 'layout/layout_auth');
        exit();
    }

    #[NoReturn]
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit();
    }

    protected function addFlashMessage(string $type, string $message): void
    {
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }
        $_SESSION['flash_messages'][] = ['type' => $type, 'message' => $message];
    }

    protected function getPostData(): array
    {
        return filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS, true) ?? [];
    }

    protected function getGetData(): array
    {
        return filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS, true) ?? [];
    }

    protected function getFileData(string $fieldName): ?array
    {
        return $_FILES[$fieldName] ?? null;
    }

    protected function isPostRequest(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isGetRequest(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    protected function requirePermission(string $permissionCode, ?string $contexteId = null, ?string $contexteType = null): void
    {
        if (!$this->securiteService->utilisateurPossedePermission($permissionCode, $contexteId, $contexteType)) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'ANONYMOUS',
                'ACCES_REFUSE',
                $contexteId,
                $contexteType,
                ['permission_requise' => $permissionCode, 'url' => $_SERVER['REQUEST_URI']]
            );
            $this->renderError(403, "Vous n'avez pas la permission d'accéder à cette ressource ou d'effectuer cette action.");
        }
    }

    protected function generateCsrfToken(string $formName): string
    {
        try {
            if (empty($_SESSION['csrf_tokens'][$formName])) {
                $_SESSION['csrf_tokens'][$formName] = bin2hex(random_bytes(32));
            }
            return $_SESSION['csrf_tokens'][$formName];
        } catch (RandomException $e) {
            error_log("Erreur de génération CSRF: " . $e->getMessage());
            $this->addFlashMessage('error', 'Une erreur de sécurité est survenue. Veuillez réessayer.');
            throw $e;
        }
    }

    protected function validateCsrfToken(string $formName, string $token): bool
    {
        if (!isset($_SESSION['csrf_tokens'][$formName]) || !hash_equals($_SESSION['csrf_tokens'][$formName], $token)) {
            unset($_SESSION['csrf_tokens'][$formName]);
            $this->addFlashMessage('error', 'Jeton de sécurité invalide. Veuillez réessayer.');
            return false;
        }
        unset($_SESSION['csrf_tokens'][$formName]);
        return true;
    }
}