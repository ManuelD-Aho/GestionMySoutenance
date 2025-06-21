<?php
namespace App\Backend\Service\Authentication;

interface ServiceAuthenticationInterface
{
    /**
     * Tente de connecter un utilisateur.
     * @param string $identifiant Le login ou l'email de l'utilisateur.
     * @param string $motDePasseClair Le mot de passe en clair.
     * @return array Tableau contenant le statut ('success' ou '2fa_required') et éventuellement les données de l'utilisateur.
     * @throws \App\Backend\Exception\IdentifiantsInvalidesException Si les identifiants sont incorrects.
     * @throws \App\Backend\Exception\CompteBloqueException Si le compte est bloqué.
     * @throws \App\Backend\Exception\CompteNonValideException Si l'email n'est pas validé.
     */
    public function tenterConnexion(string $identifiant, string $motDePasseClair): array;

    /**
     * Démarre la session de l'utilisateur après une connexion réussie ou validation 2FA.
     * @param string $numeroUtilisateur Le numéro unique de l'utilisateur.
     */
    public function demarrerSessionUtilisateur(string $numeroUtilisateur): void;

    /**
     * Déconnecte l'utilisateur et détruit la session.
     */
    public function logout(): void;

    /**
     * Récupère les données complètes de l'utilisateur connecté depuis la session.
     * @return array|null Les données complètes de l'utilisateur ou null si non connecté.
     */
    public function getUtilisateurConnecteComplet(): ?array;

    /**
     * Vérifie si un utilisateur est connecté et si sa session est valide.
     * @param string|null $numeroUtilisateur Optionnel: vérifie un utilisateur spécifique.
     * @return bool Vrai si l'utilisateur est connecté et la session valide.
     */
    public function estUtilisateurConnecteEtSessionValide(?string $numeroUtilisateur = null): bool;

    /**
     * Crée un compte utilisateur complet avec son profil spécifique.
     * @param array $donneesUtilisateur Données de base de l'utilisateur (login, email_principal, mot_de_passe).
     * @param array $donneesProfil Données spécifiques au profil.
     * @param string $typeProfilCode Code du type de profil (ex: 'TYPE_ETUD').
     * @param bool $envoyerEmailValidation Indique si un email de validation doit être envoyé.
     * @return string Le numéro d'utilisateur créé.
     * @throws \App\Backend\Exception\DoublonException Si le login ou l'email existe déjà.
     * @throws \Exception Pour toute autre erreur inattendue.
     */
    public function creerCompteUtilisateurComplet(array $donneesUtilisateur, array $donneesProfil, string $typeProfilCode, bool $envoyerEmailValidation = true): string;

    /**
     * Liste tous les utilisateurs avec leurs profils associés, avec pagination et filtres.
     * @param array $criteres Critères de recherche.
     * @param int $page Numéro de page.
     * @param int $elementsParPage Nombre d'éléments par page.
     * @return array Tableau d'utilisateurs.
     */
    public function listerUtilisateursAvecProfils(array $criteres = [], int $page = 1, int $elementsParPage = 20): array;

    /**
     * Met à jour les informations d'un profil utilisateur spécifique.
     * @param string $numeroUtilisateur Le numéro unique de l'utilisateur.
     * @param string $typeProfilCode Le code du type de profil.
     * @param array $donneesProfil Les données spécifiques au profil à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi.
     */
    public function mettreAJourProfilUtilisateur(string $numeroUtilisateur, string $typeProfilCode, array $donneesProfil): bool;

    /**
     * Met à jour les informations de base d'un utilisateur par un administrateur.
     * @param string $numeroUtilisateur Le numéro unique de l'utilisateur à mettre à jour.
     * @param array $donneesCompte Les données du compte utilisateur à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi.
     * @throws \App\Backend\Exception\DoublonException Si le login ou l'email est déjà utilisé par un autre utilisateur.
     */
    public function mettreAJourCompteUtilisateurParAdmin(string $numeroUtilisateur, array $donneesCompte): bool;

    /**
     * Supprime un utilisateur et son profil associé.
     * @param string $numeroUtilisateur Le numéro unique de l'utilisateur à supprimer.
     * @return bool Vrai si la suppression a réussi.
     * @throws \Exception En cas d'erreur de suppression ou de profil non trouvé.
     */
    public function supprimerUtilisateur(string $numeroUtilisateur): bool;

    /**
     * Change le statut du compte d'un utilisateur.
     * @param string $numeroUtilisateur Le numéro unique de l'utilisateur.
     * @param string $nouveauStatut Le nouveau statut ('actif', 'inactif', 'bloque', 'archive').
     * @param string|null $raison Optionnel: la raison du changement de statut.
     * @return bool Vrai si le statut a été modifié.
     */
    public function changerStatutDuCompte(string $numeroUtilisateur, string $nouveauStatut, ?string $raison = null): bool;

    /**
     * Modifie le mot de passe d'un utilisateur.
     * @param string $numeroUtilisateur Le numéro unique de l'utilisateur.
     * @param string $nouveauMotDePasseClair Le nouveau mot de passe en clair.
     * @param string|null $ancienMotDePasseClair L'ancien mot de passe en clair (requis si non reset par admin).
     * @param bool $isAdminReset Indique si la modification est effectuée par un administrateur.
     * @return bool Vrai si la modification a réussi.
     * @throws \App\Backend\Exception\MotDePasseInvalideException Si l'ancien mot de passe est incorrect ou le nouveau n'est pas robuste.
     * @throws \Exception Pour toute autre erreur.
     */
    public function modifierMotDePasse(string $numeroUtilisateur, string $nouveauMotDePasseClair, ?string $ancienMotDePasseClair = null, bool $isAdminReset = false): bool;

    /**
     * Demande la réinitialisation du mot de passe pour un email donné (envoi d'un token par email).
     * @param string $emailPrincipal L'email principal de l'utilisateur.
     * @throws \App\Backend\Exception\EmailException Si l'envoi de l'email échoue.
     */
    public function demanderReinitialisationMotDePasse(string $emailPrincipal): void;

    /**
     * Réinitialise le mot de passe d'un utilisateur après la validation du token.
     * @param string $tokenClair Le token de réinitialisation en clair.
     * @param string $nouveauMotDePasseClair Le nouveau mot de passe en clair.
     * @return bool Vrai si le mot de passe a été réinitialisé.
     * @throws \App\Backend\Exception\TokenInvalideException Si le token est invalide.
     * @throws \App\Backend\Exception\TokenExpireException Si le token a expiré.
     */
    public function reinitialiserMotDePasseApresValidationToken(string $tokenClair, string $nouveauMotDePasseClair): bool;

    /**
     * Génère et stocke le secret 2FA pour un utilisateur.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return array Tableau contenant le secret et l'URL du QR code.
     */
    public function genererEtStockerSecret2FA(string $numeroUtilisateur): array;

    /**
     * Active l'authentification 2FA pour un utilisateur après vérification du code.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $codeTOTP Le code TOTP soumis par l'utilisateur.
     * @return bool Vrai si l'activation réussit.
     * @throws \App\Backend\Exception\IdentifiantsInvalidesException Si le code TOTP est incorrect.
     */
    public function activerAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTP): bool;

    /**
     * Désactive l'authentification 2FA pour un utilisateur.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return bool Vrai si la désactivation réussit.
     */
    public function desactiverAuthentificationDeuxFacteurs(string $numeroUtilisateur): bool;

    /**
     * Vérifie un code TOTP lors d'une connexion avec 2FA.
     * @param string $numeroUtilisateur L'ID de l'utilisateur en attente de vérification 2FA.
     * @param string $codeTOTP Le code TOTP soumis par l'utilisateur.
     * @return bool Vrai si le code est valide.
     */
    public function verifierCodeAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTP): bool;
}