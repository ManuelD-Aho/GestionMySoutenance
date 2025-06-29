<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Model\FaireStage;
use App\Backend\Service\Interface\StageServiceInterface;
use App\Backend\Service\Interface\AuditServiceInterface;
use App\Backend\Service\Interface\NotificationServiceInterface;
use App\Backend\Service\Interface\IdentifiantGeneratorInterface;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ElementNonTrouveException;

class ServiceStage implements StageServiceInterface
{
    private PDO $pdo;
    private FaireStage $stageModel;
    private AuditServiceInterface $auditService;
    private NotificationServiceInterface $notificationService;
    private IdentifiantGeneratorInterface $identifiantGenerator;

    public function __construct(
        PDO $pdo,
        FaireStage $stageModel,
        AuditServiceInterface $auditService,
        NotificationServiceInterface $notificationService,
        IdentifiantGeneratorInterface $identifiantGenerator
    ) {
        $this->pdo = $pdo;
        $this->stageModel = $stageModel;
        $this->auditService = $auditService;
        $this->notificationService = $notificationService;
        $this->identifiantGenerator = $identifiantGenerator;
    }

    public function enregistrerStage(string $numeroEtudiant, array $donneesStage): string
    {
        $idStage = $this->identifiantGenerator->generer('STG');
        $donnees = array_merge($donneesStage, [
            'id_stage' => $idStage,
            'numero_carte_etudiant' => $numeroEtudiant,
            'est_valide' => false
        ]);

        if ($this->stageModel->trouverUnParCritere(['numero_carte_etudiant' => $numeroEtudiant, 'id_annee_academique' => $donnees['id_annee_academique']])) {
            throw new DoublonException("Un stage est déjà enregistré pour cet étudiant pour cette année académique.");
        }

        $this->stageModel->creer($donnees);
        $this->auditService->enregistrerAction($numeroEtudiant, 'INTERNSHIP_REGISTERED', $idStage, 'FaireStage', $donnees);
        return $idStage;
    }

    public function mettreAJourStage(string $idStage, array $donnees): bool
    {
        $stage = $this->recupererStageOuEchouer($idStage);
        $resultat = $this->stageModel->mettreAJourParIdentifiant($idStage, $donnees);
        $this->auditService->enregistrerAction($stage['numero_carte_etudiant'], 'INTERNSHIP_UPDATED', $idStage, 'FaireStage', ['anciennes_valeurs' => $stage, 'nouvelles_valeurs' => $donnees]);
        return $resultat;
    }

    public function validerStage(string $idStage, string $idAgent): bool
    {
        $stage = $this->recupererStageOuEchouer($idStage);
        if ($stage['est_valide']) {
            return true;
        }

        $resultat = $this->stageModel->mettreAJourParIdentifiant($idStage, ['est_valide' => true]);
        $this->auditService->enregistrerAction($idAgent, 'INTERNSHIP_VALIDATED', $idStage, 'FaireStage');
        $this->notificationService->envoyerAUtilisateur($stage['numero_carte_etudiant'], 'INTERNSHIP_VALIDATED_TPL', ['stage_sujet' => $stage['sujet']]);
        return $resultat;
    }

    public function listerStages(array $filtres = []): array
    {
        return $this->stageModel->trouverParCritere($filtres);
    }

    public function getStageParEtudiant(string $numeroEtudiant, string $idAnnee): ?array
    {
        return $this->stageModel->trouverUnParCritere(['numero_carte_etudiant' => $numeroEtudiant, 'id_annee_academique' => $idAnnee]);
    }

    private function recupererStageOuEchouer(string $idStage): array
    {
        $stage = $this->stageModel->trouverParIdentifiant($idStage);
        if (!$stage) {
            throw new ElementNonTrouveException("Le stage avec l'ID '{$idStage}' n'a pas été trouvé.");
        }
        return $stage;
    }
}