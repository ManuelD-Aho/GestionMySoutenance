<?php
// src/Backend/Controller/BaseController.php

namespace App\Backend\Controller;

use App\Backend\Exception\PermissionException;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;

abstract class BaseController
{
    protected ServiceSecuriteInterface $serviceSecurite;
    protected ServiceSupervisionInterface $serviceSupervision;
    protected FormValidator $formValidator;

    public function __construct(
        ServiceSecuriteInterface $serviceSecurite,
        ServiceSupervisionInterface $serviceSupervision,
        FormValidator $formValidator
    ) {
        $this->serviceSecurite = $serviceSecurite;
        $this->serviceSupervision = $serviceSupervision;
        $this->formValidator = $formValidator;

        $this->initializeCsrf();
    }

    protected function render(string $viewPath, array $data = [], string $layout = 'app.php'): void
    {
        extract($data);
        $user = $this->serviceSecurite->getUtilisateurConnecte();

        ob_start();
        require_once __DIR__ . '/../../Frontend/views/' . $viewPath;
        $content = ob_get_clean();

        require_once __DIR__ . '/../../Frontend/views/layout/' . $layout;
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit();
    }

    protected function jsonResponse(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit();
    }

    protected function checkPermission(string $permissionCode): void
    {
        if (!$this->serviceSecurite->utilisateurPossedePermission($permissionCode)) {
            $this->serviceSupervision->enregistrerAction(
                $_SESSION['user_id'] ?? 'ANONYMOUS',
                'ACCES_ASSET_ECHEC',
                $permissionCode,
                'Traitement'
            );
            throw new PermissionException("Accès refusé. Vous n'avez pas la permission '{$permissionCode}'.");
        }

        if (isset($_SESSION['user_id'])) {
            $this->serviceSupervision->pisterAcces($_SESSION['user_id'], $permissionCode);
        }
    }

    private function initializeCsrf(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            return;
        }

        if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token']['expires_at']) || $_SESSION['csrf_token']['expires_at'] < time()) {
            $_SESSION['csrf_token'] = [
                'value' => bin2hex(random_bytes(32)),
                'expires_at' => time() + 3600
            ];
        }
    }

    protected function verifyCsrfToken(?string $submittedToken): bool
    {
        if (empty($submittedToken) || empty($_SESSION['csrf_token']['value'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token']['value'], $submittedToken);
    }
}
