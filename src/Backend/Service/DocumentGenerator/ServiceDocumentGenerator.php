<?php

namespace App\Backend\Service\DocumentGenerator;

use PDO;
use TCPDF;
use App\Backend\Model\CompteRendu;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\Etudiant;
use App\Backend\Model\Inscrire;
use App\Backend\Model\Evaluer;
use App\Backend\Model\AnneeAcademique;
use App\Backend\Model\DocumentGenere;
use App\Backend\Model\PvSessionRapport;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGeneratorInterface;
use App\Backend\Service\ConfigurationSysteme\ServiceConfigurationSystemeInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceDocumentGenerator implements ServiceDocumentGeneratorInterface
{
    private CompteRendu $compteRenduModel;
    private RapportEtudiant $rapportEtudiantModel;
    private Etudiant $etudiantModel;
    private Inscrire $inscrireModel;
    private Evaluer $evaluerModel;
    private AnneeAcademique $anneeAcademiqueModel;
    private DocumentGenere $documentGenereModel;
    private PvSessionRapport $pvSessionRapportModel;
    private ServiceSupervisionAdminInterface $supervisionService;
    private IdentifiantGeneratorInterface $idGenerator;
    private ServiceConfigurationSystemeInterface $configService;

    public function __construct(
        PDO $db,
        CompteRendu $compteRenduModel,
        RapportEtudiant $rapportEtudiantModel,
        Etudiant $etudiantModel,
        Inscrire $inscrireModel,
        Evaluer $evaluerModel,
        AnneeAcademique $anneeAcademiqueModel,
        DocumentGenere $documentGenereModel,
        PvSessionRapport $pvSessionRapportModel,
        ServiceSupervisionAdminInterface $supervisionService,
        IdentifiantGeneratorInterface $idGenerator,
        ServiceConfigurationSystemeInterface $configService
    ) {
        $this->compteRenduModel = $compteRenduModel;
        $this->rapportEtudiantModel = $rapportEtudiantModel;
        $this->etudiantModel = $etudiantModel;
        $this->inscrireModel = $inscrireModel;
        $this->evaluerModel = $evaluerModel;
        $this->anneeAcademiqueModel = $anneeAcademiqueModel;
        $this->documentGenereModel = $documentGenereModel;
        $this->pvSessionRapportModel = $pvSessionRapportModel;
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
        $this->configService = $configService;
    }

    public function getDocumentGenereModel(): DocumentGenere
    {
        return $this->documentGenereModel;
    }

    public function genererPvValidation(string $idCompteRendu): string
    {
        $pvData = $this->compteRenduModel->trouverParIdentifiant($idCompteRendu);
        if (!$pvData) {
            throw new ElementNonTrouveException("PV non trouvé pour la génération.");
        }

        $htmlContent = "<h1>Procès-Verbal de Validation : " . htmlspecialchars($pvData['libelle_compte_rendu']) . "</h1>";
        $htmlContent .= "<p>Date de création : " . htmlspecialchars($pvData['date_creation_pv']) . "</p>";
        $htmlContent .= "<p>Type : " . htmlspecialchars($pvData['type_pv']) . "</p>";
        $htmlContent .= "<h2>Contenu du PV :</h2><p>" . nl2br(htmlspecialchars($pvData['libelle_compte_rendu'])) . "</p>";

        return $this->genererPdfDepuisHtml($htmlContent, 'PV', $idCompteRendu, 'DOC_PV', $pvData['id_rapport_etudiant'] ?? null);
    }

    public function genererAttestationScolarite(string $numeroEtudiant, string $idAnneeAcademique): string
    {
        $etudiantData = $this->etudiantModel->trouverParIdentifiant($numeroEtudiant);
        if (!$etudiantData) {
            throw new ElementNonTrouveException("Étudiant non trouvé pour l'attestation.");
        }
        $anneeData = $this->anneeAcademiqueModel->trouverParIdentifiant($idAnneeAcademique);
        if (!$anneeData) {
            throw new ElementNonTrouveException("Année académique non trouvée.");
        }

        $htmlContent = "<h1>Attestation de Scolarité</h1>";
        $htmlContent .= "<p>Attestons que l'étudiant(e) " . htmlspecialchars($etudiantData['prenom'] . " " . $etudiantData['nom']) . "</p>";
        $htmlContent .= "<p>Né(e) le " . htmlspecialchars($etudiantData['date_naissance']) . "</p>";
        $htmlContent .= "<p>Est bien inscrit(e) pour l'année académique " . htmlspecialchars($anneeData['libelle_annee_academique']) . ".</p>";

        return $this->genererPdfDepuisHtml($htmlContent, 'Attestation', $numeroEtudiant, 'DOC_ATTESTATION', $numeroEtudiant);
    }

    public function genererBulletinNotes(string $numeroEtudiant, string $idAnneeAcademique): string
    {
        $etudiantData = $this->etudiantModel->trouverParIdentifiant($numeroEtudiant);
        if (!$etudiantData) {
            throw new ElementNonTrouveException("Étudiant non trouvé pour le bulletin.");
        }
        $anneeData = $this->anneeAcademiqueModel->trouverParIdentifiant($idAnneeAcademique);
        if (!$anneeData) {
            throw new ElementNonTrouveException("Année académique non trouvée.");
        }

        $notes = $this->evaluerModel->trouverParCritere(['numero_carte_etudiant' => $numeroEtudiant, 'id_annee_academique' => $idAnneeAcademique]);

        $htmlContent = "<h1>Bulletin de Notes</h1>";
        $htmlContent .= "<p>Étudiant: " . htmlspecialchars($etudiantData['prenom'] . " " . $etudiantData['nom']) . "</p>";
        $htmlContent .= "<p>Année Académique: " . htmlspecialchars($anneeData['libelle_annee_academique']) . "</p>";
        $htmlContent .= "<h2>Notes :</h2><ul>";
        foreach ($notes as $note) {
            $htmlContent .= "<li>ECUE ID: " . htmlspecialchars($note['id_ecue']) . " - Note: " . htmlspecialchars($note['note']) . "</li>";
        }
        $htmlContent .= "</ul>";

        return $this->genererPdfDepuisHtml($htmlContent, 'Bulletin', $numeroEtudiant, 'DOC_BULLETIN', $numeroEtudiant);
    }

    public function genererRecuPaiement(array $inscription): string
    {
        $htmlContent = "<h1>Reçu de Paiement</h1>";
        $htmlContent .= "<p>Reçu N°: " . htmlspecialchars($inscription['numero_recu_paiement']) . "</p>";
        $htmlContent .= "<p>Date de paiement: " . htmlspecialchars($inscription['date_paiement']) . "</p>";
        $htmlContent .= "<p>Montant: " . htmlspecialchars($inscription['montant_inscription']) . " €</p>";

        return $this->genererPdfDepuisHtml($htmlContent, 'Recu', $inscription['numero_carte_etudiant'], 'DOC_RECU', $inscription['numero_carte_etudiant']);
    }

    private function genererPdfDepuisHtml(string $html, string $prefixeNomFichier, string $idEntite, string $typeDocumentRef, ?string $numeroUtilisateurConcerne): string
    {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('GestionMySoutenance');
        $pdf->SetTitle($prefixeNomFichier . ' - ' . $idEntite);
        $pdf->SetSubject('Document Généré par GestionMySoutenance');
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');

        $params = $this->configService->recupererParametresGeneraux();
        $uploadDir = $params['UPLOADS_PATH_DOCUMENTS_GENERES'] ?? ROOT_PATH . '/Public/uploads/documents_generes/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = $prefixeNomFichier . "_" . str_replace(['-', ':', ' '], '_', $idEntite) . "_" . time() . ".pdf";
        $absoluteFilePath = $uploadDir . $filename;
        $pdf->Output($absoluteFilePath, 'F');

        if (!file_exists($absoluteFilePath)) {
            throw new OperationImpossibleException("Échec de la sauvegarde du fichier PDF.");
        }

        $idDocumentGenere = $this->idGenerator->genererIdentifiantUnique('DOC');
        $this->documentGenereModel->creer([
            'id_document_genere' => $idDocumentGenere,
            'id_type_document' => $typeDocumentRef,
            'chemin_fichier' => 'documents_generes/' . $filename,
            'id_entite_concernee' => $idEntite,
            'type_entite_concernee' => $prefixeNomFichier,
            'numero_utilisateur_concerne' => $numeroUtilisateurConcerne
        ]);

        return 'documents_generes/' . $filename;
    }
}