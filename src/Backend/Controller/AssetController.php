<?php
namespace Backend\Controller;

class AssetController extends BaseController
{
    public function serveCss(string $filename): void
    {
        // ROOT_PATH est défini dans Public/index.php
        // Le chemin vers ton fichier CSS
        $filePath = ROOT_PATH . '/src/Frontend/css/' . basename($filename); // basename pour la sécurité

        if (file_exists($filePath) && is_readable($filePath)) {
            header('Content-Type: text/css');
            // Optionnel: Ajouter des en-têtes de cache
            // header('Cache-Control: public, max-age=3600'); // Cache pendant 1 heure
            // header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
            readfile($filePath);
            exit;
        } else {
            http_response_code(404);
            echo "Fichier CSS non trouvé."; // Ou inclure une vue 404
            exit;
        }
    }
}