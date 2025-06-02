<?php

namespace App\Backend\Service\Permissions;

// Conservez vos use statements pour les exceptions si l'interface les mentionne dans les PHPDocs
// use App\Backend\Exception\ElementNonTrouveException;
// use App\Backend\Exception\OperationImpossibleException;
// use App\Backend\Exception\DoublonException;
// use App\Backend\Exception\ValidationException;
// use PDOException;

interface ServicePermissionsInterface
{
    // Méthodes pour GroupeUtilisateur ajustées
    public function creerGroupeUtilisateur(string $idGroupeUtilisateur, string $libelle): string;
    public function modifierGroupeUtilisateur(string $idGroupeUtilisateur, string $libelle): bool;
    public function supprimerGroupeUtilisateur(string $idGroupeUtilisateur): bool;
    public function recupererGroupeUtilisateurParId(string $idGroupeUtilisateur): ?array;
    public function listerGroupesUtilisateur(): array;
    // Supprimez recupererGroupeUtilisateurParCode si la colonne code_groupe_utilisateur n'existe plus
    // public function recupererGroupeUtilisateurParCode(string $codeGroupe): ?array;


    // Conservez les signatures originales pour les autres méthodes pour l'instant,
    // MAIS VÉRIFIEZ-LES ET ADAPTEZ-LES SI LEURS TABLES RESPECTIVES ONT AUSSI CHANGÉ.
    // Les types d'ID (int vs string) et les colonnes optionnelles (description, code) sont des points clés.

    public function creerTypeUtilisateur(string $libelle, ?string $description, ?string $codeType = null): int; // Signature de votre dernier code fourni
    public function modifierTypeUtilisateur(int $idType, string $libelle, ?string $description, ?string $codeType = null): bool; // Signature de votre dernier code fourni
    public function supprimerTypeUtilisateur(int $idType): bool; // Signature de votre dernier code fourni
    public function recupererTypeUtilisateurParId(int $idType): ?array; // Signature de votre dernier code fourni
    // Si code_type_utilisateur n'existe plus, cette méthode doit être revue/supprimée :
    public function recupererTypeUtilisateurParCode(string $codeType): ?array;
    public function listerTypesUtilisateur(): array;

    public function creerNiveauAcces(string $libelle, ?string $description, ?string $codeNiveauAcces = null): int; // Signature de votre dernier code fourni
    public function modifierNiveauAcces(int $idNiveau, string $libelle, ?string $description, ?string $codeNiveauAcces = null): bool; // Signature de votre dernier code fourni
    public function supprimerNiveauAcces(int $idNiveau): bool; // Signature de votre dernier code fourni
    public function recupererNiveauAccesParId(int $idNiveau): ?array; // Signature de votre dernier code fourni
    // Si code_niveau_acces n'existe plus, cette méthode doit être revue/supprimée :
    public function recupererNiveauAccesParCode(string $codeNiveauAcces): ?array;
    public function listerNiveauxAcces(): array;

    public function creerTraitement(string $libelleTraitement, string $codeTraitement): int; // Signature de votre dernier code fourni
    public function modifierTraitement(int $idTraitement, string $libelleTraitement, string $codeTraitement): bool; // Signature de votre dernier code fourni
    public function supprimerTraitement(int $idTraitement): bool; // Signature de votre dernier code fourni
    public function recupererTraitementParId(int $idTraitement): ?array; // Signature de votre dernier code fourni
    public function recupererTraitementParCode(string $codeTraitement): ?array; // Signature de votre dernier code fourni
    public function listerTraitements(): array;

    // Pour ces méthodes, id_groupe_utilisateur est maintenant un string
    public function attribuerPermissionGroupe(string $idGroupeUtilisateur, int $idTraitement): bool;
    public function retirerPermissionGroupe(string $idGroupeUtilisateur, int $idTraitement): bool;
    public function recupererPermissionsPourGroupe(string $idGroupeUtilisateur): array;

    public function recupererGroupesPourPermission(int $idTraitement): array;

    public function utilisateurPossedePermission(string $numeroUtilisateur, string $codePermission): bool; // Signature de votre dernier code fourni
    // Pour groupePossedePermission, idGroupeUtilisateur est maintenant un string
    public function groupePossedePermission(string $idGroupeUtilisateur, string $codePermission): bool;
    public function getPermissionsPourUtilisateur(string $numeroUtilisateur): array;
}