<?php
// src/Backend/Controller/AssetController.php

namespace App\Backend\Controller;

use App\Backend\Service\Document\ServiceDocumentInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface; // Ajout de la dépendance
use App\Backend\Service\Supervision\ServiceSupervisionInterface; // Ajout de la dépendance
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\PermissionException;
use Exception;

class AssetController extends BaseController
{
    private ServiceDocumentInterface $documentService;

    public function __construct(
        ServiceDocumentInterface $documentService,
        ServiceSecuriteInterface $securiteService, // Injecté pour BaseController
        ServiceSupervisionInterface $supervisionService // Injecté pour BaseController
    ) {
        parent::__construct($securiteService, $supervisionService);
        $this->documentService = $documentService;
    }

    /**
     * Sert les fichiers protégés (documents générés, photos de profil, etc.).
     * Le chemin est construit à partir de $filePath qui peut inclure des sous-dossiers.
     *
     * @param string $filePath Le chemin relatif du fichier (ex: 'documents_generes/mon_fichier.pdf').
     */
    public function serve(string $filePath): void // Renommée de serveProtectedFile
    {
        $user = $this->securiteService->getUtilisateurConnecte();
        if (!$user) {
            $this->supervisionService->enregistrerAction('ANONYMOUS', 'ACCES_ASSET_ECHEC', null, null, ['reason' => 'Non connecté', 'file' => $filePath]);
            $this->renderError(401, "Accès non autorisé. Veuillez vous connecter.");
            return;
        }

        $fullPath = ROOT_PATH . '/Public/uploads/' . $filePath;

        if (!file_exists($fullPath) || !is_file($fullPath)) {
            $this->supervisionService->enregistrerAction($user['numero_utilisateur'], 'ACCES_ASSET_ECHEC', null, null, ['reason' => 'Fichier non trouvé', 'file' => $filePath]);
            $this->renderError(404, "Le fichier demandé n'existe pas.");
            return;
        }

        $hasPermission = false;

        // Vérifier les permissions
        if ($this->securiteService->utilisateurPossedePermission('TRAIT_ADMIN_ACCES_FICHIERS_PROTEGES')) {
            $hasPermission = true;
        }
        // Si le fichier est un document généré et l'utilisateur en est le propriétaire
        elseif (str_starts_with($filePath, 'documents_generes/')) {
            $filename = basename($filePath);
            if ($this->documentService->verifierProprieteDocument($filename, $user['numero_utilisateur'])) {
                $hasPermission = true;
            }
        }
        // Si le fichier est une photo de profil et l'utilisateur en est le propriétaire ou a accès
        elseif (str_starts_with($filePath, 'profile_pictures/')) {
            // Logique pour vérifier si l'utilisateur peut voir cette photo de profil
            // Par exemple, si c'est sa propre photo ou si l'utilisateur a une permission générale de voir les photos de profil
            if ($user['photo_profil'] === $filePath || $this->securiteService->utilisateurPossedePermission('TRAIT_VIEW_ALL_PROFILE_PICTURES')) {
                $hasPermission = true;
            }
        }
        // Exemple de permission pour le personnel administratif d'accéder aux documents étudiants
        elseif ($this->securiteService->utilisateurPossedePermission('TRAIT_PERS_ADMIN_ACCES_DOCUMENTS_ETUDIANTS')) {
            $hasPermission = true;
        }

        if (!$hasPermission) {
            $this->supervisionService->enregistrerAction($user['numero_utilisateur'], 'ACCES_ASSET_ECHEC', null, null, ['reason' => 'Permission refusée', 'file' => $filePath]);
            $this->renderError(403, "Vous n'êtes pas autorisé à accéder à ce fichier.");
            return;
        }

        try {
            $mimeType = mime_content_type($fullPath);
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($fullPath));
            header('Content-Disposition: inline; filename="' . basename($filePath) . '"'); // Utiliser basename pour le nom du fichier
            readfile($fullPath);

            $this->supervisionService->enregistrerAction($user['numero_utilisateur'], 'ACCES_ASSET_SUCCES', null, null, ['file' => $filePath]);
            exit();
        } catch (Exception $e) {
            $this->supervisionService->enregistrerAction($user['numero_utilisateur'], 'ACCES_ASSET_ECHEC', null, null, ['reason' => 'Erreur de lecture du fichier', 'file' => $filePath, 'error' => $e->getMessage()]);
            $this->renderError(500, "Erreur lors de la lecture du fichier.");
        }
    }
}