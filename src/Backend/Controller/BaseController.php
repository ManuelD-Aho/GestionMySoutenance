<?php
namespace App\Backend\Controller;
class BaseController
{
    public function home(): void
    {
        header('Location: /login');
        exit;
    }
//    protected function render(string $viewPath, array $data = []): void
//    {
//        extract($data);
//        $fullViewPath = dirname(__DIR__, 3) . '/' . $viewPath;
//
//        if (file_exists($fullViewPath)) {
//            ob_start();
//            include $fullViewPath;
//            $content = ob_get_clean();
//            echo $content;
//        } else {
//            http_response_code(500);
//            echo "Erreur: La vue '$fullViewPath' est introuvable.";
//        }
//    }

    // Dans BaseController.php, la méthode render pourrait ressembler à ceci :
    protected function render(string $viewPath, array $data = []): void
    {
        extract($data); // Extrait les variables pour les rendre accessibles dans la vue
        // Si $viewPath est le layout principal (app.php), il inclura $contentView
        // Si $viewPath est une vue simple (comme login.php), elle est incluse directement.
        $fullViewPath = ROOT_PATH . '/' . $viewPath;

        if (file_exists($fullViewPath)) {
            ob_start();
            include $fullViewPath;
            $content = ob_get_clean();
            echo $content;
        } else {
            // ... gestion d'erreur ...
        }
    }
}

