<?php
namespace Backend\Controller;
class BaseController
{
    public function home(): void
    {
        header('Location: /login');
        exit;
    }
    protected function render(string $viewPath, array $data = []): void
    {
        extract($data);
        $fullViewPath = dirname(__DIR__, 3) . '/' . $viewPath;

        if (file_exists($fullViewPath)) {
            ob_start();
            include $fullViewPath;
            $content = ob_get_clean();
            echo $content;
        } else {
            http_response_code(500);
            echo "Erreur: La vue '$fullViewPath' est introuvable.";
        }
    }
}

