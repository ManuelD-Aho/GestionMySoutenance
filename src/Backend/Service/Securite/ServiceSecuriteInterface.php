<?php
// src/Backend/Service/Securite/ServiceSecuriteInterface.php

namespace App\Backend\Service\Securite;

use App\Backend\Service\Communication\ServiceCommunicationInterface;

interface ServiceSecuriteInterface
{
    //================================================================
    // AUTHENTIFICATION & GESTION DE SESSION
    //================================================================

    /**
     * Tente de connecter un utilisateur avec son identifiant et son mot de passe.
     * Gère les tentatives échouées et le blocage de compte.
     *
     * @param string $identifiant Le login ou l'email de l'utilisateur.
     * @param string $motDePasseClair Le mot de passe en clair.
     * @return array Un tableau indiquant le statut ('success', '2fa_required').
     */
    public function tenterConnexion(string $identifiant, string $motDePasseClair): array;

    /**
     * Démarre une session complète pour un utilisateur après une authentification réussie.
     * Charge les permissions et les données utilisateur en session.
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur.
     */
    public function demarrerSessionUtilisateur(string $numeroUtilisateur): void;

    /**
     * Déconnecte l'utilisateur actuel en détruisant sa session.
     */
    public function logout(): void;

    /**
     * Vérifie si un utilisateur est actuellement connecté.
     *
     * @return bool True si l'utilisateur est connecté.
     */
    public function estUtilisateurConnecte(): bool;

    /**
     * Récupère les données de l'utilisateur actuellement connecté stockées en session.
     *
     * @return array|null Les données de l'utilisateur ou null.
     */
    public function getUtilisateurConnecte(): ?array;

    //================================================================
    // GESTION DES MOTS DE PASSE
    //================================================================

    /**
     * Déclenche le processus de réinitialisation de mot de passe pour un email donné.
     *
     * @param string $emailPrincipal L'email du compte.
     * @param ServiceCommunicationInterface $communicationService Le service pour envoyer l'email.
     */
    public function demanderReinitialisationMotDePasse(string $emailPrincipal, ServiceCommunicationInterface $communicationService): void;

    /**
     * Réinitialise le mot de passe d'un utilisateur en utilisant un token de validation.
     *
     * @param string $tokenClair Le token reçu par l'utilisateur.
     * @param string $nouveauMotDePasseClair Le nouveau mot de passe.
     * @return bool True si la modification a réussi.
     */
    public function reinitialiserMotDePasseViaToken(string $tokenClair, string $nouveauMotDePasseClair): bool;

    /**
     * Permet à un utilisateur connecté de modifier son propre mot de passe.
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur.
     * @param string $nouveauMotDePasseClair Le nouveau mot de passe.
     * @param string $ancienMotDePasseClair L'ancien mot de passe pour vérification.
     * @return bool True si la modification a réussi.
     */
    public function modifierMotDePasse(string $numeroUtilisateur, string $nouveauMotDePasseClair, string $ancienMotDePasseClair): bool;

    //================================================================
    // AUTHENTIFICATION À DEUX FACTEURS (2FA)
    //================================================================

    /**
     * Génère un nouveau secret 2FA et le QR code associé pour un utilisateur.
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur.
     * @return array Contenant le secret ('secret') et l'URL du QR code ('qr_code_url').
     */
    public function genererEtStockerSecret2FA(string $numeroUtilisateur): array;

    /**
     * Active l'authentification à deux facteurs après vérification d'un code TOTP.
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur.
     * @param string $codeTOTP Le code généré par l'application d'authentification.
     * @return bool True si l'activation a réussi.
     */
    public function activerAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTP): bool;

    /**
     * Désactive l'authentification à deux facteurs après vérification du mot de passe.
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur.
     * @param string $motDePasseClair Le mot de passe actuel pour confirmation.
     * @return bool True si la désactivation a réussi.
     */
    public function desactiverAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $motDePasseClair): bool;

    /**
     * Vérifie la validité d'un code TOTP pour un utilisateur.
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur.
     * @param string $codeTOTP Le code à vérifier.
     * @param string|null $secret Le secret 2FA (optionnel, sera récupéré si non fourni).
     * @return bool True si le code est valide.
     */
    public function verifierCodeAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTP, ?string $secret = null): bool;

    //================================================================
    // AUTORISATION & PERMISSIONS
    //================================================================

    /**
     * Vérifie si l'utilisateur connecté possède une permission, potentiellement dans un contexte spécifique.
     *
     * @param string $permissionCode Le code de la permission à vérifier (ex: 'TRAIT_ETUDIANT_RAPPORT_SOUMETTRE').
     * @param string|null $contexteId L'ID de l'entité sur laquelle la permission est vérifiée (ex: un ID de rapport).
     * @param string|null $contexteType Le type de l'entité (ex: 'RapportEtudiant').
     * @return bool True si l'utilisateur a la permission.
     */
    public function utilisateurPossedePermission(string $permissionCode, ?string $contexteId = null, ?string $contexteType = null): bool;

    /**
     * Met à jour les permissions dans toutes les sessions actives d'un utilisateur.
     * Utile lorsqu'un rôle ou une délégation est modifié.
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur dont les sessions doivent être mises à jour.
     */
    public function synchroniserPermissionsSessionsUtilisateur(string $numeroUtilisateur): void;
}