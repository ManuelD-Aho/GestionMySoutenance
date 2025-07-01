<?php

namespace App\Backend\Service\Document;

use PDO;
use App\Backend\Model\DocumentGenere;
use App\Backend\Service\DocumentGenerator\ServiceDocumentGeneratorInterface;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGeneratorInterface;
use App\Backend\Service\Fichier\ServiceFichierInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\ValidationException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceDocument implements ServiceDocumentInterface
{
    private PDO $db;
    private DocumentGenere $documentModel;
    private ServiceDocumentGeneratorInterface $generatorService;
    private ServiceSupervisionAdminInterface $supervisionService;
    private IdentifiantGeneratorInterface $idGenerator;
    private ServiceFichierInterface $fichierService;

    public function __construct(
        PDO $db,
        DocumentGenere $documentModel,
        ServiceDocumentGeneratorInterface $generatorService,
        ServiceSupervisionAdminInterface $supervisionService,
        IdentifiantGeneratorInterface $idGenerator,
        ServiceFichierInterface $fichierService
    ) {
        $this->db = $db;
        $this->documentModel = $documentModel;
        $this->generatorService = $generatorService;
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
        $this->fichierService = $fichierService;
    }

    public function genererDocumentPDF(string $template, array $donnees, array $options = []): string
    {
        try {
            // Récupérer le template
            $templateData = $this->obtenirTemplate($template);
            if (!$templateData) {
                throw new ElementNonTrouveException("Template non trouvé: {$template}");
            }

            // Traiter le contenu du template avec les données
            $contenuTraite = $this->traiterTemplate($templateData['contenu'], $donnees);

            // Générer le PDF
            $nomFichier = $options['nom_fichier'] ?? 'document_' . date('Y-m-d_H-i-s');
            $cheminDocument = $this->genererPDF($contenuTraite, $nomFichier, $options);

            // Enregistrer le document dans la base
            $idDocument = $this->enregistrerDocument($template, $cheminDocument, $donnees, $options);

            $this->supervisionService->enregistrerAction(
                $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                'GENERATION_DOCUMENT_PDF',
                "Génération d'un document PDF",
                'document',
                $idDocument,
                ['template' => $template, 'nom_fichier' => $nomFichier]
            );

            return $cheminDocument;

        } catch (\Exception $e) {
            throw new OperationImpossibleException("Impossible de générer le document PDF: " . $e->getMessage());
        }
    }

    public function creerTemplate(string $nomTemplate, string $contenuTemplate, array $variables): string
    {
        try {
            $this->db->beginTransaction();

            $idTemplate = $this->idGenerator->genererProchainId('template_document');
            
            $sql = "INSERT INTO templates_documents (id_template, nom_template, contenu_template, variables_disponibles, date_creation, statut_template)
                    VALUES (?, ?, ?, ?, NOW(), 'ACTIF')";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $idTemplate,
                $nomTemplate,
                $contenuTemplate,
                json_encode($variables)
            ]);

            if ($result) {
                $this->supervisionService->enregistrerAction(
                    $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                    'CREATION_TEMPLATE',
                    "Création d'un template de document",
                    'template',
                    $idTemplate,
                    ['nom' => $nomTemplate, 'variables' => count($variables)]
                );
            }

            $this->db->commit();
            return $idTemplate;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible de créer le template: " . $e->getMessage());
        }
    }

    public function modifierTemplate(string $idTemplate, array $donneesModification): bool
    {
        try {
            $this->db->beginTransaction();

            $template = $this->obtenirTemplate($idTemplate);
            if (!$template) {
                throw new ElementNonTrouveException("Template non trouvé: {$idTemplate}");
            }

            $champsAutorisees = ['nom_template', 'contenu_template', 'variables_disponibles'];
            $setClause = [];
            $params = [];

            foreach ($donneesModification as $champ => $valeur) {
                if (in_array($champ, $champsAutorisees)) {
                    $setClause[] = "{$champ} = ?";
                    if ($champ === 'variables_disponibles' && is_array($valeur)) {
                        $params[] = json_encode($valeur);
                    } else {
                        $params[] = $valeur;
                    }
                }
            }

            if (empty($setClause)) {
                throw new ValidationException("Aucune donnée valide à modifier.");
            }

            $setClause[] = "date_modification = NOW()";
            $params[] = $idTemplate;

            $sql = "UPDATE templates_documents SET " . implode(', ', $setClause) . " WHERE id_template = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                $this->supervisionService->enregistrerAction(
                    $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                    'MODIFICATION_TEMPLATE',
                    "Modification d'un template de document",
                    'template',
                    $idTemplate,
                    $donneesModification
                );
            }

            $this->db->commit();
            return $result;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible de modifier le template: " . $e->getMessage());
        }
    }

    public function supprimerTemplate(string $idTemplate): bool
    {
        try {
            $this->db->beginTransaction();

            $template = $this->obtenirTemplate($idTemplate);
            if (!$template) {
                throw new ElementNonTrouveException("Template non trouvé: {$idTemplate}");
            }

            // Suppression logique
            $sql = "UPDATE templates_documents SET statut_template = 'SUPPRIME', date_suppression = NOW() WHERE id_template = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$idTemplate]);

            if ($result) {
                $this->supervisionService->enregistrerAction(
                    $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                    'SUPPRESSION_TEMPLATE',
                    "Suppression d'un template de document",
                    'template',
                    $idTemplate,
                    ['nom' => $template['nom_template']]
                );
            }

            $this->db->commit();
            return $result;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible de supprimer le template: " . $e->getMessage());
        }
    }

    public function listerTemplates(array $filtres = []): array
    {
        $sql = "SELECT * FROM templates_documents WHERE statut_template != 'SUPPRIME'";
        $params = [];

        if (!empty($filtres['nom'])) {
            $sql .= " AND nom_template LIKE ?";
            $params[] = '%' . $filtres['nom'] . '%';
        }

        if (!empty($filtres['type'])) {
            $sql .= " AND type_template = ?";
            $params[] = $filtres['type'];
        }

        $sql .= " ORDER BY date_creation DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fusionnerDocumentsPDF(array $cheminsFichiers, string $nomFichierSortie): string
    {
        try {
            // Vérifier que tous les fichiers existent
            foreach ($cheminsFichiers as $chemin) {
                if (!file_exists($chemin)) {
                    throw new ElementNonTrouveException("Fichier non trouvé: {$chemin}");
                }
            }

            $cheminSortie = ROOT_PATH . "/tmp/{$nomFichierSortie}.pdf";

            // Utiliser une bibliothèque de fusion PDF (simulation ici)
            $this->executerFusionPDF($cheminsFichiers, $cheminSortie);

            $this->supervisionService->enregistrerAction(
                $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                'FUSION_DOCUMENTS_PDF',
                "Fusion de documents PDF",
                null,
                null,
                ['fichiers_source' => count($cheminsFichiers), 'fichier_sortie' => $nomFichierSortie]
            );

            return $cheminSortie;

        } catch (\Exception $e) {
            throw new OperationImpossibleException("Impossible de fusionner les documents: " . $e->getMessage());
        }
    }

    public function convertirDocument(string $cheminSource, string $formatCible, array $options = []): string
    {
        try {
            if (!file_exists($cheminSource)) {
                throw new ElementNonTrouveException("Fichier source non trouvé: {$cheminSource}");
            }

            $formatsSupporte = ['PDF', 'HTML', 'DOCX', 'TXT'];
            if (!in_array(strtoupper($formatCible), $formatsSupporte)) {
                throw new ValidationException("Format de destination non supporté: {$formatCible}");
            }

            $infoFichier = pathinfo($cheminSource);
            $nomSortie = $infoFichier['filename'] . '_converti.' . strtolower($formatCible);
            $cheminSortie = dirname($cheminSource) . '/' . $nomSortie;

            // Simulation de conversion
            $this->executerConversion($cheminSource, $cheminSortie, $formatCible, $options);

            $this->supervisionService->enregistrerAction(
                $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                'CONVERSION_DOCUMENT',
                "Conversion de document",
                null,
                null,
                ['source' => basename($cheminSource), 'format_cible' => $formatCible]
            );

            return $cheminSortie;

        } catch (\Exception $e) {
            throw new OperationImpossibleException("Impossible de convertir le document: " . $e->getMessage());
        }
    }

    public function ajouterFiligrane(string $cheminDocument, string $texteFiligrane, array $parametres = []): string
    {
        try {
            if (!file_exists($cheminDocument)) {
                throw new ElementNonTrouveException("Document non trouvé: {$cheminDocument}");
            }

            $infoFichier = pathinfo($cheminDocument);
            $nomSortie = $infoFichier['filename'] . '_filigrane.' . $infoFichier['extension'];
            $cheminSortie = dirname($cheminDocument) . '/' . $nomSortie;

            $parametresDefaut = [
                'position' => 'centre',
                'opacite' => 0.3,
                'taille_police' => 12,
                'couleur' => '#CCCCCC',
                'rotation' => 45
            ];

            $parametres = array_merge($parametresDefaut, $parametres);

            // Simulation d'ajout de filigrane
            copy($cheminDocument, $cheminSortie);
            $this->executerAjoutFiligrane($cheminSortie, $texteFiligrane, $parametres);

            $this->supervisionService->enregistrerAction(
                $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                'AJOUT_FILIGRANE',
                "Ajout de filigrane à un document",
                null,
                null,
                ['document' => basename($cheminDocument), 'texte' => $texteFiligrane]
            );

            return $cheminSortie;

        } catch (\Exception $e) {
            throw new OperationImpossibleException("Impossible d'ajouter le filigrane: " . $e->getMessage());
        }
    }

    public function signerDocument(string $cheminDocument, string $certificat, string $motDePasse): string
    {
        try {
            if (!file_exists($cheminDocument)) {
                throw new ElementNonTrouveException("Document non trouvé: {$cheminDocument}");
            }

            if (!file_exists($certificat)) {
                throw new ElementNonTrouveException("Certificat non trouvé: {$certificat}");
            }

            $infoFichier = pathinfo($cheminDocument);
            $nomSortie = $infoFichier['filename'] . '_signe.' . $infoFichier['extension'];
            $cheminSortie = dirname($cheminDocument) . '/' . $nomSortie;

            // Simulation de signature numérique
            copy($cheminDocument, $cheminSortie);
            $this->executerSignatureNumerique($cheminSortie, $certificat, $motDePasse);

            $this->supervisionService->enregistrerAction(
                $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                'SIGNATURE_DOCUMENT',
                "Signature numérique d'un document",
                null,
                null,
                ['document' => basename($cheminDocument), 'certificat' => basename($certificat)]
            );

            return $cheminSortie;

        } catch (\Exception $e) {
            throw new OperationImpossibleException("Impossible de signer le document: " . $e->getMessage());
        }
    }

    public function archiverDocuments(array $documentsIds, string $motifArchivage): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE document_genere SET statut_document = 'ARCHIVE', date_archivage = NOW(), motif_archivage = ? WHERE id_document_genere IN (" . str_repeat('?,', count($documentsIds) - 1) . "?)";
            $params = array_merge([$motifArchivage], $documentsIds);

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                $this->supervisionService->enregistrerAction(
                    $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                    'ARCHIVAGE_DOCUMENTS',
                    "Archivage de documents",
                    null,
                    null,
                    ['documents' => $documentsIds, 'motif' => $motifArchivage]
                );
            }

            $this->db->commit();
            return $result;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible d'archiver les documents: " . $e->getMessage());
        }
    }

    public function rechercherDocuments(array $criteres, int $page = 1, int $elementsParPage = 20): array
    {
        $offset = ($page - 1) * $elementsParPage;
        
        $sql = "SELECT dg.*, td.nom_template 
                FROM document_genere dg
                LEFT JOIN templates_documents td ON dg.template_utilise = td.id_template
                WHERE dg.statut_document != 'SUPPRIME'";

        $params = [];

        if (!empty($criteres['type_document'])) {
            $sql .= " AND dg.type_document = ?";
            $params[] = $criteres['type_document'];
        }

        if (!empty($criteres['date_debut'])) {
            $sql .= " AND dg.date_generation >= ?";
            $params[] = $criteres['date_debut'];
        }

        if (!empty($criteres['date_fin'])) {
            $sql .= " AND dg.date_generation <= ?";
            $params[] = $criteres['date_fin'];
        }

        if (!empty($criteres['utilisateur'])) {
            $sql .= " AND dg.numero_utilisateur_generateur = ?";
            $params[] = $criteres['utilisateur'];
        }

        if (!empty($criteres['recherche'])) {
            $sql .= " AND (dg.nom_document LIKE ? OR dg.description_document LIKE ?)";
            $terme = '%' . $criteres['recherche'] . '%';
            $params[] = $terme;
            $params[] = $terme;
        }

        // Compter le total
        $countSql = "SELECT COUNT(*) as total FROM ($sql) as count_query";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Récupérer les données paginées
        $sql .= " ORDER BY dg.date_generation DESC LIMIT ? OFFSET ?";
        $params[] = $elementsParPage;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'documents' => $documents,
            'pagination' => [
                'page_actuelle' => $page,
                'elements_par_page' => $elementsParPage,
                'total_elements' => $total,
                'total_pages' => ceil($total / $elementsParPage)
            ]
        ];
    }

    public function validerIntegriteDocument(string $cheminDocument): array
    {
        try {
            if (!file_exists($cheminDocument)) {
                throw new ElementNonTrouveException("Document non trouvé: {$cheminDocument}");
            }

            $resultats = [
                'fichier_accessible' => true,
                'taille_fichier' => filesize($cheminDocument),
                'hash_md5' => md5_file($cheminDocument),
                'hash_sha256' => hash_file('sha256', $cheminDocument),
                'format_valide' => $this->verifierFormatDocument($cheminDocument),
                'structure_valide' => $this->verifierStructureDocument($cheminDocument),
                'signature_numerique' => $this->verifierSignatureNumerique($cheminDocument),
                'date_verification' => date('Y-m-d H:i:s')
            ];

            $integrite = $resultats['format_valide'] && $resultats['structure_valide'];
            $resultats['integrite_globale'] = $integrite ? 'VALIDE' : 'INVALIDE';

            $this->supervisionService->enregistrerAction(
                $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                'VALIDATION_INTEGRITE_DOCUMENT',
                "Validation de l'intégrité d'un document",
                null,
                null,
                ['document' => basename($cheminDocument), 'integrite' => $resultats['integrite_globale']]
            );

            return $resultats;

        } catch (\Exception $e) {
            throw new OperationImpossibleException("Impossible de valider l'intégrité du document: " . $e->getMessage());
        }
    }

    // Méthodes privées d'assistance

    private function obtenirTemplate(string $idTemplate): ?array
    {
        $sql = "SELECT * FROM templates_documents WHERE id_template = ? AND statut_template = 'ACTIF'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idTemplate]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function traiterTemplate(string $contenu, array $donnees): string
    {
        // Traitement simple de template avec remplacement de variables
        $contenuTraite = $contenu;
        
        foreach ($donnees as $variable => $valeur) {
            $placeholder = "{{" . $variable . "}}";
            $contenuTraite = str_replace($placeholder, $valeur, $contenuTraite);
        }

        // Traitement des conditions simples
        $contenuTraite = preg_replace_callback(
            '/\{\{#if\s+(\w+)\}\}(.*?)\{\{\/if\}\}/s',
            function($matches) use ($donnees) {
                $variable = $matches[1];
                $contenu = $matches[2];
                return !empty($donnees[$variable]) ? $contenu : '';
            },
            $contenuTraite
        );

        return $contenuTraite;
    }

    private function genererPDF(string $contenu, string $nomFichier, array $options): string
    {
        // Utilisation de TCPDF ou autre bibliothèque PDF
        $cheminDocument = ROOT_PATH . "/tmp/{$nomFichier}.pdf";
        
        // Simulation de génération PDF
        $pdf = new \TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);
        $pdf->writeHTML($contenu);
        $pdf->Output($cheminDocument, 'F');

        return $cheminDocument;
    }

    private function enregistrerDocument(string $template, string $cheminDocument, array $donnees, array $options): string
    {
        $idDocument = $this->idGenerator->genererProchainId('document_genere');
        
        $donneesDocument = [
            'id_document_genere' => $idDocument,
            'template_utilise' => $template,
            'nom_document' => basename($cheminDocument),
            'chemin_document' => $cheminDocument,
            'type_document' => $options['type'] ?? 'PDF',
            'taille_document' => filesize($cheminDocument),
            'numero_utilisateur_generateur' => $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
            'date_generation' => date('Y-m-d H:i:s'),
            'statut_document' => 'GENERE',
            'donnees_generation' => json_encode($donnees)
        ];

        $this->documentModel->creer($donneesDocument);
        return $idDocument;
    }

    private function executerFusionPDF(array $cheminsFichiers, string $cheminSortie): void
    {
        // Simulation de fusion PDF
        $contenu = "Fusion de " . count($cheminsFichiers) . " documents PDF";
        file_put_contents($cheminSortie, $contenu);
    }

    private function executerConversion(string $source, string $destination, string $format, array $options): void
    {
        // Simulation de conversion
        $contenu = "Document converti au format {$format}";
        file_put_contents($destination, $contenu);
    }

    private function executerAjoutFiligrane(string $cheminDocument, string $texte, array $parametres): void
    {
        // Simulation d'ajout de filigrane
        // Dans une vraie implémentation, utiliser une bibliothèque PDF
    }

    private function executerSignatureNumerique(string $cheminDocument, string $certificat, string $motDePasse): void
    {
        // Simulation de signature numérique
        // Dans une vraie implémentation, utiliser une bibliothèque de cryptographie
    }

    private function verifierFormatDocument(string $cheminDocument): bool
    {
        $extension = pathinfo($cheminDocument, PATHINFO_EXTENSION);
        $formatsValides = ['pdf', 'doc', 'docx', 'txt', 'html'];
        return in_array(strtolower($extension), $formatsValides);
    }

    private function verifierStructureDocument(string $cheminDocument): bool
    {
        // Vérification basique de la structure du fichier
        $taille = filesize($cheminDocument);
        return $taille > 0 && $taille < 100 * 1024 * 1024; // Moins de 100MB
    }

    private function verifierSignatureNumerique(string $cheminDocument): array
    {
        // Simulation de vérification de signature
        return [
            'presente' => false,
            'valide' => false,
            'certificat' => null,
            'date_signature' => null
        ];
    }
}