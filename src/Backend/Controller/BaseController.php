<?php

namespace Backend\Controller;

class BaseController
{
    public function home(): void
    {
        header('Location: /login');
        exit;
    }

    // Méthode de rendu basique (à améliorer ou remplacer par un moteur de template)
    protected function render(string $viewPath, array $data = []): void
    {
        extract($data);
        // Assumer que les vues sont relatives à la racine du projet
        $fullViewPath = dirname(__DIR__, 3) . '/' . $viewPath; // Ajuster le nombre de __DIR__ si nécessaire

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

