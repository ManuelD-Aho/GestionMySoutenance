<?php

namespace App\Backend\Service\Fichier;

use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\ConfigurationSysteme\ServiceConfigurationSystemeInterface;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\ValidationException;

class ServiceFichier implements ServiceFichierInterface
{
    private ServiceSupervisionAdminInterface $supervisionService;
    private ServiceConfigurationSystemeInterface $configService;

    public function __construct(
        ServiceSupervisionAdminInterface $supervisionService,
        ServiceConfigurationSystemeInterface $configService
    ) {
        $this->supervisionService = $supervisionService;
        $this->configService = $configService;
    }

    public function uploadFichier(array $fileData, string $destinationType, array $allowedMimeTypes = [], int $maxSize = 0): string
    {
        if (!isset($fileData['error']) || is_array($fileData['error'])) {
            throw new ValidationException("Paramètres de fichier invalides.");
        }

        switch ($fileData['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new ValidationException("Aucun fichier n'a été envoyé.");
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new ValidationException("La taille du fichier dépasse la limite autorisée.");
            default:
                throw new OperationImpossibleException("Erreur inconnue lors de l'upload du fichier.");
        }

        if ($maxSize > 0 && $fileData['size'] > $maxSize) {
            throw new ValidationException("La taille du fichier dépasse la limite de " . ($maxSize / 1024 / 1024) . " Mo.");
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($fileData['tmp_name']);
        if (!empty($allowedMimeTypes) && !in_array($mimeType, $allowedMimeTypes)) {
            throw new ValidationException("Le type de fichier '{$mimeType}' n'est pas autorisé.");
        }

        $destinationPath = $this->getCheminStockage($destinationType);
        if (!is_dir($destinationPath)) {
            if (!mkdir($destinationPath, 0755, true)) {
                throw new OperationImpossibleException("Impossible de créer le répertoire de destination.");
            }
        }

        $fileExtension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
        $safeFileName = bin2hex(random_bytes(16)) . '.' . $fileExtension;
        $filePath = $destinationPath . '/' . $safeFileName;

        if (!move_uploaded_file($fileData['tmp_name'], $filePath)) {
            throw new OperationImpossibleException("Échec du déplacement du fichier uploadé.");
        }

        $this->supervisionService->enregistrerAction(
            $_SESSION['user_id'] ?? 'SYSTEM',
            'UPLOAD_FICHIER',
            "Fichier '{$fileData['name']}' uploadé vers '{$filePath}'."
        );

        return $destinationType . '/' . $safeFileName;
    }

    public function supprimerFichier(string $filePath): bool
    {
        $fullPath = ROOT_PATH . '/Public/uploads/' . $filePath;
        if (file_exists($fullPath) && is_file($fullPath)) {
            if (unlink($fullPath)) {
                $this->supervisionService->enregistrerAction(
                    $_SESSION['user_id'] ?? 'SYSTEM',
                    'SUPPRESSION_FICHIER',
                    "Fichier '{$filePath}' supprimé."
                );
                return true;
            }
        }
        return false;
    }

    public function getCheminStockage(string $typeFichier): string
    {
        $params = $this->configService->recupererParametresGeneraux();
        $paramKey = 'UPLOADS_PATH_' . strtoupper($typeFichier);
        $defaultPath = ROOT_PATH . '/Public/uploads/' . $typeFichier;
        return $params[$paramKey] ?? $defaultPath;
    }
}