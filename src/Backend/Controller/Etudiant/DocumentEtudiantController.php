<?php
namespace App\Backend\Controller\Etudiant;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Service\DocumentGenerator\ServiceDocumentGenerator; // Importer le service
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class DocumentEtudiantController extends BaseController
{
    private ServiceDocumentGenerator $documentGeneratorService;

    public function __construct(
        ServiceAuthentication    $authService,
        ServicePermissions       $permissionService,
        FormValidator            $validator,
        ServiceDocumentGenerator $documentGeneratorService // Injection
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->documentGeneratorService = $documentGeneratorService;
    }

    /**
     * Affiche la liste des documents de l'étudiant.
     */
    public function index(): void
    {
        $this->requirePermission('TRAIT_ETUDIANT_DOCUMENTS_LISTER'); // Exiger la permission

        try {
            $currentUser = $this->getCurrentUser();
            if (!$currentUser || $currentUser['id_type_utilisateur'] !== 'TYPE_ETUD') {
                throw new OperationImpossibleException("Accès refusé. Non étudiant.");
            }
            $numeroCarteEtudiant = $currentUser['numero_utilisateur']; // L'ID utilisateur est le numéro de carte étudiant

            // Récupérer les documents générés pour cet étudiant
            $documents = $this->documentGeneratorService->getDocumentGenereModel()->trouverParUtilisateurConcerne($numeroCarteEtudiant);

            $data = [
                'page_title' => 'Mes Documents',
                'documents' => $documents
            ];
            $this->render('Etudiant/mes_documents', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement de vos documents: " . $e->getMessage());
            $this->redirect('/dashboard/etudiant');
        }
    }

    /**
     * Permet à l'étudiant de télécharger un document spécifique.
     * @param string $idDocument L'ID du document à télécharger (ID de document_genere).
     */
    public function downloadDocument(string $idDocument): void
    {
        $this->requirePermission('TRAIT_ETUDIANT_DOCUMENTS_TELECHARGER');

        try {
            $currentUser = $this->getCurrentUser();
            if (!$currentUser || $currentUser['id_type_utilisateur'] !== 'TYPE_ETUD') {
                throw new OperationImpossibleException("Accès refusé. Non étudiant.");
            }
            $numeroCarteEtudiant = $currentUser['numero_utilisateur'];

            $document = $this->documentGeneratorService->getDocumentGenereModel()->trouverParIdentifiant($idDocument);
            if (!$document) {
                throw new ElementNonTrouveException("Document non trouvé.");
            }

            // Vérifier que le document appartient bien à l'étudiant connecté
            if ($document['numero_utilisateur_concerne'] !== $numeroCarteEtudiant) {
                throw new OperationImpossibleException("Vous n'êtes pas autorisé à télécharger ce document.");
            }

            $filePath = $document['chemin_fichier'];
            $fileName = $document['nom_original'] ?? basename($filePath); // Utiliser nom_original si présent

            if (!file_exists($filePath) || !is_file($filePath)) {
                throw new ElementNonTrouveException("Fichier document non trouvé sur le serveur.");
            }

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit();

        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erreur lors du téléchargement du document: ' . $e->getMessage());
            $this->redirect('/dashboard/etudiant/documents');
        }
    }

    // Les méthodes create(), update(), delete() génériques du template initial sont à supprimer
    // car la gestion des documents est plutôt par soumission de rapport ou génération automatique.
    // La "suppression" serait plutôt un archivage.
    /*
    public function create(): void {}
    public function update($id): void {}
    public function delete($id): void {}
    */
}