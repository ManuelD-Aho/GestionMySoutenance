<?php

namespace App\Backend\Service\Permissions;

use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ValidationException;
use PDOException;

interface ServicePermissionsInterface
{
    public function creerGroupeUtilisateur(string $idGroupeUtilisateur, string $libelle, ?string $description = null): bool;
    public function modifierGroupeUtilisateur(string $idGroupeUtilisateur, string $libelle, ?string $description = null): bool;
    public function supprimerGroupeUtilisateur(string $idGroupeUtilisateur): bool;
    public function recupererGroupeUtilisateurParId(string $idGroupeUtilisateur): ?array;
    public function listerGroupesUtilisateur(): array;

    public function creerTypeUtilisateur(string $idTypeUtilisateur, string $libelle, ?string $description = null): bool;
    public function modifierTypeUtilisateur(string $idTypeUtilisateur, string $libelle, ?string $description = null): bool;
    public function supprimerTypeUtilisateur(string $idTypeUtilisateur): bool;
    public function recupererTypeUtilisateurParId(string $idTypeUtilisateur): ?array;
    public function listerTypesUtilisateur(): array;

    public function creerNiveauAcces(string $idNiveauAcces, string $libelle, ?string $description = null): bool;
    public function modifierNiveauAcces(string $idNiveauAcces, string $libelle, ?string $description = null): bool;
    public function supprimerNiveauAcces(string $idNiveauAcces): bool;
    public function recupererNiveauAccesParId(string $idNiveauAcces): ?array;
    public function listerNiveauxAcces(): array;

    public function creerTraitement(string $idTraitement, string $libelleTraitement): bool;
    public function modifierTraitement(string $idTraitement, string $libelleTraitement): bool;
    public function supprimerTraitement(string $idTraitement): bool;
    public function recupererTraitementParId(string $idTraitement): ?array;
    public function listerTraitements(): array;

    public function attribuerPermissionGroupe(string $idGroupeUtilisateur, string $idTraitement): bool;
    public function retirerPermissionGroupe(string $idGroupeUtilisateur, string $idTraitement): bool;
    public function recupererPermissionsPourGroupe(string $idGroupeUtilisateur): array;
    public function recupererGroupesPourPermission(string $idTraitement): array;

    public function utilisateurPossedePermission(string $numeroUtilisateur, string $idTraitement): bool;
    public function groupePossedePermission(string $idGroupeUtilisateur, string $idTraitement): bool;
    public function getPermissionsPourUtilisateur(string $numeroUtilisateur): array;
}