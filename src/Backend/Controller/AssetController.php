<?php

declare(strict_types=1);

namespace App\Backend\Controller;

class AssetController
{
    private const MIME_TYPES = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff2' => 'font/woff2',
        'woff' => 'font/woff',
        'ttf' => 'font/ttf',
    ];

    private function serve(string $baseDir, string $filename): void
    {
        $filePath = realpath($baseDir . DIRECTORY_SEPARATOR . $filename);

        if ($filePath === false || strpos($filePath, realpath($baseDir)) !== 0) {
            http_response_code(404);
            echo "Fichier non trouvé ou accès non autorisé.";
            return;
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $contentType = self::MIME_TYPES[$extension] ?? 'application/octet-stream';

        header('Content-Type: ' . $contentType);
        header('Cache-Control: public, max-age=31536000');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
        exit;
    }

    public function serveCss(string $filename): void
    {
        $this->serve(ROOT_PATH . '/Public/assets/css', $filename);
    }

    public function serveJs(string $filename): void
    {
        $this->serve(ROOT_PATH . '/Public/assets/js', $filename);
    }

    public function serveImg(string $filename): void
    {
        $this->serve(ROOT_PATH . '/Public/assets/img', $filename);
    }

    public function serveCarImg(string $filename): void
    {
        $this->serve(ROOT_PATH . '/Public/assets/img/carousel', $filename);
    }

    public function serveUpload(string $type, string $filename): void
    {
        $allowedTypes = ['photos_profil', 'documents_rapports', 'pieces_jointes'];
        if (!in_array($type, $allowedTypes)) {
            http_response_code(403);
            echo "Accès interdit à ce type de ressource.";
            return;
        }
        $this->serve(ROOT_PATH . '/Public/uploads/' . $type, $filename);
    }
}