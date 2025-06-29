<?php

declare(strict_types=1);

namespace App\Backend\Service;

use TCPDF;
use App\Backend\Model\RapportModele;
use App\Backend\Service\Interface\DocumentGeneratorServiceInterface;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\ModeleNonTrouveException;

class ServiceDocumentGenerator implements DocumentGeneratorServiceInterface
{
    private RapportModele $rapportModeleModel;
    private string $uploadDir;

    public function __construct(RapportModele $rapportModeleModel)
    {
        $this->rapportModeleModel = $rapportModeleModel;
        $this->uploadDir = __DIR__ . '/../../../Public/uploads/documents_generes/';
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function genererPdfDepuisHtml(string $htmlContent, array $options = []): string
    {
        try {
            $pdf = new TCPDF(
                $options['orientation'] ?? 'P',
                'mm',
                $options['format'] ?? 'A4',
                true,
                'UTF-8',
                false
            );

            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('GestionMySoutenance');
            $pdf->SetTitle($options['title'] ?? 'Document Généré');
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(true);
            $pdf->AddPage();
            $pdf->writeHTML($htmlContent, true, false, true, false, '');

            $filename = uniqid('doc_', true) . '.pdf';
            $filepath = $this->uploadDir . $filename;
            $pdf->Output($filepath, 'F');

            return 'documents_generes/' . $filename;
        } catch (\Exception $e) {
            throw new OperationImpossibleException("Erreur lors de la génération du PDF: " . $e->getMessage());
        }
    }

    public function genererPdfDepuisTemplate(string $templateCode, array $variables): string
    {
        $template = $this->rapportModeleModel->trouverUnParCritere(['code_modele' => $templateCode]);
        if (!$template) {
            throw new ModeleNonTrouveException("Le modèle de document '{$templateCode}' n'a pas été trouvé.");
        }

        $htmlContent = $template['contenu_html'];
        foreach ($variables as $key => $value) {
            if (is_scalar($value)) {
                $htmlContent = str_replace("{{{$key}}}", htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'), $htmlContent);
            }
        }

        return $this->genererPdfDepuisHtml($htmlContent, ['title' => $template['nom_modele']]);
    }

    public function ajouterFiligrane(string $cheminPdf, string $texte): bool
    {
        $filepath = $this->uploadDir . basename($cheminPdf);
        if (!file_exists($filepath)) return false;

        $pdf = new TCPDF();
        $pagecount = $pdf->setSourceFile($filepath);

        for ($i = 1; $i <= $pagecount; $i++) {
            $tpl = $pdf->importPage($i);
            $pdf->AddPage();
            $pdf->useTemplate($tpl);

            $pdf->SetFont('helvetica', 'B', 50);
            $pdf->SetTextColor(200, 200, 200);
            $pdf->setAlpha(0.5);
            $pdf->Rotate(45, 100, 100);
            $pdf->Text(70, 120, $texte);
            $pdf->Rotate(0);
            $pdf->setAlpha(1);
        }

        $pdf->Output($filepath, 'F');
        return true;
    }

    public function fusionnerPdfs(array $cheminsPdfs): string
    {
        $pdf = new TCPDF();
        foreach ($cheminsPdfs as $chemin) {
            $filepath = $this->uploadDir . basename($chemin);
            if (!file_exists($filepath)) continue;

            $pagecount = $pdf->setSourceFile($filepath);
            for ($i = 1; $i <= $pagecount; $i++) {
                $tpl = $pdf->importPage($i);
                $pdf->AddPage();
                $pdf->useTemplate($tpl);
            }
        }

        $filename = 'fusion_' . uniqid() . '.pdf';
        $filepath = $this->uploadDir . $filename;
        $pdf->Output($filepath, 'F');

        return 'documents_generes/' . $filename;
    }

    public function signerPdf(string $cheminPdf, array $infosSignature): bool
    {
        $filepath = $this->uploadDir . basename($cheminPdf);
        if (!file_exists($filepath)) return false;

        $pdf = new TCPDF();
        $certificate = 'file://' . realpath($infosSignature['cert_path']);
        $privateKey = 'file://' . realpath($infosSignature['key_path']);

        $info = [
            'Name' => 'GestionMySoutenance',
            'Location' => 'Université Virtuelle',
            'Reason' => $infosSignature['reason'] ?? 'Signature du document',
            'ContactInfo' => 'http://gestionsoutenance.test',
        ];

        $pdf->setSignature($certificate, $privateKey, $infosSignature['password'], '', 2, $info);

        $pagecount = $pdf->setSourceFile($filepath);
        for ($i = 1; $i <= $pagecount; $i++) {
            $tpl = $pdf->importPage($i);
            $pdf->AddPage();
            $pdf->useTemplate($tpl);
        }

        // La signature est appliquée automatiquement lors du Output
        $pdf->Output($filepath, 'F');
        return true;
    }
}