<?php
namespace App\Backend\Controller;

// AssetController ne dépend pas directement des services d'authentification/permissions/validator
// Car il sert des fichiers statiques. Il peut être le seul à ne pas étendre BaseController.
// Cependant, si vous voulez journaliser l'accès aux assets, il faudrait injecter ServiceSupervisionAdmin.
// Pour cet exemple, je vais le faire étendre BaseController pour la cohérence des imports,
// mais vous pouvez le faire "stand-alone" si vous le souhaitez.

use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin; // Ajout pour la journalisation (optionnel)

class AssetController extends BaseController
{
    private ServiceSupervisionAdmin $supervisionService; // Optionnel, si vous voulez journaliser l'accès aux assets

    public function __construct(
        ServiceAuthentication   $authService, // Ces services ne sont pas utilisés directement, mais requis par BaseController
        ServicePermissions      $permissionService, // Idem
        FormValidator           $validator, // Idem
        ServiceSupervisionAdmin $supervisionService // Injection optionnelle
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->supervisionService = $supervisionService;
    }

    /**
     * Sert un fichier CSS spécifique.
     * @param string $filename Le nom du fichier CSS à servir.
     */
    public function serveCss(string $filename): void
    {
        $this->serveAsset('css', $filename);
    }

    /**
     * Sert un fichier JavaScript spécifique.
     * @param string $filename Le nom du fichier JavaScript à servir.
     */
    public function serveJs(string $filename): void
    {
        $this->serveAsset('js', $filename);
    }


    public function serveImg(string $filename): void
    {
        // Le type d'asset est 'img/carousel' pour correspondre à la structure de dossier
        $this->serveAsset('img/carousel', $filename);
    }

    /**
     * Sert un fichier image pour le carrousel.
     * Cette méthode est publique et appelée par la route spécifique /assets/img/carousel/{filename}.
     * Elle délègue le travail à la méthode privée serveAsset.
     * @param string $filename Le nom du fichier image à servir.
     */
    public function serveCarImg(string $filename): void
    {
        // Le type d'asset est 'img/carousel' pour correspondre à la structure de dossier
        $this->serveAsset('img/carousel', $filename);
    }

    /**
     * Sert un asset générique (CSS, JS, images, etc.) en gérant le type MIME et les chemins.
     * @param string $type Le type d'asset (ex: 'css', 'js', 'images').
     * @param string $filename Le nom du fichier de l'asset.
     */
    private function serveAsset(string $type, string $filename): void
    {
        // Sécurité: Nettoyer le nom de fichier pour éviter le "directory traversal"
        $filename = basename($filename); // Retire le chemin et les caractères de répertoire
        $filePath = __DIR__ . "/../../../Public/assets/{$type}/{$filename}";

        // Vérifier que le fichier existe et est bien dans le répertoire des assets
        if (!file_exists($filePath) || !is_file($filePath)) {
            http_response_code(404);
            echo "Asset Not Found: " . htmlspecialchars($filename);
            // Journaliser l'accès à un asset non trouvé (optionnel)
            if (isset($this->supervisionService)) {
                $this->supervisionService->enregistrerAction(
                    $_SESSION['user_id'] ?? 'GUEST',
                    'ACCES_ASSET_ECHEC',
                    "Tentative d'accès à l'asset non trouvé: {$filePath}"
                );
            }
            exit();
        }

        // Déterminer le type MIME
        $mimeType = mime_content_type($filePath);
        if (!$mimeType) {
            // Fallback ou erreur si le type MIME ne peut pas être déterminé
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            switch ($extension) {
                case 'css': $mimeType = 'text/css'; break;
                case 'js': $mimeType = 'application/javascript'; break;
                case 'png': $mimeType = 'image/png'; break;
                case 'jpg':
                case 'jpeg': $mimeType = 'image/jpeg'; break;
                case 'gif': $mimeType = 'image/gif'; break;
                case 'svg': $mimeType = 'image/svg+xml'; break;
                default: $mimeType = 'application/octet-stream'; // Type générique si inconnu
            }
        }

        // Envoyer les en-têtes appropriés
        header("Content-Type: {$mimeType}");
        header("Content-Length: " . filesize($filePath));
        header("Cache-Control: public, max-age=86400"); // Mettre en cache pour 24 heures

        // Lire et servir le fichier
        readfile($filePath);

        // Journaliser l'accès à l'asset (optionnel)
        if (isset($this->supervisionService)) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'GUEST',
                'ACCES_ASSET_SUCCES',
                "Accès à l'asset: {$filename} (Type: {$type})",
                $filename,
                'Asset'
            );
        }
        exit(); // Terminer l'exécution du script après avoir servi le fichier
    }
}