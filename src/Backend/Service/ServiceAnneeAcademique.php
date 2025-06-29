<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Model\AnneeAcademique;
use App\Backend\Model\Inscrire;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\ParametreSysteme;
use App\Backend\Service\Interface\AnneeAcademiqueServiceInterface;
use App\Backend\Service\Interface\AuditServiceInterface;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceAnneeAcademique implements AnneeAcademiqueServiceInterface
{
    private PDO $pdo;
    private AnneeAcademique $anneeAcademiqueModel;
    private Inscrire $inscrireModel;
    private RapportEtudiant $rapportEtudiantModel;
    private AuditServiceInterface $auditService;

    public function __construct(
        PDO $pdo,
        AnneeAcademique $anneeAcademiqueModel,
        Inscrire $inscrireModel,
        RapportEtudiant $rapportEtudiantModel,
        AuditServiceInterface $auditService
    ) {
        $this->pdo = $pdo;
        $this->anneeAcademiqueModel = $anneeAcademiqueModel;
        $this->inscrireModel = $inscrireModel;
        $this->rapportEtudiantModel = $rapportEtudiantModel;
        $this->auditService = $auditService;
    }

    public function creerAnneeAcademique(array $donnees): string
    {
        $this->pdo->beginTransaction();
        try {
            $idAnnee = "ANNEE-" . $donnees['libelle'];
            $donnees['id_annee_academique'] = $idAnnee;

            if ($this->anneeAcademiqueModel->trouverUnParCritere(['libelle' => $donnees['libelle']])) {
                throw new DoublonException("Une année académique avec le libellé '{$donnees['libelle']}' existe déjà.");
            }

            $this->anneeAcademiqueModel->creer($donnees);

            if (!empty($donnees['est_active'])) {
                $this->definirAnneeAcademiqueActive($idAnnee);
            }

            $this->auditService->enregistrerAction('SYSTEM', 'ACADEMIC_YEAR_CREATE', $idAnnee, 'AnneeAcademique', $donnees);
            $this->pdo->commit();

            return $idAnnee;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function mettreAJourAnneeAcademique(string $idAnnee, array $donnees): bool
    {
        $anneeExistante = $this->recupererOuEchouer($idAnnee);

        if (isset($donnees['libelle']) && $donnees['libelle'] !== $anneeExistante['libelle']) {
            if ($this->anneeAcademiqueModel->trouverUnParCritere(['libelle' => $donnees['libelle']])) {
                throw new DoublonException("Une année académique avec le libellé '{$donnees['libelle']}' existe déjà.");
            }
        }

        $this->pdo->beginTransaction();
        try {
            $resultat = $this->anneeAcademiqueModel->mettreAJourParIdentifiant($idAnnee, $donnees);
            $this->auditService->enregistrerAction('SYSTEM', 'ACADEMIC_YEAR_UPDATE', $idAnnee, 'AnneeAcademique', ['anciennes_valeurs' => $anneeExistante, 'nouvelles_valeurs' => $donnees]);
            $this->pdo->commit();

            return $resultat;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function supprimerAnneeAcademique(string $idAnnee): bool
    {
        $this->recupererOuEchouer($idAnnee);

        $inscriptions = $this->inscrireModel->compterParCritere(['id_annee_academique' => $idAnnee]);
        if ($inscriptions > 0) {
            throw new OperationImpossibleException("Impossible de supprimer l'année académique car {$inscriptions} inscription(s) y sont associées.");
        }

        $rapports = $this->rapportEtudiantModel->compterParCritere(['id_annee_academique' => $idAnnee]);
        if ($rapports > 0) {
            throw new OperationImpossibleException("Impossible de supprimer l'année académique car {$rapports} rapport(s) y sont associés.");
        }

        $this->pdo->beginTransaction();
        try {
            $resultat = $this->anneeAcademiqueModel->supprimerParIdentifiant($idAnnee);
            $this->auditService->enregistrerAction('SYSTEM', 'ACADEMIC_YEAR_DELETE', $idAnnee, 'AnneeAcademique');
            $this->pdo->commit();

            return $resultat;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function recupererAnneeAcademiqueParId(string $idAnnee): ?array
    {
        return $this->anneeAcademiqueModel->trouverParIdentifiant($idAnnee);
    }

    public function listerAnneesAcademiques(array $filtres = []): array
    {
        return $this->anneeAcademiqueModel->trouverParCritere($filtres, ['*'], 'AND', 'date_debut DESC');
    }

    public function definirAnneeAcademiqueActive(string $idAnnee): bool
    {
        $this->recupererOuEchouer($idAnnee);

        $this->pdo->beginTransaction();
        try {
            $this->anneeAcademiqueModel->mettreAJourParCles([], ['est_active' => false]);
            $resultat = $this->anneeAcademiqueModel->mettreAJourParIdentifiant($idAnnee, ['est_active' => true]);

            $this->auditService->enregistrerAction('SYSTEM', 'ACADEMIC_YEAR_SET_ACTIVE', $idAnnee, 'AnneeAcademique');
            $this->pdo->commit();

            return $resultat;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function getAnneeAcademiqueActive(): ?array
    {
        return $this->anneeAcademiqueModel->trouverUnParCritere(['est_active' => true]);
    }

    public function archiverAnneeAcademique(string $idAnnee): bool
    {
        $annee = $this->recupererOuEchouer($idAnnee);

        if ($annee['est_active']) {
            throw new OperationImpossibleException("Impossible d'archiver une année académique active.");
        }

        $this->pdo->beginTransaction();
        try {
            $resultat = $this->anneeAcademiqueModel->mettreAJourParIdentifiant($idAnnee, ['est_archivee' => true]);
            $this->auditService->enregistrerAction('SYSTEM', 'ACADEMIC_YEAR_ARCHIVE', $idAnnee, 'AnneeAcademique');
            $this->pdo->commit();

            return $resultat;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function recupererOuEchouer(string $idAnnee): array
    {
        $annee = $this->recupererAnneeAcademiqueParId($idAnnee);
        if (!$annee) {
            throw new ElementNonTrouveException("L'année académique avec l'ID '{$idAnnee}' n'a pas été trouvée.");
        }
        return $annee;
    }
}