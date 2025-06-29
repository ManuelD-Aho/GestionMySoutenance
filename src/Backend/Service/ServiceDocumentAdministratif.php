<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Model\Etudiant;
use App\Backend\Model\Inscrire;
use App\Backend\Model\Evaluer;
use App\Backend\Model\DocumentGenere;
use App\Backend\Service\Interface\DocumentAdministratifServiceInterface;
use App\Backend\Service\Interface\DocumentGeneratorServiceInterface;
use App\Backend\Service\Interface\AuditServiceInterface;
use App\Backend\Service\Interface\IdentifiantGeneratorInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceDocumentAdministratif implements DocumentAdministratifServiceInterface
{
    private PDO $pdo;
    private Etudiant $etudiantModel;
    private Inscrire $inscrireModel;
    private Evaluer $evaluerModel;
    private DocumentGenere $documentGenereModel;
    private DocumentGeneratorServiceInterface $documentGenerator;
    private AuditServiceInterface $auditService;
    private IdentifiantGeneratorInterface $identifiantGenerator;

    public function __construct(
        PDO $pdo,
        Etudiant $etudiantModel,
        Inscrire $inscrireModel,
        Evaluer $evaluerModel,
        DocumentGenere $documentGenereModel,
        DocumentGeneratorServiceInterface $documentGenerator,
        AuditServiceInterface $auditService,
        IdentifiantGeneratorInterface $identifiantGenerator
    ) {
        $this->pdo = $pdo;
        $this->etudiantModel = $etudiantModel;
        $this->inscrireModel = $inscrireModel;
        $this->evaluerModel = $evaluerModel;
        $this->documentGenereModel = $documentGenereModel;
        $this->documentGenerator = $documentGenerator;
        $this->auditService = $auditService;
        $this->identifiantGenerator = $identifiantGenerator;
    }

    public function genererAttestationScolarite(string $numeroEtudiant): string
    {
        $etudiant = $this->etudiantModel->trouverParIdentifiant($numeroEtudiant);
        if (!$etudiant) {
            throw new ElementNonTrouveException("Étudiant non trouvé.");
        }

        $inscription = $this->inscrireModel->trouverUnParCritere(['numero_carte_etudiant' => $numeroEtudiant, 'est_actif' => true]);
        if (!$inscription) {
            throw new ElementNonTrouveException("Aucune inscription active trouvée pour cet étudiant.");
        }

        $variables = array_merge($etudiant, $inscription);
        $cheminFichier = $this->documentGenerator->genererPdfDepuisTemplate('ATTESTATION_SCOLARITE_TPL', $variables);

        return $this->enregistrerDocumentGenere($numeroEtudiant, 'ATTESTATION_SCOLARITE', $cheminFichier);
    }

    public function genererBulletinDeNotes(string $numeroEtudiant, string $idAnnee): string
    {
        $etudiant = $this->etudiantModel->trouverParIdentifiant($numeroEtudiant);
        if (!$etudiant) {
            throw new ElementNonTrouveException("Étudiant non trouvé.");
        }

        $notes = $this->evaluerModel->trouverParCritere(['numero_carte_etudiant' => $numeroEtudiant, 'id_annee_academique' => $idAnnee]);
        if (empty($notes)) {
            throw new ElementNonTrouveException("Aucune note trouvée pour cet étudiant pour l'année spécifiée.");
        }

        $variables = ['etudiant' => $etudiant, 'notes' => $notes, 'annee' => $idAnnee];
        $cheminFichier = $this->documentGenerator->genererPdfDepuisTemplate('BULLETIN_NOTES_TPL', $variables);

        return $this->enregistrerDocumentGenere($numeroEtudiant, 'BULLETIN_NOTES', $cheminFichier);
    }

    public function genererRecuDePaiement(string $idInscription): string
    {
        $inscription = $this->inscrireModel->trouverParIdentifiant($idInscription);
        if (!$inscription) {
            throw new ElementNonTrouveException("Inscription non trouvée.");
        }
        if ($inscription['id_statut_paiement'] !== 'PAIEMENT_VALIDE') {
            throw new OperationImpossibleException("Le paiement pour cette inscription n'est pas validé.");
        }

        $etudiant = $this->etudiantModel->trouverParIdentifiant($inscription['numero_carte_etudiant']);
        $variables = ['inscription' => $inscription, 'etudiant' => $etudiant];
        $cheminFichier = $this->documentGenerator->genererPdfDepuisTemplate('RECU_PAIEMENT_TPL', $variables);

        return $this->enregistrerDocumentGenere($inscription['numero_carte_etudiant'], 'RECU_PAIEMENT', $cheminFichier);
    }

    public function listerDocumentsPourEtudiant(string $numeroEtudiant): array
    {
        return $this->documentGenereModel->trouverParCritere(
            ['numero_utilisateur_concerne' => $numeroEtudiant],
            ['*'],
            'AND',
            'date_generation DESC'
        );
    }

    public function archiverDocument(string $idDocument): bool
    {
        $doc = $this->documentGenereModel->trouverParIdentifiant($idDocument);
        if (!$doc) {
            throw new ElementNonTrouveException("Document non trouvé.");
        }
        return $this->documentGenereModel->mettreAJourParIdentifiant($idDocument, ['est_archive' => true]);
    }

    private function enregistrerDocumentGenere(string $numeroUtilisateur, string $typeDocument, string $cheminFichier): string
    {
        $idDocument = $this->identifiantGenerator->generer('DOC');
        $donnees = [
            'id_document_genere' => $idDocument,
            'id_type_document' => $typeDocument,
            'numero_utilisateur_concerne' => $numeroUtilisateur,
            'date_generation' => (new \DateTime())->format('Y-m-d H:i:s'),
            'chemin_fichier' => $cheminFichier,
            'est_archive' => false
        ];
        $this->documentGenereModel->creer($donnees);
        $this->auditService->enregistrerAction('SYSTEM_ADMIN', 'ADMIN_DOC_GENERATED', $idDocument, 'DocumentGenere', $donnees);
        return $idDocument;
    }
}