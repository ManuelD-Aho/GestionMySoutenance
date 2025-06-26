<?php

namespace App\Backend\Service\DocumentAdministratif;

use App\Backend\Exception\OperationImpossibleException;
use PDO;
use App\Backend\Service\DocumentGenerator\ServiceDocumentGeneratorInterface;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Model\DocumentGenere;
use App\Backend\Model\Inscrire;
use App\Backend\Exception\ElementNonTrouveException;

class ServiceDocumentAdministratif implements ServiceDocumentAdministratifInterface
{
    private ServiceDocumentGeneratorInterface $documentGeneratorService;
    private ServiceSupervisionAdminInterface $supervisionService;
    private DocumentGenere $documentGenereModel;
    private Inscrire $inscrireModel;

    public function __construct(
        PDO $db,
        ServiceDocumentGeneratorInterface $documentGeneratorService,
        ServiceSupervisionAdminInterface $supervisionService,
        DocumentGenere $documentGenereModel,
        Inscrire $inscrireModel
    ) {
        $this->documentGeneratorService = $documentGeneratorService;
        $this->supervisionService = $supervisionService;
        $this->documentGenereModel = $documentGenereModel;
        $this->inscrireModel = $inscrireModel;
    }

    public function genererAttestationScolarite(string $numeroEtudiant, string $idAnneeAcademique): string
    {
        $filePath = $this->documentGeneratorService->genererAttestationScolarite($numeroEtudiant, $idAnneeAcademique);

        $this->supervisionService->enregistrerAction(
            $_SESSION['user_id'] ?? 'SYSTEM',
            'GENERATION_ATTESTATION',
            "Attestation de scolarité générée pour l'étudiant {$numeroEtudiant} pour l'année {$idAnneeAcademique}.",
            $numeroEtudiant,
            'Etudiant'
        );

        return $filePath;
    }

    public function genererBulletinNotes(string $numeroEtudiant, string $idAnneeAcademique): string
    {
        $filePath = $this->documentGeneratorService->genererBulletinNotes($numeroEtudiant, $idAnneeAcademique);

        $this->supervisionService->enregistrerAction(
            $_SESSION['user_id'] ?? 'SYSTEM',
            'GENERATION_BULLETIN',
            "Bulletin de notes généré pour l'étudiant {$numeroEtudiant} pour l'année {$idAnneeAcademique}.",
            $numeroEtudiant,
            'Etudiant'
        );

        return $filePath;
    }

    public function genererRecuPaiement(string $idInscription): string
    {
        $keys = explode(':', base64_decode($idInscription));
        if (count($keys) !== 3) {
            throw new \InvalidArgumentException("ID d'inscription invalide.");
        }
        list($numeroCarteEtudiant, $idNiveauEtude, $idAnneeAcademique) = $keys;

        $inscription = $this->inscrireModel->trouverParCleComposite($numeroCarteEtudiant, $idNiveauEtude, $idAnneeAcademique);
        if (!$inscription) {
            throw new ElementNonTrouveException("Inscription non trouvée.");
        }

        if ($inscription['id_statut_paiement'] !== 'PAIE_OK') {
            throw new OperationImpossibleException("Impossible de générer un reçu pour une inscription non payée.");
        }

        $filePath = $this->documentGeneratorService->genererRecuPaiement($inscription);

        $this->supervisionService->enregistrerAction(
            $_SESSION['user_id'] ?? 'SYSTEM',
            'GENERATION_RECU_PAIEMENT',
            "Reçu de paiement généré pour l'inscription de {$numeroCarteEtudiant}.",
            $idInscription,
            'Inscription'
        );

        return $filePath;
    }

    public function listerDocumentsGeneresParPersonnel(string $numeroPersonnel): array
    {
        $logs = $this->supervisionService->consulterJournauxActionsUtilisateurs([
            'numero_utilisateur' => $numeroPersonnel,
            'id_action' => ['operator' => 'in', 'values' => [
                'GENERATION_ATTESTATION',
                'GENERATION_BULLETIN',
                'GENERATION_RECU_PAIEMENT'
            ]]
        ], 1000);

        $idsDocuments = [];
        foreach ($logs as $log) {
            if (isset($log['details_action_decoded']['document_id'])) {
                $idsDocuments[] = $log['details_action_decoded']['document_id'];
            }
        }

        if (empty($idsDocuments)) {
            return [];
        }

        return $this->documentGenereModel->trouverParCritere([
            'id_document_genere' => ['operator' => 'in', 'values' => array_unique($idsDocuments)]
        ]);
    }
}