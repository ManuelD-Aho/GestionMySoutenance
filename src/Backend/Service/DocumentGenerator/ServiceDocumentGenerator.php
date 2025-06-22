<?php
namespace App\Backend\Service\DocumentGenerator;

use PDO;
// Importer les modèles nécessaires pour les données des documents
use App\Backend\Model\CompteRendu;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\Etudiant;
use App\Backend\Model\Inscrire;
use App\Backend\Model\Evaluer;
use App\Backend\Model\AnneeAcademique;
use App\Backend\Model\DocumentGenere; // Nouveau modèle
use App\Backend\Model\PvSessionRapport;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGenerator;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

// Exemple avec une librairie PDF fictive, remplacez par Dompdf, TCPDF, etc.
// use Dompdf\Dompdf; // Si vous utilisez Dompdf

class ServiceDocumentGenerator implements ServiceDocumentGeneratorInterface
{
    private CompteRendu $compteRenduModel;
    private RapportEtudiant $rapportEtudiantModel;
    private Etudiant $etudiantModel;
    private Inscrire $inscrireModel;
    private Evaluer $evaluerModel;
    private AnneeAcademique $anneeAcademiqueModel;
    private DocumentGenere $documentGenereModel;
    private PvSessionRapport $pvSessionRapportModel; // Nouveau modèle pour gérer les liaisons PV-Session-Rapport
    private ServiceSupervisionAdmin $supervisionService;
    private IdentifiantGenerator $idGenerator;

    public function __construct(
        PDO $db,
        ServiceSupervisionAdmin $supervisionService,
        PvSessionRapport $pvSessionRapportModel,
        IdentifiantGenerator $idGenerator
    ) {
        $this->compteRenduModel = new CompteRendu($db);
        $this->rapportEtudiantModel = new RapportEtudiant($db);
        $this->etudiantModel = new Etudiant($db);
        $this->inscrireModel = new Inscrire($db);
        $this->evaluerModel = new Evaluer($db);
        $this->anneeAcademiqueModel = new AnneeAcademique($db);
        $this->documentGenereModel = new DocumentGenere($db); // Initialisation
        $this->pvSessionRapportModel = new PvSessionRapport($db); // Nouveau modèle pour gérer les liaisons PV-Session-Rapport
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
    }

    /**
     * Retourne le modèle DocumentGenere pour des opérations externes si nécessaire.
     * @return DocumentGenere
     */
    public function getDocumentGenereModel(): DocumentGenere
    {
        return $this->documentGenereModel;
    }


    /**
     * Génère un Procès-Verbal de validation au format PDF.
     * @param string $idCompteRendu L'ID du PV à générer.
     * @return string Le chemin vers le fichier PDF généré.
     * @throws ElementNonTrouveException Si le PV n'est pas trouvé.
     * @throws OperationImpossibleException En cas d'échec de la génération du PDF.
     */
    public function genererPvValidation(string $idCompteRendu): string
    {
        $pvData = $this->compteRenduModel->trouverParIdentifiant($idCompteRendu);
        if (!$pvData) {
            throw new ElementNonTrouveException("PV non trouvé pour la génération.");
        }

        // Récupérer les données supplémentaires pour le PV (rapport, étudiants, membres jury, etc.)
        $rapportsAssocies = [];
        if ($pvData['type_pv'] === 'Individuel' && $pvData['id_rapport_etudiant']) {
            $rapportsAssocies[] = $this->rapportEtudiantModel->trouverParIdentifiant($pvData['id_rapport_etudiant']);
        } elseif ($pvData['type_pv'] === 'Session') {
            // CORRECTION ICI : Utiliser trouverParCritere du modèle PvSessionRapport
            $liaisons = $this->pvSessionRapportModel->trouverParCritere(['id_compte_rendu' => $idCompteRendu], ['id_rapport_etudiant']); // <-- LIGNE MODIFIÉE
            foreach ($liaisons as $liaison) {
                $rapportsAssocies[] = $this->rapportEtudiantModel->trouverParIdentifiant($liaison['id_rapport_etudiant']);
            }
        }

        // Simuler le contenu HTML ou utiliser un moteur de template pour le PDF
        $htmlContent = "<h1>Procès-Verbal de Validation : " . htmlspecialchars($pvData['libelle_compte_rendu']) . "</h1>";
        $htmlContent .= "<p>Date de création : " . htmlspecialchars($pvData['date_creation_pv']) . "</p>";
        $htmlContent .= "<p>Type : " . htmlspecialchars($pvData['type_pv']) . "</p>";
        $htmlContent .= "<h2>Contenu du PV :</h2><p>" . nl2br(htmlspecialchars($pvData['libelle_compte_rendu'])) . "</p>";

        if (!empty($rapportsAssocies)) {
            $htmlContent .= "<h3>Rapports Associés :</h3><ul>";
            foreach ($rapportsAssocies as $rapport) {
                if ($rapport) {
                    $htmlContent .= "<li>Rapport ID: " . htmlspecialchars($rapport['id_rapport_etudiant']) . " - Titre: " . htmlspecialchars($rapport['libelle_rapport_etudiant']) . "</li>";
                }
            }
            $htmlContent .= "</ul>";
        }

        // Définir un chemin de sauvegarde sécurisé (hors du répertoire public)
        $uploadDir = __DIR__ . '/../../../Public/uploads/documents_generes/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $filename = "PV_" . str_replace('-', '_', $idCompteRendu) . "_" . time() . ".pdf";
        $filePath = $uploadDir . $filename;

        // Simuler l'écriture du fichier
        file_put_contents($filePath, "Contenu simulé du PV pour l'ID {$idCompteRendu}.");

        if (!file_exists($filePath)) {
            throw new OperationImpossibleException("Échec de la sauvegarde du fichier PV.");
        }

        $this->supervisionService->enregistrerAction(
            $_SESSION['user_id'] ?? 'SYSTEM',
            'GENERATION_PV_VALIDATION',
            "PV '{$idCompteRendu}' généré : {$filePath}",
            $idCompteRendu,
            'CompteRendu'
        );

        return $filePath;
    }

    /**
     * Génère une attestation de scolarité au format PDF.
     * @param string $numeroEtudiant Le numéro de carte de l'étudiant.
     * @param string $typeAttestation Le type d'attestation (ex: 'inscription', 'reussite').
     * @return string Le chemin vers le fichier PDF généré.
     * @throws ElementNonTrouveException Si l'étudiant ou les données nécessaires ne sont pas trouvés.
     * @throws OperationImpossibleException En cas d'échec de la génération.
     */
    public function genererAttestationScolarite(string $numeroEtudiant, string $typeAttestation): string
    {
        $etudiantData = $this->etudiantModel->trouverParNumeroCarteEtudiant($numeroEtudiant);
        if (!$etudiantData) {
            throw new ElementNonTrouveException("Étudiant non trouvé pour l'attestation.");
        }

        $anneeAcademiqueActive = $this->anneeAcademiqueModel->trouverUnParCritere(['est_active' => 1]);
        if (!$anneeAcademiqueActive) {
            throw new OperationImpossibleException("Aucune année académique active trouvée.");
        }

        $inscriptionData = $this->inscrireModel->trouverParCleComposite($numeroEtudiant, $etudiantData['id_niveau_etude'] ?? '', $anneeAcademiqueActive['id_annee_academique']);
        // Vous auriez besoin des détails d'inscription pour l'année active
        // $inscriptionData = $this->inscrireModel->trouverParCleComposite($numeroEtudiant, $idNiveauEtudeActuel, $anneeAcademiqueActive['id_annee_academique']);
        // Gérer le cas où id_niveau_etude est dans la table Etudiant ou Inscrire.

        $htmlContent = "<h1>Attestation de Scolarité</h1>";
        $htmlContent .= "<p>Attestons que l'étudiant(e) " . htmlspecialchars($etudiantData['prenom'] . " " . $etudiantData['nom']) . "</p>";
        $htmlContent .= "<p>Né(e) le " . htmlspecialchars($etudiantData['date_naissance']) . "</p>";
        $htmlContent .= "<p>Est bien inscrit(e) en " . htmlspecialchars($etudiantData['id_niveau_etude'] ?? 'Niveau non spécifié') . " pour l'année académique " . htmlspecialchars($anneeAcademiqueActive['libelle_annee_academique']) . ".</p>";
        // Ajouter plus de détails selon le type d'attestation

        $uploadDir = __DIR__ . '/../../../public/uploads/documents_generes/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
        $filename = "Attestation_" . str_replace('-', '_', $numeroEtudiant) . "_" . time() . ".pdf";
        $filePath = $uploadDir . $filename;
        file_put_contents($filePath, "Contenu simulé de l'attestation pour {$numeroEtudiant}."); // Simuler

        if (!file_exists($filePath)) {
            throw new OperationImpossibleException("Échec de la sauvegarde de l'attestation.");
        }

        $this->supervisionService->enregistrerAction(
            $_SESSION['user_id'] ?? 'SYSTEM',
            'GENERATION_ATTESTATION',
            "Attestation de scolarité '{$typeAttestation}' générée pour {$numeroEtudiant}",
            $numeroEtudiant,
            'Etudiant'
        );
        return $filePath;
    }

    /**
     * Génère un bulletin de notes pour un étudiant pour une année académique donnée.
     * @param string $numeroEtudiant Le numéro de carte de l'étudiant.
     * @param string $idAnneeAcademique L'ID de l'année académique.
     * @return string Le chemin vers le fichier PDF généré.
     * @throws ElementNonTrouveException Si l'étudiant ou l'année académique n'est pas trouvée.
     * @throws OperationImpossibleException En cas d'échec de la génération.
     */
    public function genererBulletinNotes(string $numeroEtudiant, string $idAnneeAcademique): string
    {
        $etudiantData = $this->etudiantModel->trouverParNumeroCarteEtudiant($numeroEtudiant);
        if (!$etudiantData) {
            throw new ElementNonTrouveException("Étudiant non trouvé pour le bulletin.");
        }
        $anneeData = $this->anneeAcademiqueModel->trouverParIdentifiant($idAnneeAcademique);
        if (!$anneeData) {
            throw new ElementNonTrouveException("Année académique non trouvée pour le bulletin.");
        }

        // Récupérer toutes les notes de l'étudiant pour l'année donnée
        // Cette partie nécessitera des jointures si les notes sont liées à l'année académique par les inscriptions
        // Pour l'exemple, nous allons juste récupérer les notes directes.
        $notes = $this->evaluerModel->trouverParCritere(['numero_carte_etudiant' => $numeroEtudiant]);
        // Filtrer notes par année si ECUEs liés aux années/niveaux ou ajouter date critere.

        $htmlContent = "<h1>Bulletin de Notes</h1>";
        $htmlContent .= "<p>Étudiant: " . htmlspecialchars($etudiantData['prenom'] . " " . $etudiantData['nom']) . "</p>";
        $htmlContent .= "<p>Année Académique: " . htmlspecialchars($anneeData['libelle_annee_academique']) . "</p>";
        $htmlContent .= "<h2>Notes :</h2><ul>";
        foreach ($notes as $note) {
            $htmlContent .= "<li>ECUE ID: " . htmlspecialchars($note['id_ecue']) . " - Note: " . htmlspecialchars($note['note']) . "</li>";
        }
        $htmlContent .= "</ul>";

        $uploadDir = __DIR__ . '/../../../public/uploads/documents_generes/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
        $filename = "Bulletin_" . str_replace('-', '_', $numeroEtudiant) . "_" . str_replace('-', '_', $idAnneeAcademique) . "_" . time() . ".pdf";
        $filePath = $uploadDir . $filename;
        file_put_contents($filePath, "Contenu simulé du bulletin pour {$numeroEtudiant} en {$idAnneeAcademique}."); // Simuler

        if (!file_exists($filePath)) {
            throw new OperationImpossibleException("Échec de la sauvegarde du bulletin.");
        }

        $this->supervisionService->enregistrerAction(
            $_SESSION['user_id'] ?? 'SYSTEM',
            'GENERATION_BULLETIN',
            "Bulletin de notes généré pour {$numeroEtudiant} ({$idAnneeAcademique})",
            $numeroEtudiant,
            'Etudiant'
        );
        return $filePath;
    }
}