<?php
// src/Backend/Controller/AssetController.php

namespace App\Backend\Controller;

use App\Config\Container;
use App\Backend\Service\Document\ServiceDocumentInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\PermissionException;

class AssetController extends BaseController
{
    private ServiceDocumentInterface $documentService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->documentService = $container->get(ServiceDocumentInterface::class);
    }

    public function serveProtectedFile(string $type, string $filename): void
    {
        $user = $this->securiteService->getUtilisateurConnecte();
        if (!$user) {
            $this->supervisionService->enregistrerAction('ANONYMOUS', 'ACCES_ASSET_ECHEC', null, null, ['reason' => 'Non connecté', 'file' => $type . '/' . $filename]);
            $this->renderError(401, "Accès non autorisé. Veuillez vous connecter.");
        }

        $fullPath = ROOT_PATH . '/Public/uploads/' . $type . '/' . $filename;

        if (!file_exists($fullPath) || !is_file($fullPath)) {
            $this->supervisionService->enregistrerAction($user['numero_utilisateur'], 'ACCES_ASSET_ECHEC', null, null, ['reason' => 'Fichier non trouvé', 'file' => $type . '/' . $filename]);
            $this->renderError(404, "Le fichier demandé n'existe pas.");
        }

        $hasPermission = false;

        if ($this->securiteService->utilisateurPossedePermission('TRAIT_ADMIN_ACCES_FICHIERS_PROTEGES')) {
            $hasPermission = true;
        }
        elseif ($this->documentService->verifierProprieteDocument($filename, $user['numero_utilisateur'])) {
            $hasPermission = true;
        }
        elseif ($this->securiteService->utilisateurPossedePermission('TRAIT_PERS_ADMIN_ACCES_DOCUMENTS_ETUDIANTS')) {
            $hasPermission = true;
        }

        if (!$hasPermission) {
            $this->supervisionService->enregistrerAction($user['numero_utilisateur'], 'ACCES_ASSET_ECHEC', null, null, ['reason' => 'Permission refusée', 'file' => $type . '/' . $filename]);
            $this->renderError(403, "Vous n'êtes pas autorisé à accéder à ce fichier.");
        }

        $mimeType = mime_content_type($fullPath);
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($fullPath));
        header('Content-Disposition: inline; filename="' . basename($filename) . '"');
        readfile($fullPath);

        $this->supervisionService->enregistrerAction($user['numero_utilisateur'], 'ACCES_ASSET_SUCCES', null, null, ['file' => $type . '/' . $filename]);
        exit();
    }
}