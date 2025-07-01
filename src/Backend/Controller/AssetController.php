<?php
// src/Backend/Controller/AssetController.php

namespace App\Backend\Controller;

use App\Backend\Exception\PermissionException;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Backend\Util\FormValidator;

class AssetController extends BaseController
{
    private string $baseUploadPath;
    private \App\Backend\Model\GenericModel $documentGenereModel;

    public function __construct(
        ServiceSecuriteInterface $serviceSecurite,
        ServiceSupervisionInterface $serviceSupervision,
        FormValidator $formValidator,
        ServiceSystemeInterface $serviceSysteme,
        \App\Config\Container $container
    ) {
        parent::__construct($serviceSecurite, $serviceSupervision, $formValidator);
        $this->baseUploadPath = $serviceSysteme->getParametre('UPLOADS_PATH_BASE', realpath(__DIR__ . '/../../../Public/uploads/'));
        $this->documentGenereModel = $container->getModelForTable('document_genere');
    }

    /**
     * Sert un fichier protégé après avoir vérifié les droits de l'utilisateur.
     *
     * @param string $type Le sous-dossier de l'asset (ex: 'documents_generes').
     * @param string $filename Le nom du fichier demandé.
     */
    public function serveProtectedAsset(string $type, string $filename): void
    {
        try {
            $this->checkPermission('ACCES_ASSET_PROTEGE');

            if (strpos($type, '..') !== false || strpos($filename, '..') !== false) {
                throw new PermissionException("Chemin de fichier invalide.");
            }

            $fullPath = realpath($this->baseUploadPath . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $filename);

            if (!$fullPath || strpos($fullPath, realpath($this->baseUploadPath)) !== 0) {
                $this->serveNotFound();
                return;
            }

            $this->checkAssetPermissions($type, $filename);
            $this->serveFile($fullPath);

        } catch (PermissionException $e) {
            http_response_code(403);
            $this->render('errors/403.php', ['error_message' => $e->getMessage()], 'layout_auth.php');
        } catch (\Exception $e) {
            $this->serviceSupervision->enregistrerAction('SYSTEM', 'ASSET_CONTROLLER_EXCEPTION', null, null, ['error' => $e->getMessage()]);
            http_response_code(500);
            $this->render('errors/500.php', ['error_message' => 'Erreur interne du serveur.'], 'layout_auth.php');
        }
    }

    private function checkAssetPermissions(string $type, string $filename): void
    {
        $user = $this->serviceSecurite->getUtilisateurConnecte();
        if (!$user) {
            throw new PermissionException("Utilisateur non authentifié.");
        }

        if ($user['id_groupe_utilisateur'] === 'GRP_ADMIN_SYS') {
            return; // L'administrateur peut tout voir.
        }

        switch ($type) {
            case 'documents_generes':
                $relativePath = $type . '/' . $filename;
                $document = $this->documentGenereModel->trouverUnParCritere(['chemin_fichier' => $relativePath]);
                if (!$document) {
                    throw new PermissionException("Document non trouvé dans la base de données.");
                }
                if ($document['numero_utilisateur_concerne'] !== $user['numero_utilisateur']) {
                    throw new PermissionException("Vous n'avez pas l'autorisation de consulter ce document.");
                }
                break;
            default:
                throw new PermissionException("Type de ressource protégé inconnu ou non géré.");
        }
    }

    private function serveNotFound(): void
    {
        http_response_code(404);
        $this->render('errors/404.php', [], 'layout_auth.php');
    }

    private function serveFile(string $filePath): void
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            $this->serveNotFound();
            return;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($filePath);

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        while (ob_get_level()) {
            ob_end_clean();
        }

        readfile($filePath);
        exit();
    }
}