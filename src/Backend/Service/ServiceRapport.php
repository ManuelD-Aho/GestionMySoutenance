<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\SectionRapport;
use App\Backend\Model\Rendre;
use App\Backend\Service\Interface\RapportServiceInterface;
use App\Backend\Service\Interface\AuditServiceInterface;
use App\Backend\Service\Interface\NotificationServiceInterface;
use App\Backend\Service\Interface\IdentifiantGeneratorInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceRapport implements RapportServiceInterface
{
    private PDO $pdo;
    private RapportEtudiant $rapportModel;
    private SectionRapport $sectionModel;
    private Rendre $rendreModel;
    private AuditServiceInterface $auditService;
    private NotificationServiceInterface $notificationService;
    private IdentifiantGeneratorInterface $identifiantGenerator;

    public function __construct(
        PDO $pdo,
        RapportEtudiant $rapportModel,
        SectionRapport $sectionModel,
        Rendre $rendreModel,
        AuditServiceInterface $auditService,
        NotificationServiceInterface $notificationService,
        IdentifiantGeneratorInterface $identifiantGenerator
    ) {
        $this->pdo = $pdo;
        $this->rapportModel = $rapportModel;
        $this->sectionModel = $sectionModel;
        $this->rendreModel = $rendreModel;
        $this->auditService = $auditService;
        $this->notificationService = $notificationService;
        $this->identifiantGenerator = $identifiantGenerator;
    }

    public function creerBrouillon(string $numeroEtudiant, array $metadonnees): string
    {
        $idRapport = $this->identifiantGenerator->generer('RAP');
        $donnees = array_merge($metadonnees, [
            'id_rapport_etudiant' => $idRapport,
            'numero_carte_etudiant' => $numeroEtudiant,
            'id_statut_rapport' => 'RAP_BROUILLON',
            'date_creation' => (new \DateTime())->format('Y-m-d H:i:s')
        ]);

        $this->rapportModel->creer($donnees);
        $this->auditService->enregistrerAction($numeroEtudiant, 'REPORT_DRAFT_CREATED', $idRapport, 'RapportEtudiant', $donnees);
        return $idRapport;
    }

    public function mettreAJourSection(string $idRapport, string $titreSection, string $contenu): bool
    {
        $this->recupererRapportOuEchouer($idRapport);
        $section = $this->sectionModel->trouverUnParCritere(['id_rapport_etudiant' => $idRapport, 'titre_section' => $titreSection]);

        if ($section) {
            return $this->sectionModel->mettreAJourParIdentifiant($section['id_section'], ['contenu_section' => $contenu]);
        }

        return (bool)$this->sectionModel->creer([
            'id_rapport_etudiant' => $idRapport,
            'titre_section' => $titreSection,
            'contenu_section' => $contenu
        ]);
    }

    public function soumettrePourVerification(string $idRapport): bool
    {
        $rapport = $this->recupererRapportOuEchouer($idRapport);
        if (!in_array($rapport['id_statut_rapport'], ['RAP_BROUILLON', 'RAP_CORRECTION_DEMANDEE'])) {
            throw new OperationImpossibleException("Le rapport ne peut être soumis que depuis l'état 'Brouillon' ou 'Correction demandée'.");
        }

        $resultat = $this->rapportModel->mettreAJourParIdentifiant($idRapport, ['id_statut_rapport' => 'RAP_SOUMIS', 'date_soumission' => (new \DateTime())->format('Y-m-d H:i:s')]);
        $this->auditService->enregistrerAction($rapport['numero_carte_etudiant'], 'REPORT_SUBMITTED', $idRapport, 'RapportEtudiant');
        $this->notificationService->envoyerAGroupe('GRP_CONFORMITE', 'NEW_REPORT_TO_VERIFY_TPL', ['report_id' => $idRapport]);
        return $resultat;
    }

    public function retournerPourCorrection(string $idRapport, string $motif): bool
    {
        $rapport = $this->recupererRapportOuEchouer($idRapport);
        $resultat = $this->rapportModel->mettreAJourParIdentifiant($idRapport, ['id_statut_rapport' => 'RAP_CORRECTION_DEMANDEE']);
        $this->auditService->enregistrerAction('ManuelD-Aho', 'REPORT_RETURNED_FOR_CORRECTION', $idRapport, 'RapportEtudiant', ['motif' => $motif]);
        $this->notificationService->envoyerAUtilisateur($rapport['numero_carte_etudiant'], 'REPORT_CORRECTION_NEEDED_TPL', ['report_title' => $rapport['titre'], 'motif' => $motif]);
        return $resultat;
    }

    public function resoumettreApresCorrection(string $idRapport, string $noteExplicative): bool
    {
        $this->auditService->enregistrerAction($_SESSION['user_id'], 'REPORT_RESUBMITTED', $idRapport, 'RapportEtudiant', ['note' => $noteExplicative]);
        return $this->soumettrePourVerification($idRapport);
    }

    public function getHistoriqueStatuts(string $idRapport): array
    {
        return $this->auditService->getHistoriquePourEntite($idRapport, 'RapportEtudiant');
    }

    public function recupererRapportComplet(string $idRapport): ?array
    {
        $rapport = $this->rapportModel->trouverParIdentifiant($idRapport);
        if (!$rapport) return null;
        $rapport['sections'] = $this->sectionModel->trouverParCritere(['id_rapport_etudiant' => $idRapport]);
        return $rapport;
    }

    public function archiverRapport(string $idRapport): bool
    {
        $resultat = $this->rapportModel->mettreAJourParIdentifiant($idRapport, ['id_statut_rapport' => 'RAP_ARCHIVE']);
        $this->auditService->enregistrerAction('ManuelD-Aho', 'REPORT_ARCHIVED', $idRapport, 'RapportEtudiant');
        return $resultat;
    }

    public function listerRapports(array $filtres = []): array
    {
        return $this->rapportModel->trouverParCritere($filtres);
    }

    public function assignerDirecteurMemoire(string $idRapport, string $idEnseignant): bool
    {
        $this->recupererRapportOuEchouer($idRapport);
        $resultat = $this->rendreModel->creer(['id_rapport_etudiant' => $idRapport, 'numero_enseignant' => $idEnseignant]);
        $this->auditService->enregistrerAction('ManuelD-Aho', 'REPORT_SUPERVISOR_ASSIGNED', $idRapport, 'RapportEtudiant', ['enseignant' => $idEnseignant]);
        return (bool)$resultat;
    }

    private function recupererRapportOuEchouer(string $idRapport): array
    {
        $rapport = $this->rapportModel->trouverParIdentifiant($idRapport);
        if (!$rapport) {
            throw new ElementNonTrouveException("Le rapport avec l'ID '{$idRapport}' n'a pas été trouvé.");
        }
        return $rapport;
    }
}