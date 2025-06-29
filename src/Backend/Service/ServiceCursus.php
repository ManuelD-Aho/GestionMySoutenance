<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Model\Ue;
use App\Backend\Model\Ecue;
use App\Backend\Service\Interface\CursusServiceInterface;
use App\Backend\Service\Interface\AuditServiceInterface;
use App\Backend\Service\Interface\IdentifiantGeneratorInterface;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ElementNonTrouveException;

class ServiceCursus implements CursusServiceInterface
{
    private PDO $pdo;
    private Ue $ueModel;
    private Ecue $ecueModel;
    private AuditServiceInterface $auditService;
    private IdentifiantGeneratorInterface $identifiantGenerator;

    public function __construct(
        PDO $pdo,
        Ue $ueModel,
        Ecue $ecueModel,
        AuditServiceInterface $auditService,
        IdentifiantGeneratorInterface $identifiantGenerator
    ) {
        $this->pdo = $pdo;
        $this->ueModel = $ueModel;
        $this->ecueModel = $ecueModel;
        $this->auditService = $auditService;
        $this->identifiantGenerator = $identifiantGenerator;
    }

    public function creerUE(array $donnees): string
    {
        if ($this->ueModel->trouverUnParCritere(['code_ue' => $donnees['code_ue']])) {
            throw new DoublonException("Une UE avec le code '{$donnees['code_ue']}' existe déjà.");
        }

        $idUe = $this->identifiantGenerator->generer('UE');
        $donnees['id_ue'] = $idUe;

        $this->ueModel->creer($donnees);
        $this->auditService->enregistrerAction('SYSTEM_ADMIN', 'CURSUS_UE_CREATE', $idUe, 'Ue', $donnees);

        return $idUe;
    }

    public function mettreAJourUE(string $idUe, array $donnees): bool
    {
        $ue = $this->recupererUeOuEchouer($idUe);
        $resultat = $this->ueModel->mettreAJourParIdentifiant($idUe, $donnees);
        $this->auditService->enregistrerAction('SYSTEM_ADMIN', 'CURSUS_UE_UPDATE', $idUe, 'Ue', ['anciennes_valeurs' => $ue, 'nouvelles_valeurs' => $donnees]);
        return $resultat;
    }

    public function creerECUE(array $donnees): string
    {
        if ($this->ecueModel->trouverUnParCritere(['code_ecue' => $donnees['code_ecue']])) {
            throw new DoublonException("Un ECUE avec le code '{$donnees['code_ecue']}' existe déjà.");
        }

        $idEcue = $this->identifiantGenerator->generer('ECUE');
        $donnees['id_ecue'] = $idEcue;

        $this->ecueModel->creer($donnees);
        $this->auditService->enregistrerAction('SYSTEM_ADMIN', 'CURSUS_ECUE_CREATE', $idEcue, 'Ecue', $donnees);

        return $idEcue;
    }

    public function mettreAJourECUE(string $idEcue, array $donnees): bool
    {
        $ecue = $this->recupererEcueOuEchouer($idEcue);
        $resultat = $this->ecueModel->mettreAJourParIdentifiant($idEcue, $donnees);
        $this->auditService->enregistrerAction('SYSTEM_ADMIN', 'CURSUS_ECUE_UPDATE', $idEcue, 'Ecue', ['anciennes_valeurs' => $ecue, 'nouvelles_valeurs' => $donnees]);
        return $resultat;
    }

    public function lierEcueAUe(string $idEcue, string $idUe): bool
    {
        $this->recupererUeOuEchouer($idUe);
        $ecue = $this->recupererEcueOuEchouer($idEcue);

        $resultat = $this->ecueModel->mettreAJourParIdentifiant($idEcue, ['id_ue' => $idUe]);
        $this->auditService->enregistrerAction('SYSTEM_ADMIN', 'CURSUS_LINK_ECUE_UE', $idEcue, 'Ecue', ['id_ue_liee' => $idUe]);
        return $resultat;
    }

    public function listerCursusComplet(string $idNiveauEtude): array
    {
        $ues = $this->ueModel->trouverParCritere(['id_niveau_etude' => $idNiveauEtude], ['*'], 'AND', 'libelle_ue ASC');
        $ecues = $this->ecueModel->trouverParCritere(['id_ue' => ['operator' => 'IN', 'values' => array_column($ues, 'id_ue')]]);

        $ecuesParUe = [];
        foreach ($ecues as $ecue) {
            $ecuesParUe[$ecue['id_ue']][] = $ecue;
        }

        $cursus = [];
        foreach ($ues as $ue) {
            $ue['ecues'] = $ecuesParUe[$ue['id_ue']] ?? [];
            $cursus[] = $ue;
        }

        return $cursus;
    }

    private function recupererUeOuEchouer(string $idUe): array
    {
        $ue = $this->ueModel->trouverParIdentifiant($idUe);
        if (!$ue) {
            throw new ElementNonTrouveException("L'UE avec l'ID '{$idUe}' n'a pas été trouvée.");
        }
        return $ue;
    }

    private function recupererEcueOuEchouer(string $idEcue): array
    {
        $ecue = $this->ecueModel->trouverParIdentifiant($idEcue);
        if (!$ecue) {
            throw new ElementNonTrouveException("L'ECUE avec l'ID '{$idEcue}' n'a pas été trouvé.");
        }
        return $ecue;
    }
}