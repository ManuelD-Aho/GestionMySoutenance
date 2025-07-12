<?php
// src/Backend/Service/Utilisateur/ServiceUtilisateurInterface.php

namespace App\Backend\Service\Utilisateur;

interface ServiceUtilisateurInterface
{
    // --- CRUD Entités & Comptes ---
    public function creerEntite(string $typeEntite, array $donneesProfil): string;
    public function activerComptePourEntite(string $numeroEntite, array $donneesCompte, bool $envoyerEmailValidation = true): bool;
    public function createAdminUser(string $login, string $email, string $password): string; // <-- Nouvelle méthode ajoutée
    public function creerUtilisateurComplet(array $userData, array $profileData, string $type): string;
    public function listerUtilisateursComplets(array $filtres = []): array;
    public function lireUtilisateurComplet(string $id): ?array;
    public function mettreAJourUtilisateur(string $numeroUtilisateur, array $donneesProfil, array $donneesCompte): bool;
    public function supprimerUtilisateurEtEntite(string $id): bool;

    // --- Gestion des Comptes ---
    public function changerStatutCompte(string $numeroUtilisateur, string $nouveauStatut): bool;
    public function reinitialiserMotDePasseAdmin(string $id): bool;
    public function renvoyerEmailValidation(string $numeroUtilisateur): bool;
    public function telechargerPhotoProfil(string $numeroUtilisateur, array $fileData): string;

    // --- Gestion des Délégations ---
    public function creerDelegation(string $idDelegant, string $idDelegue, string $idTraitement, string $dateDebut, string $dateFin, ?string $contexteId = null, ?string $contexteType = null): string;
    public function revoquerDelegation(string $idDelegation): bool;
    public function listerDelegations(array $filtres = []): array;
    public function lireDelegation(string $idDelegation): ?array;

    // --- Processus Métier ---
    public function gererTransitionsRoles(string $departingUserId, string $newUserId): array;
    public function importerEtudiantsDepuisFichier(string $filePath, array $mapping): array;
    public function listerEntitesSansCompte(string $typeEntite): array; // Nouvelle méthode
}