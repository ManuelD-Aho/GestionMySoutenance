<?php

declare(strict_types=1);

namespace App\Backend\Service;

use App\Backend\Service\Interface\FichierServiceInterface;
use App\Backend\Service\Interface\AuditServiceInterface;
use App\Backend\Exception\ValidationException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceFichier implements FichierServiceInterface
{
    private AuditServiceInterface $auditService;
    private string $baseUploadDir;

    public function __construct(AuditServiceInterface $auditService)
    {
        $this->auditService = $auditService;
        $this->baseUploadDir = __DIR__ . '/../../../Public/uploads/';
    }

    public function uploader(array $fichier, string $destination, array $contraintes = []): string
    {
        if ($fichier['error'] !== UPLOAD_ERR_OK) {
            throw new ValidationException("Erreur lors de l'upload du fichier.", ['file' => 'Upload error code: ' . $fichier['error']]);
        }

        $tailleMax = $contraintes['max_size'] ?? 5 * 1024 * 1024; // 5MB par défaut
        if ($fichier['size'] > $tailleMax) {
            throw new ValidationException("Le fichier dépasse la taille maximale autorisée.", ['file_size' => 'Taille maximale: ' . $tailleMax]);
        }

        $typesMimeAutorises = $contraintes['allowed_types'] ?? ['image/jpeg', 'image/png', 'application/pdf'];
        if (!in_array($fichier['type'], $typesMimeAutorises)) {
            throw new ValidationException("Le type de fichier n'est pas autorisé.", ['file_type' => 'Types autorisés: ' . implode(', ', $typesMimeAutorises)]);
        }

        $nomFichier = bin2hex(random_bytes(16)) . '.' . pathinfo($fichier['name'], PATHINFO_EXTENSION);
        $cheminDestination = $this->baseUploadDir . ltrim($destination, '/');

        if (!is_dir($cheminDestination)) {
            mkdir($cheminDestination, 0755, true);
        }

        $cheminComplet = $cheminDestination . '/' . $nomFichier;

        if (!move_uploaded_file($fichier['tmp_name'], $cheminComplet)) {
            throw new OperationImpossibleException("Impossible de déplacer le fichier uploadé.");
        }

        $this->auditService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'FILE_UPLOADED', null, null, ['path' => $cheminComplet]);

        return ltrim($destination, '/') . '/' . $nomFichier;
    }

    public function supprimer(string $cheminFichier): bool
    {
        $cheminComplet = $this->baseUploadDir . $cheminFichier;
        if (file_exists($cheminComplet) && is_file($cheminComplet)) {
            if (unlink($cheminComplet)) {
                $this->auditService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'FILE_DELETED', null, null, ['path' => $cheminComplet]);
                return true;
            }
        }
        return false;
    }

    public function genererUrlSecurisee(string $cheminFichier, int $dureeValidite): string
    {
        $expiration = time() + $dureeValidite;
        $secretKey = getenv('APP_SECRET_KEY') ?: 'default_secret';
        $signature = hash_hmac('sha256', $cheminFichier . $expiration, $secretKey);

        return "/download.php?file=" . urlencode($cheminFichier) . "&expires=" . $expiration . "&sig=" . $signature;
    }

    public function getMetadonnees(string $cheminFichier): array
    {
        $cheminComplet = $this->baseUploadDir . $cheminFichier;
        if (!file_exists($cheminComplet)) {
            throw new ElementNonTrouveException("Fichier non trouvé.");
        }
        return [
            'size' => filesize($cheminComplet),
            'mime_type' => mime_content_type($cheminComplet),
            'last_modified' => filemtime($cheminComplet)
        ];
    }

    public function redimensionnerImage(string $cheminImage, int $largeur, int $hauteur): string
    {
        $cheminComplet = $this->baseUploadDir . $cheminImage;
        if (!file_exists($cheminComplet)) {
            throw new ElementNonTrouveException("Image non trouvée.");
        }

        list($width_orig, $height_orig, $type) = getimagesize($cheminComplet);
        $ratio_orig = $width_orig / $height_orig;

        if ($largeur / $hauteur > $ratio_orig) {
            $largeur = $hauteur * $ratio_orig;
        } else {
            $hauteur = $largeur / $ratio_orig;
        }

        $image_p = imagecreatetruecolor($largeur, $hauteur);

        switch ($type) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($cheminComplet);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($cheminComplet);
                break;
            default:
                throw new OperationImpossibleException("Type d'image non supporté pour le redimensionnement.");
        }

        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $largeur, $hauteur, $width_orig, $height_orig);

        $path_parts = pathinfo($cheminComplet);
        $new_filename = $path_parts['filename'] . "_{$largeur}x{$hauteur}." . $path_parts['extension'];
        $new_filepath = $path_parts['dirname'] . '/' . $new_filename;

        imagejpeg($image_p, $new_filepath, 85);
        imagedestroy($image_p);
        imagedestroy($image);

        return str_replace($this->baseUploadDir, '', $new_filepath);
    }

    public function compresserFichier(string $cheminFichier): string
    {
        $cheminComplet = $this->baseUploadDir . $cheminFichier;
        if (!file_exists($cheminComplet)) {
            throw new ElementNonTrouveException("Fichier non trouvé.");
        }

        $zip = new \ZipArchive();
        $zipFilename = $cheminComplet . '.zip';

        if ($zip->open($zipFilename, \ZipArchive::CREATE) !== TRUE) {
            throw new OperationImpossibleException("Impossible de créer l'archive zip.");
        }

        $zip->addFile($cheminComplet, basename($cheminFichier));
        $zip->close();

        return $cheminFichier . '.zip';
    }

    public function scannerAntivirus(string $cheminFichier): bool
    {
        // Cette fonction est un placeholder pour une intégration réelle
        // avec un scanner comme ClamAV.
        // ex: $result = shell_exec('clamscan ' . escapeshellarg($this->baseUploadDir . $cheminFichier));
        // if (strpos($result, 'FOUND') !== false) { throw new ... }
        return true;
    }
}