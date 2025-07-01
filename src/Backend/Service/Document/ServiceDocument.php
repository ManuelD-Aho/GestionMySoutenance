<?php
// src/Backend/Service/Document/ServiceDocument.php

namespace App\Backend\Service\Document;

use PDO;
use TCPDF;
use App\Backend\Model\GenericModel;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Exception\{ElementNonTrouveException, OperationImpossibleException, ValidationException};

class ServiceDocument implements ServiceDocumentInterface
{
    private PDO $db;
    private GenericModel $documentGenereModel;
    private GenericModel $etudiantModel;
    private GenericModel $inscrireModel;
    private GenericModel $evaluerModel;
    private GenericModel $compteRenduModel;
    private GenericModel $anneeAcademiqueModel;
    private RapportEtudiant $rapportModel;
    private GenericModel $sectionRapportModel;
    private ServiceSystemeInterface $systemeService;
    private ServiceSupervisionInterface $supervisionService;

    public function __construct(
        PDO $db,
        GenericModel $documentGenereModel,
        GenericModel $etudiantModel,
        GenericModel $inscrireModel,
        GenericModel $evaluerModel,
        GenericModel $compteRenduModel,
        GenericModel $anneeAcademiqueModel,
        RapportEtudiant $rapportModel,
        GenericModel $sectionRapportModel,
        ServiceSystemeInterface $systemeService,
        ServiceSupervisionInterface $supervisionService
    ) {
        $this->db = $db;
        $this->documentGenereModel = $documentGenereModel;
        $this->etudiantModel = $etudiantModel;
        $this->inscrireModel = $inscrireModel;
        $this->evaluerModel = $evaluerModel;
        $this->compteRenduModel = $compteRenduModel;
        $this->anneeAcademiqueModel = $anneeAcademiqueModel;
        $this->rapportModel = $rapportModel;
        $this->sectionRapportModel = $sectionRapportModel;
        $this->systemeService = $systemeService;
        $this->supervisionService = $supervisionService;
    }

    // ====================================================================
    // SECTION 1: GÉNÉRATION DE DOCUMENTS PDF
    // ====================================================================

    public function genererAttestationScolarite(string $numeroEtudiant, string $idAnneeAcademique): string
    {
        $etudiant = $this->etudiantModel->trouverParIdentifiant($numeroEtudiant);
        $annee = $this->anneeAcademiqueModel->trouverParIdentifiant($idAnneeAcademique);
        $inscription = $this->inscrireModel->trouverUnParCritere(['numero_carte_etudiant' => $numeroEtudiant, 'id_annee_academique' => $idAnneeAcademique]);

        if (!$etudiant || !$inscription || !$annee) {
            throw new ElementNonTrouveException("Données d'inscription introuvables pour l'étudiant {$numeroEtudiant} pour l'année {$idAnneeAcademique}.");
        }

        $templatePath = __DIR__ . '/../../../templates/pdf/attestation_scolarite.html';
        if (!file_exists($templatePath)) throw new OperationImpossibleException("Le modèle de l'attestation est introuvable.");

        $htmlContent = file_get_contents($templatePath);

        $variables = [
            '{{nom_etudiant}}' => htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']),
            '{{date_naissance}}' => date('d/m/Y', strtotime($etudiant['date_naissance'])),
            '{{lieu_naissance}}' => htmlspecialchars($etudiant['lieu_naissance']),
            '{{annee_academique}}' => htmlspecialchars($annee['libelle_annee_academique']),
            '{{date_generation}}' => date('d/m/Y')
        ];
        $htmlFinal = strtr($htmlContent, $variables);

        // L'ID de l'entité concernée est l'ID composite de l'inscription pour garantir l'unicité
        $idEntite = $numeroEtudiant . '_' . $inscription['id_niveau_etude'] . '_' . $idAnneeAcademique;
        return $this->genererPdfDepuisHtml($htmlFinal, 'AttestationScolarite', $idEntite, 'DOC_ATTESTATION', $numeroEtudiant);
    }

    public function genererBulletinNotes(string $numeroEtudiant, string $idAnneeAcademique): string
    {
        $etudiant = $this->etudiantModel->trouverParIdentifiant($numeroEtudiant);
        $annee = $this->anneeAcademiqueModel->trouverParIdentifiant($idAnneeAcademique);
        if (!$etudiant || !$annee) throw new ElementNonTrouveException("Étudiant ou année académique introuvable.");

        $notes = $this->evaluerModel->trouverParCritere(['numero_carte_etudiant' => $numeroEtudiant, 'id_annee_academique' => $idAnneeAcademique]);

        $templatePath = __DIR__ . '/../../../templates/pdf/bulletin_notes.html';
        if (!file_exists($templatePath)) throw new OperationImpossibleException("Le modèle du bulletin est introuvable.");
        $htmlContent = file_get_contents($templatePath);

        $lignesNotes = '';
        foreach ($notes as $note) {
            $lignesNotes .= "<tr><td>" . htmlspecialchars($note['id_ecue']) . "</td><td>" . htmlspecialchars($note['note']) . "</td></tr>";
        }

        $variables = [
            '{{nom_etudiant}}' => htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']),
            '{{annee_academique}}' => htmlspecialchars($annee['libelle_annee_academique']),
            '{{lignes_notes}}' => $lignesNotes,
            '{{date_generation}}' => date('d/m/Y')
        ];
        $htmlFinal = strtr($htmlContent, $variables);

        $idEntite = $numeroEtudiant . '_' . $idAnneeAcademique;
        return $this->genererPdfAvecVersionning($htmlFinal, 'BulletinNotes', $idEntite, 'DOC_BULLETIN', $numeroEtudiant);
    }

    public function genererPvValidation(string $idCompteRendu): string
    {
        $pv = $this->compteRenduModel->trouverParIdentifiant($idCompteRendu);
        if (!$pv) throw new ElementNonTrouveException("PV '{$idCompteRendu}' non trouvé.");

        $templatePath = __DIR__ . '/../../../templates/pdf/pv_validation.html';
        if (!file_exists($templatePath)) throw new OperationImpossibleException("Le modèle du PV est introuvable.");
        $htmlContent = file_get_contents($templatePath);

        $variables = [
            '{{titre_pv}}' => htmlspecialchars($pv['libelle_compte_rendu']),
            '{{contenu_pv}}' => nl2br(htmlspecialchars($pv['contenu'])), // nl2br pour conserver les sauts de ligne
            '{{date_creation_pv}}' => date('d/m/Y', strtotime($pv['date_creation_pv']))
        ];
        $htmlFinal = strtr($htmlContent, $variables);

        return $this->genererPdfDepuisHtml($htmlFinal, 'PV', $idCompteRendu, 'DOC_PV', $pv['id_redacteur']);
    }

    public function genererRecuPaiement(string $idInscription): string
    {
        // ... (logique pour décomposer l'ID composite de l'inscription si nécessaire)
        $inscription = $this->inscrireModel->trouverUnParCritere(['id_inscription' => $idInscription]); // Suppose un ID unique
        if (!$inscription) throw new ElementNonTrouveException("Inscription non trouvée.");

        $htmlContent = "<h1>Reçu de Paiement</h1>";
        $htmlContent .= "<p>Reçu N°: " . htmlspecialchars($inscription['numero_recu_paiement']) . "</p>";
        $htmlContent .= "<p>Date de paiement: " . htmlspecialchars($inscription['date_paiement']) . "</p>";
        $htmlContent .= "<p>Montant: " . htmlspecialchars($inscription['montant_inscription']) . " €</p>";

        return $this->genererPdfDepuisHtml($htmlContent, 'RecuPaiement', $idInscription, 'DOC_RECU', $inscription['numero_carte_etudiant']);
    }

    public function genererRapportEtudiantPdf(string $idRapport): string
    {
        $rapport = $this->rapportModel->trouverParIdentifiant($idRapport);
        if (!$rapport) throw new ElementNonTrouveException("Rapport non trouvé.");
        $sections = $this->sectionRapportModel->trouverParCritere(['id_rapport_etudiant' => $idRapport], ['*'], 'AND', 'ordre ASC');

        $htmlContent = "<h1>" . htmlspecialchars($rapport['libelle_rapport_etudiant']) . "</h1>";
        $htmlContent .= "<h2>Thème : " . htmlspecialchars($rapport['theme']) . "</h2>";
        $htmlContent .= "<h3>Résumé</h3><div>" . $rapport['resume'] . "</div>"; // Le contenu est déjà du HTML

        foreach ($sections as $section) {
            $htmlContent .= "<h3>" . htmlspecialchars($section['titre_section']) . "</h3>";
            $htmlContent .= "<div>" . $section['contenu_section'] . "</div>";
        }

        return $this->genererPdfDepuisHtml($htmlContent, 'Rapport', $idRapport, 'DOC_RAPPORT', $rapport['numero_carte_etudiant']);
    }

    public function genererListePdf(string $nomListe, array $donnees, array $colonnes): string
    {
        if (empty($donnees)) throw new OperationImpossibleException("Aucune donnée à exporter.");

        $htmlContent = "<h1>Liste : " . htmlspecialchars($nomListe) . "</h1>";
        $htmlContent .= '<table border="1" cellpadding="4" cellspacing="0" style="width:100%; border-collapse: collapse;"><thead><tr style="background-color:#f2f2f2;">';
        foreach ($colonnes as $libelle) {
            $htmlContent .= '<th>' . htmlspecialchars($libelle) . '</th>';
        }
        $htmlContent .= '</tr></thead><tbody>';
        foreach ($donnees as $ligne) {
            $htmlContent .= '<tr>';
            foreach (array_keys($colonnes) as $key) {
                $htmlContent .= '<td>' . htmlspecialchars($ligne[$key] ?? '') . '</td>';
            }
            $htmlContent .= '</tr>';
        }
        $htmlContent .= '</tbody></table>';

        $idEntite = 'export_' . str_replace(' ', '_', $nomListe) . '_' . time();
        return $this->genererPdfDepuisHtml($htmlContent, 'ExportListe', $idEntite, 'DOC_EXPORT', $_SESSION['user_id'] ?? 'SYSTEM');
    }

    // --- Section 2: Gestion des Fichiers Uploadés ---

    public function uploadFichierSecurise(array $fileData, string $destinationType, array $allowedMimeTypes, int $maxSizeInBytes): string
    {
        if (!isset($fileData['error']) || is_array($fileData['error']) || $fileData['error'] !== UPLOAD_ERR_OK) {
            throw new ValidationException("Erreur lors de l'upload du fichier.");
        }
        if ($fileData['size'] > $maxSizeInBytes) {
            throw new ValidationException("Le fichier est trop volumineux.");
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($fileData['tmp_name']);
        if (!in_array($mimeType, $allowedMimeTypes)) {
            throw new ValidationException("Le type de fichier '{$mimeType}' n'est pas autorisé.");
        }

        $uploadBasePath = $this->systemeService->getParametre('UPLOADS_PATH_BASE', ROOT_PATH . '/Public/uploads/');
        $destinationPath = $uploadBasePath . $destinationType;
        if (!is_dir($destinationPath)) mkdir($destinationPath, 0755, true);

        $safeFileName = bin2hex(random_bytes(16)) . '.' . pathinfo($fileData['name'], PATHINFO_EXTENSION);
        $filePath = $destinationPath . '/' . $safeFileName;

        if (!move_uploaded_file($fileData['tmp_name'], $filePath)) {
            throw new OperationImpossibleException("Échec du déplacement du fichier uploadé.");
        }

        $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'UPLOAD_FICHIER', null, $filePath, 'Fichier');
        return $destinationType . '/' . $safeFileName; // Retourne le chemin relatif
    }

    public function supprimerFichier(string $relativePath): bool
    {
        $uploadBasePath = $this->systemeService->getParametre('UPLOADS_PATH_BASE', ROOT_PATH . '/Public/uploads/');
        $fullPath = $uploadBasePath . $relativePath;

        if (file_exists($fullPath) && is_file($fullPath)) {
            if (unlink($fullPath)) {
                $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'DELETE_FICHIER', null, $relativePath, 'Fichier');
                return true;
            }
        }
        return false;
    }
    // --- Méthodes privées techniques ---
    private function genererPdfAvecVersionning(string $html, string $prefixeNomFichier, string $idEntite, string $typeDocumentRef, ?string $numeroUtilisateurConcerne): string
    {
        $anciennesVersions = $this->documentGenereModel->trouverParCritere(['id_entite_concernee' => $idEntite, 'id_type_document' => $typeDocumentRef], ['*'], 'AND', 'version DESC');
        $nouvelleVersion = 1;
        if (!empty($anciennesVersions)) {
            $derniereVersion = $anciennesVersions[0];
            $this->documentGenereModel->mettreAJourParIdentifiant($derniereVersion['id_document_genere'], ['est_archive' => 1]);
            $nouvelleVersion = $derniereVersion['version'] + 1;
        }
        return $this->genererPdfDepuisHtml($html, $prefixeNomFichier, $idEntite, $typeDocumentRef, $numeroUtilisateurConcerne, $nouvelleVersion);
    }

    private function genererPdfDepuisHtml(string $html, string $prefixeNomFichier, string $idEntite, string $typeDocumentRef, ?string $numeroUtilisateurConcerne, int $version = 1): string
    {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('GestionMySoutenance');
        $pdf->SetAuthor('Université XYZ');
        $pdf->SetTitle($prefixeNomFichier . ' - ' . $idEntite);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');

        $uploadBasePath = $this->systemeService->getParametre('UPLOADS_PATH_BASE', ROOT_PATH . '/Public/uploads/');
        $destinationPath = $uploadBasePath . 'documents_generes';
        if (!is_dir($destinationPath)) mkdir($destinationPath, 0755, true);

        $filename = "{$prefixeNomFichier}_{$idEntite}_v{$version}_" . time() . ".pdf";
        $absoluteFilePath = $destinationPath . '/' . $filename;
        $pdf->Output($absoluteFilePath, 'F');

        if (!file_exists($absoluteFilePath)) throw new OperationImpossibleException("Échec de la sauvegarde du fichier PDF sur le serveur.");

        $idDocumentGenere = $this->systemeService->genererIdentifiantUnique('DOC');
        $this->documentGenereModel->creer([
            'id_document_genere' => $idDocumentGenere,
            'id_type_document' => $typeDocumentRef,
            'chemin_fichier' => 'documents_generes/' . $filename,
            'version' => $version,
            'id_entite_concernee' => $idEntite,
            'type_entite_concernee' => $prefixeNomFichier,
            'numero_utilisateur_concerne' => $numeroUtilisateurConcerne,
            'est_archive' => 0
        ]);

        $this->supervisionService->enregistrerAction('SYSTEM', 'GENERATION_DOCUMENT', $idDocumentGenere, 'DocumentGenere', ['type' => $typeDocumentRef, 'version' => $version]);

        return $idDocumentGenere;
    }
}