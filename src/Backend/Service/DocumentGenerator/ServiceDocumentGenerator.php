<?php
namespace App\Backend\Service\DocumentGenerator;

use PDO;
use TCPDF;
use App\Backend\Model\DocumentGenere;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGeneratorInterface;

class ServiceDocumentGenerator implements ServiceDocumentGeneratorInterface
{
    private DocumentGenere $documentGenereModel;
    private IdentifiantGeneratorInterface $idGenerator;
    private string $storagePath;

    public function __construct(PDO $db, IdentifiantGeneratorInterface $idGenerator)
    {
        $this->documentGenereModel = new DocumentGenere($db);
        $this->idGenerator = $idGenerator;
        $this->storagePath = __DIR__ . '/../../../../Public/uploads/documents/';
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0777, true);
        }
    }

    private function generatePdf(string $htmlContent, string $fileName): string
    {
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->writeHTML($htmlContent, true, false, true, false, '');
        $filePath = $this->storagePath . $fileName;
        $pdf->Output($filePath, 'F');
        return $filePath;
    }

    public function genererPvValidation(string $idCompteRendu): string
    {
        $html = "<h1>PV de Validation #{$idCompteRendu}</h1><p>Contenu...</p>";
        $fileName = "PV_{$idCompteRendu}.pdf";
        $filePath = $this->generatePdf($html, $fileName);
        $this->documentGenereModel->creer([
            'id_document' => $this->idGenerator->generate('document_genere'),
            'id_type_document' => 'PV_VALIDATION',
            'chemin_fichier' => $filePath,
            'id_entite_concernee' => $idCompteRendu,
            'type_entite_concernee' => 'CompteRendu'
        ]);
        return $filePath;
    }

    public function genererAttestationScolarite(string $numeroEtudiant): string
    {
        $html = "<h1>Attestation de Scolarité pour {$numeroEtudiant}</h1><p>Contenu...</p>";
        $fileName = "Attestation_{$numeroEtudiant}.pdf";
        $filePath = $this->generatePdf($html, $fileName);
        $this->documentGenereModel->creer([
            'id_document' => $this->idGenerator->generate('document_genere'),
            'id_type_document' => 'ATTESTATION_SCOLARITE',
            'chemin_fichier' => $filePath,
            'id_entite_concernee' => $numeroEtudiant,
            'type_entite_concernee' => 'Etudiant'
        ]);
        return $filePath;
    }

    public function genererBulletinNotes(string $numeroEtudiant, string $idAnneeAcademique): string
    {
        $html = "<h1>Bulletin de Notes pour {$numeroEtudiant} - Année {$idAnneeAcademique}</h1><p>Contenu...</p>";
        $fileName = "Bulletin_{$numeroEtudiant}_{$idAnneeAcademique}.pdf";
        $filePath = $this->generatePdf($html, $fileName);
        $this->documentGenereModel->creer([
            'id_document' => $this->idGenerator->generate('document_genere'),
            'id_type_document' => 'BULLETIN_NOTES',
            'chemin_fichier' => $filePath,
            'id_entite_concernee' => $numeroEtudiant,
            'type_entite_concernee' => 'Etudiant'
        ]);
        return $filePath;
    }
}