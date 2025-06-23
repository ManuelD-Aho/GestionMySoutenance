<?php
namespace App\Backend\Service\Permissions;

interface ServicePermissionsInterface
{
    public function creerGroupeUtilisateur(string $idGroupeUtilisateur, string $libelleGroupeUtilisateur): bool;
    public function modifierGroupeUtilisateur(string $idGroupeUtilisateur, array $donnees): bool;
    public function supprimerGroupeUtilisateur(string $idGroupeUtilisateur): bool;
    public function listerGroupesUtilisateur(): array;
    public function recupererGroupeUtilisateurParId(string $idGroupeUtilisateur): ?array;

    public function creerTypeUtilisateur(string $idTypeUtilisateur, string $libelleTypeUtilisateur): bool;
    public function modifierTypeUtilisateur(string $idTypeUtilisateur, array $donnees): bool;
    public function supprimerTypeUtilisateur(string $idTypeUtilisateur): bool;
    public function listerTypesUtilisateur(): array;

    public function creerNiveauAcces(string $idNiveauAcces, string $libelleNiveauAcces): bool;
    public function modifierNiveauAcces(string $idNiveauAcces, array $donnees): bool;
    public function supprimerNiveauAcces(string $idNiveauAcces): bool;
    public function listerNiveauxAcces(): array;

    public function creerTraitement(string $idTraitement, string $libelleTraitement): bool;
    public function modifierTraitement(string $idTraitement, array $donnees): bool;
    public function supprimerTraitement(string $idTraitement): bool;
    public function listerTraitements(): array;

    public function attribuerPermissionGroupe(string $idGroupeUtilisateur, string $idTraitement): bool;
    public function retirerPermissionGroupe(string $idGroupeUtilisateur, string $idTraitement): bool;
    public function recupererPermissionsPourGroupe(string $idGroupeUtilisateur): array;

    public function utilisateurPossedePermission(string $permissionCode): bool;
}