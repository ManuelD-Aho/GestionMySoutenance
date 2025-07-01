<?php
// src/Backend/Controller/AssetController.php

namespace App\Backend\Controller;

use App\Backend\Model\RapportEtudiant;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Document\ServiceDocumentInterface;
use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Config\Container;
use App\Backend\Exception\PermissionException;

class AssetController extends BaseController
{
    private ServiceDocumentInterface $serviceDocument;
    private ServiceSystemeInterface $serviceSysteme;

    public function __construct(
        Container $container,
        ServiceSecuriteInterface $serviceSecurite,
        ServiceDocumentInterface $serviceDocument,
        ServiceSystemeInterface $serviceSysteme
    ) {
        parent::__construct($container, $serviceSecurite);
        $this->serviceDocument = $serviceDocument;
        $this->serviceSysteme = $serviceSysteme;
    }

    public function serveProtectedAsset(string $type, string $filename): void
    {
        try {
            // La vérification de connexion est la première étape
            if (!$this->serviceSecurite->estUtilisateurConnecte()) {
                throw new PermissionException("Accès non authentifié.");
            }

            $utilisateurConnecte = $this->serviceSecurite->getUtilisateurConnecte();
            $numeroUtilisateur = $utilisateurConnecte['numero_utilisateur'];
            $idGroupe = $utilisateurConnecte['id_groupe_utilisateur'];

            // CORRECTION : Utilisation de la méthode correcte getParametre
            $basePath = $this->serviceSysteme->getParametre('UPLOADS_PATH_BASE');
            if (!$basePath) {
                error_log("Le paramètre système 'UPLOADS_PATH_BASE' n'est pas configuré.");
                $this->serveNotFound();
                return;
            }

            // Sécurisation du chemin
            $fullPath = realpath($basePath . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $filename);
            if ($fullPath === false || strpos($fullPath, realpath($basePath)) !== 0) {
                $this->serveNotFound();
                return;
            }

            $hasAccess = false;
            switch ($type) {
                case 'documents_generes':
                    // L'admin a toujours accès
                    if ($idGroupe === 'GRP_ADMIN_SYS') {
                        $hasAccess = true;
                    } else {
                        // CORRECTION : Utilisation de la nouvelle méthode du service
                        $hasAccess = $this->serviceDocument->verifierProprieteDocument($filename, $numeroUtilisateur);
                    }
                    break;

                case 'profile_pictures':
                    // Tout utilisateur connecté peut voir les photos de profil
                    $hasAccess = true;
                    break;

                case 'rapport_images':
                    // Logique plus complexe : vérifier si l'utilisateur est l'étudiant propriétaire,
                    // un membre de la commission qui évalue le rapport, ou un admin.
                    // Cette logique serait implémentée dans un service.
                    // Pour l'exemple, on autorise l'admin et le propriétaire.
                    $rapportId = explode('_', $filename)[0]; // Suppose un nommage de fichier comme 'RAP-ID_image.jpg'
                    $rapport = $this->container->get(RapportEtudiant::class)->trouverParIdentifiant($rapportId);
                    if ($idGroupe === 'GRP_ADMIN_SYS' || ($rapport && $rapport['numero_carte_etudiant'] === $numeroUtilisateur)) {
                        $hasAccess = true;
                    }
                    // Il faudrait ajouter la vérification pour les membres de la commission
                    break;
            }

            if ($hasAccess) {
                $this->serveFile($fullPath);
            } else {
                throw new PermissionException("Vous n'avez pas les droits pour accéder à ce fichier.");
            }

        } catch (PermissionException $e) {
            $this->serveForbidden($e->getMessage());
        } catch (\Exception $e) {
            error_log("Erreur dans AssetController: " . $e->getMessage());
            $this->serveNotFound();
        }
    }

    private function serveFile(string $filePath): void
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            $this->serveNotFound();
            return;
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        ob_clean();
        flush();
        readfile($filePath);
        exit();
    }

    private function serveForbidden(string $message = "Accès interdit."): void
    {
        http_response_code(403);
        // On utilise le layout 'app' car l'utilisateur est connecté mais n'a pas les droits
        $this->render('errors/403.php', ['error_message' => $message], 'app');
    }

    private function serveNotFound(): void
    {
        http_response_code(404);
        $layout = $this->serviceSecurite->estUtilisateurConnecte() ? 'app' : 'auth';
        $this->render('errors/404.php', [], $layout);
    }
}