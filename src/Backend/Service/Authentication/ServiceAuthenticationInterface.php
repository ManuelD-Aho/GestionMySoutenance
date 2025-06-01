<?php

namespace App\Backend\Service\Authentication;

use App\Backend\Exception\AuthenticationException;
use App\Backend\Exception\CompteBloqueException;
use App\Backend\Exception\IdentifiantsInvalidesException;
use App\Backend\Exception\TokenInvalideException;
use App\Backend\Exception\TokenExpireException;
use App\Backend\Exception\UtilisateurNonTrouveException;
use App\Backend\Exception\EmailNonValideException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\MotDePasseInvalideException;
use App\Backend\Exception\CompteNonValideException;
use App\Backend\Exception\ValidationException;
use PDOException;

interface ServiceAuthenticationInterface
{
    public function tenterConnexion(string $identifiant, string $motDePasse): object;
    public function traiterTentativeConnexionEchoueePourUtilisateur(string $numeroUtilisateur): void;
    public function reinitialiserTentativesConnexion(string $numeroUtilisateur): void;
    public function estCompteActuellementBloque(string $numeroUtilisateur): bool;
    public function genererEtStockerSecret2FA(string $numeroUtilisateur): string;
    public function activerAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTPVerifie): bool;
    public function verifierCodeAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTP): bool;
    public function desactiverAuthentificationDeuxFacteurs(string $numeroUtilisateur): bool;
    public function demarrerSessionUtilisateur(object $utilisateurAvecProfil): void;
    public function estUtilisateurConnecteEtSessionValide(): bool;
    public function getUtilisateurConnecteComplet(): ?object;
    public function terminerSessionUtilisateur(): void;
    public function creerCompteUtilisateurComplet(array $donneesUtilisateur, array $donneesProfil, string $idTypeUtilisateurProfil, bool $envoyerEmailValidation = true): string;
    public function genererNumeroUtilisateurUniqueNonSequentiel(): string;
    public function envoyerEmailValidationCompte(string $numeroUtilisateur, string $emailPrincipal, string $tokenValidation): void;
    public function validerCompteEmailViaToken(string $tokenValidation): bool;
    public function recupererUtilisateurCompletParNumero(string $numeroUtilisateur): ?object;
    public function recupererUtilisateurCompletParEmailPrincipal(string $emailPrincipal): ?object;
    public function recupererUtilisateurCompletParLogin(string $login): ?object;
    public function listerUtilisateursAvecProfils(array $criteres = [], int $page = 1, int $elementsParPage = 25): array;
    public function modifierMotDePasse(string $numeroUtilisateur, string $nouveauMotDePasseClair, ?string $ancienMotDePasseClair = null, bool $parAdmin = false): bool;
    public function mettreAJourProfilUtilisateur(string $numeroUtilisateur, string $idTypeUtilisateurProfil, array $donneesProfil): bool;
    public function mettreAJourCompteUtilisateurParAdmin(string $numeroUtilisateur, array $donneesCompte): bool;
    public function changerStatutDuCompte(string $numeroUtilisateur, string $nouveauStatut, ?string $raison = null): bool;
    public function verifierRobustesseMotDePasse(string $motDePasse): array;
    public function demanderReinitialisationMotDePasse(string $emailPrincipal): bool;
    public function validerTokenReinitialisationMotDePasse(string $token): string;
    public function reinitialiserMotDePasseApresValidationToken(string $token, string $nouveauMotDePasseClair): bool;
    public function recupererEmailSourceDuProfil(string $numeroUtilisateur): ?string;
    public function estNouveauMotDePasseDansHistorique(string $numeroUtilisateur, string $nouveauMotDePasseClair, int $limiteHistorique = 3): bool;
    public function journaliserActionAuthentification(?string $numeroUtilisateurActeur, string $numeroUtilisateurConcerne, string $idActionSysteme, string $resultat, ?array $details = null): void;
}