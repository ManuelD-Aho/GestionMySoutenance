<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

use App\Backend\Exception\AuthenticationException;
use App\Backend\Exception\CompteBloqueException;
use App\Backend\Exception\CompteNonValideException;
use App\Backend\Exception\IdentifiantsInvalidesException;
use App\Backend\Exception\TokenExpireException;
use App\Backend\Exception\TokenInvalideException;

interface AuthenticationServiceInterface
{
    /**
     * Tente de connecter un utilisateur avec son identifiant et mot de passe.
     *
     * @param string $identifiant Login ou email de l'utilisateur.
     * @param string $motDePasse Mot de passe en clair.
     * @return array Les données de l'utilisateur si la connexion réussit.
     * @throws IdentifiantsInvalidesException Si les identifiants sont incorrects.
     * @throws CompteBloqueException Si le compte est temporairement bloqué.
     * @throws CompteNonValideException Si le compte n'est pas actif ou validé.
     */
    public function tenterConnexion(string $identifiant, string $motDePasse): array;

    /**
     * Démarre et configure la session PHP pour un utilisateur authentifié.
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur.
     * @return bool True si la session est démarrée avec succès.
     */
    public function demarrerSessionUtilisateur(string $numeroUtilisateur): bool;

    /**
     * Déconnecte l'utilisateur et détruit sa session.
     */
    public function logout(): void;

    /**
     * Récupère les données de l'utilisateur actuellement connecté.
     *
     * @return array|null Les données de l'utilisateur ou null si personne n'est connecté.
     */
    public function getUtilisateurConnecte(): ?array;

    /**
     * Vérifie si un utilisateur est actuellement connecté.
     *
     * @return bool True si un utilisateur est connecté, sinon false.
     */
    public function estConnecte(): bool;

    /**
     * Vérifie un code d'authentification à deux facteurs.
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur.
     * @param string $code Le code 2FA à vérifier.
     * @return bool True si le code est valide.
     * @throws AuthenticationException Si le code est invalide.
     */
    public function verifierCode2FA(string $numeroUtilisateur, string $code): bool;

    /**
     * Déclenche le processus de demande de réinitialisation de mot de passe.
     *
     * @param string $email L'email de l'utilisateur.
     * @return bool True si l'email a été envoyé.
     */
    public function demanderReinitialisationMotDePasse(string $email): bool;

    /**
     * Valide un token de réinitialisation de mot de passe.
     *
     * @param string $token Le token reçu par email.
     * @return string|null L'ID de l'utilisateur si le token est valide, sinon null.
     * @throws TokenExpireException Si le token a expiré.
     * @throws TokenInvalideException Si le token est invalide.
     */
    public function validerTokenReinitialisation(string $token): ?string;

    /**
     * Réinitialise le mot de passe d'un utilisateur à l'aide d'un token valide.
     *
     * @param string $token Le token de réinitialisation.
     * @param string $nouveauMotDePasse Le nouveau mot de passe.
     * @return bool True si le mot de passe a été réinitialisé.
     * @throws TokenInvalideException Si le token est invalide.
     */
    public function reinitialiserMotDePasseAvecToken(string $token, string $nouveauMotDePasse): bool;
}