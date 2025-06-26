<?php

namespace App\Backend\Service\Authentication;

interface ServiceAuthenticationInterface
{
    public function tenterConnexion(string $identifiant, string $motDePasseClair): array;
    public function demarrerSessionUtilisateur(string $numeroUtilisateur): void;
    public function logout(): void;
    public function getUtilisateurConnecteComplet(): ?array;
    public function estUtilisateurConnecteEtSessionValide(?string $numeroUtilisateur = null): bool;
    public function creerCompteUtilisateurComplet(array $donneesUtilisateur, array $donneesProfil, string $typeProfilCode, bool $envoyerEmailValidation = true): string;
    public function listerUtilisateursAvecProfils(array $criteres = [], int $page = 1, int $elementsParPage = 20): array;
    public function mettreAJourProfilUtilisateur(string $numeroUtilisateur, string $typeProfilCode, array $donneesProfil): bool;
    public function mettreAJourCompteUtilisateurParAdmin(string $numeroUtilisateur, array $donneesCompte): bool;
    public function supprimerUtilisateur(string $numeroUtilisateur): bool;
    public function changerStatutDuCompte(string $numeroUtilisateur, string $nouveauStatut, ?string $raison = null): bool;
    public function modifierMotDePasse(string $numeroUtilisateur, string $nouveauMotDePasseClair, ?string $ancienMotDePasseClair = null, bool $isAdminReset = false): bool;
    public function demanderReinitialisationMotDePasse(string $emailPrincipal): void;
    public function reinitialiserMotDePasseApresValidationToken(string $tokenClair, string $nouveauMotDePasseClair): bool;
    public function genererEtStockerSecret2FA(string $numeroUtilisateur): array;
    public function activerAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTP): bool;
    public function desactiverAuthentificationDeuxFacteurs(string $numeroUtilisateur): bool;
    public function verifierCodeAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTP): bool;
}