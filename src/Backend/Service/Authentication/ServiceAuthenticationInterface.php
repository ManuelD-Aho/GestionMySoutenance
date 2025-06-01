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

/**
 * Interface pour le service d'authentification des utilisateurs.
 * Gère la connexion, la création de compte, la 2FA, la gestion des sessions et les opérations liées aux utilisateurs.
 */
interface ServiceAuthenticationInterface
{
    /**
     * Tente de connecter un utilisateur en utilisant son identifiant (login ou email principal) et son mot de passe.
     * Gère les tentatives échouées, le blocage de compte et la vérification 2FA si activée.
     *
     * @param string $identifiant Login ou email principal de l'utilisateur.
     * @param string $motDePasse Mot de passe en clair.
     * @return object L'objet utilisateur complet (avec profil et informations des tables de référence jointes) si la connexion réussit (avant étape 2FA).
     * @throws IdentifiantsInvalidesException Si les identifiants sont incorrects.
     * @throws CompteBloqueException Si le compte est temporairement bloqué.
     * @throws CompteNonValideException Si le compte n'est pas actif ou l'email non validé.
     * @throws AuthenticationException Si la 2FA est requise (code spécifique pour redirection, ex: 1001).
     * @throws UtilisateurNonTrouveException Si l'identifiant n'est pas trouvé.
     * @throws PDOException En cas d'erreur de base de données.
     */
    public function tenterConnexion(string $identifiant, string $motDePasse): object;

    /**
     * Traite une tentative de connexion échouée pour un utilisateur identifié par son numéro.
     * Incrémente le compteur et gère le blocage du compte si nécessaire.
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur pour lequel la tentative a échoué.
     * @return void
     * @throws UtilisateurNonTrouveException Si l'utilisateur n'est pas trouvé.
     * @throws PDOException En cas d'erreur de base de données.
     */
    public function traiterTentativeConnexionEchoueePourUtilisateur(string $numeroUtilisateur): void;

    /**
     * Réinitialise le compteur de tentatives de connexion échouées pour un utilisateur.
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return void
     * @throws OperationImpossibleException En cas d'échec de la mise à jour.
     * @throws UtilisateurNonTrouveException Si l'utilisateur n'est pas trouvé.
     * @throws PDOException En cas d'erreur de base de données.
     */
    public function reinitialiserTentativesConnexion(string $numeroUtilisateur): void;

    /**
     * Vérifie si un compte utilisateur est actuellement bloqué.
     * Gère le déblocage automatique si la période de blocage est expirée.
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return bool True si le compte est actuellement bloqué, false sinon.
     * @throws UtilisateurNonTrouveException Si l'utilisateur n'est pas trouvé.
     * @throws PDOException En cas d'erreur de base de données.
     */
    public function estCompteActuellementBloque(string $numeroUtilisateur): bool;

    /**
     * Génère un nouveau secret 2FA (TOTP) pour un utilisateur et le stocke.
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return string L'URI otpauth:// à utiliser pour générer un QR code (incluant le secret encodé en Base32).
     * @throws OperationImpossibleException En cas d'échec de la génération ou du stockage.
     * @throws UtilisateurNonTrouveException Si l'utilisateur n'est pas trouvé.
     * @throws PDOException En cas d'erreur de base de données.
     * @throws \SodiumException Si l'encodage Base32 échoue.
     * @throws \RobThree\Auth\TwoFactorAuthException Si la génération du secret 2FA échoue.
     */
    public function genererEtStockerSecret2FA(string $numeroUtilisateur): string;

    /**
     * Valide un code TOTP fourni par l'utilisateur et active la 2FA pour son compte.
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $codeTOTPVerifie Le code TOTP à vérifier.
     * @return bool True si la 2FA est activée avec succès.
     * @throws OperationImpossibleException Si le secret 2FA n'a pas été préalablement configuré ou en cas d'erreur.
     * @throws MotDePasseInvalideException Si le code TOTP est invalide (terme générique pour code invalide).
     * @throws UtilisateurNonTrouveException Si l'utilisateur n'est pas trouvé.
     * @throws PDOException En cas d'erreur de base de données.
     */
    public function activerAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTPVerifie): bool;

    /**
     * Vérifie un code TOTP fourni par l'utilisateur lors d'une tentative de connexion après l'étape du mot de passe.
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $codeTOTP Le code TOTP à vérifier.
     * @return bool True si le code est valide.
     * @throws OperationImpossibleException Si la 2FA n'est pas active ou le secret non configuré.
     * @throws MotDePasseInvalideException Si le code TOTP est invalide.
     * @throws UtilisateurNonTrouveException Si l'utilisateur n'est pas trouvé.
     */
    public function verifierCodeAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTP): bool;

    /**
     * Désactive la 2FA pour le compte d'un utilisateur.
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return bool True si la désactivation est réussie.
     * @throws UtilisateurNonTrouveException Si l'utilisateur n'est pas trouvé.
     * @throws PDOException En cas d'erreur de base de données.
     */
    public function desactiverAuthentificationDeuxFacteurs(string $numeroUtilisateur): bool;

    /**
     * Démarre et configure la session PHP pour un utilisateur authentifié.
     *
     * @param object $utilisateurAvecProfil L'objet utilisateur complet (compte + profil).
     * @return void
     */
    public function demarrerSessionUtilisateur(object $utilisateurAvecProfil): void;

    /**
     * Vérifie si un utilisateur est actuellement connecté et si sa session est encore valide.
     *
     * @return bool True si connecté et session valide, false sinon.
     */
    public function estUtilisateurConnecteEtSessionValide(): bool;

    /**
     * Récupère l'objet utilisateur complet (compte + profil) de la session active.
     *
     * @return object|null L'objet utilisateur ou null si non connecté ou session invalide.
     */
    public function getUtilisateurConnecteComplet(): ?object;

    /**
     * Termine la session de l'utilisateur actuel.
     *
     * @return void
     */
    public function terminerSessionUtilisateur(): void;

    /**
     * Crée un nouveau compte utilisateur et son profil associé.
     *
     * @param array $donneesUtilisateur Données pour la table `utilisateur` (login_utilisateur, mot_de_passe, id_groupe_utilisateur, id_niveau_acces_donne, photo_profil).
     * @param array $donneesProfil Données spécifiques au profil (nom, prenom, email du profil, numero_carte_etudiant/matricule, etc.).
     * @param string $typeProfilLibelle Libellé du type de profil à créer (ex: 'Etudiant', 'Enseignant').
     * @param bool $envoyerEmailValidation Indique si un email de validation doit être envoyé.
     * @return string Le `numero_utilisateur` généré.
     * @throws ValidationException Si les données d'entrée sont invalides (format, robustesse mdp, champs requis manquants).
     * @throws EmailNonValideException Si l'email du profil est invalide ou déjà utilisé comme email_principal.
     * @throws OperationImpossibleException En cas d'erreur système, type de profil inconnu ou problème métier (ex: étudiant non éligible).
     * @throws PDOException En cas d'erreur de base de données.
     * @throws UtilisateurNonTrouveException Si un type ou groupe par défaut n'est pas trouvé.
     */
    public function creerCompteUtilisateurComplet(array $donneesUtilisateur, array $donneesProfil, string $typeProfilLibelle, bool $envoyerEmailValidation = true): string;

    /**
     * Génère un `numero_utilisateur` unique et non séquentiel.
     *
     * @return string Le numéro utilisateur unique.
     * @throws PDOException En cas d'erreur de base de données lors de la vérification d'unicité.
     * @throws \Exception Si la génération de bytes aléatoires échoue.
     */
    public function genererNumeroUtilisateurUniqueNonSequentiel(): string;

    /**
     * Envoie un email à l'utilisateur avec un lien pour valider son adresse email principale.
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $emailPrincipal L'adresse email à laquelle envoyer le lien.
     * @param string $tokenValidation Le token de validation (en clair).
     * @return void
     * @throws OperationImpossibleException En cas d'échec de l'envoi de l'email.
     */
    public function envoyerEmailValidationCompte(string $numeroUtilisateur, string $emailPrincipal, string $tokenValidation): void;

    /**
     * Valide un compte utilisateur via un token de validation d'email.
     * Met à jour le statut du compte à 'actif' et email_valide à true.
     * Le token de validation n'a pas de date d'expiration en base de données.
     *
     * @param string $tokenValidation Le token de validation (en clair) reçu par l'utilisateur.
     * @return bool True si la validation est réussie.
     * @throws TokenInvalideException Si le token est invalide, non trouvé ou déjà utilisé.
     * @throws PDOException En cas d'erreur de base de données.
     */
    public function validerCompteEmailViaToken(string $tokenValidation): bool;

    /**
     * Récupère les informations complètes (compte + profil + libellés des FK) d'un utilisateur par son numéro.
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return object|null L'objet utilisateur complet ou null si non trouvé.
     * @throws PDOException En cas d'erreur de base de données.
     * @throws UtilisateurNonTrouveException Si un type ou groupe pour la jointure n'est pas trouvé.
     */
    public function recupererUtilisateurCompletParNumero(string $numeroUtilisateur): ?object;

    /**
     * Récupère les informations complètes (compte + profil + libellés des FK) d'un utilisateur par son email principal.
     *
     * @param string $emailPrincipal L'email principal de l'utilisateur.
     * @return object|null L'objet utilisateur complet ou null si non trouvé.
     * @throws PDOException En cas d'erreur de base de données.
     * @throws UtilisateurNonTrouveException Si un type ou groupe pour la jointure n'est pas trouvé.
     */
    public function recupererUtilisateurCompletParEmailPrincipal(string $emailPrincipal): ?object;

    /**
     * Récupère les informations complètes (compte + profil + libellés des FK) d'un utilisateur par son login.
     *
     * @param string $login Le login de l'utilisateur.
     * @return object|null L'objet utilisateur complet ou null si non trouvé.
     * @throws PDOException En cas d'erreur de base de données.
     * @throws UtilisateurNonTrouveException Si un type ou groupe pour la jointure n'est pas trouvé.
     */
    public function recupererUtilisateurCompletParLogin(string $login): ?object;

    /**
     * Liste les utilisateurs avec leurs profils et libellés, avec options de filtrage et de pagination.
     *
     * @param array $criteres Critères de filtrage (ex: statut_compte, id_type_utilisateur, recherche_generale).
     * @param int $page Numéro de la page (commence à 1).
     * @param int $elementsParPage Nombre d'éléments par page.
     * @return array Un tableau contenant 'utilisateurs' (liste des objets utilisateurs complets) et 'total_elements'.
     * @throws PDOException En cas d'erreur de base de données.
     * @throws UtilisateurNonTrouveException Si un type utilisateur pour les jointures n'est pas trouvé lors de la construction de la requête de recherche.
     */
    public function listerUtilisateursAvecProfils(array $criteres = [], int $page = 1, int $elementsParPage = 25): array;

    /**
     * Modifie le mot de passe d'un utilisateur.
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $nouveauMotDePasseClair Le nouveau mot de passe en clair.
     * @param string|null $ancienMotDePasseClair L'ancien mot de passe (requis si non modifié par admin).
     * @param bool $parAdmin True si la modification est effectuée par un administrateur.
     * @return bool True si la modification est réussie.
     * @throws UtilisateurNonTrouveException Si l'utilisateur n'est pas trouvé.
     * @throws MotDePasseInvalideException Si l'ancien mot de passe est incorrect ou le nouveau déjà utilisé ou ne respecte pas la politique.
     * @throws ValidationException Si le nouveau mot de passe n'est pas assez robuste.
     * @throws OperationImpossibleException En cas d'erreur de base de données.
     * @throws PDOException En cas d'erreur de base de données.
     */
    public function modifierMotDePasse(string $numeroUtilisateur, string $nouveauMotDePasseClair, ?string $ancienMotDePasseClair = null, bool $parAdmin = false): bool;

    /**
     * Met à jour les informations du profil spécifique d'un utilisateur.
     * Synchronise `utilisateur.email_principal` si l'email du profil change, et réinitialise `email_valide`.
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $typeProfilLibelle Libellé du type de profil (ex: 'Etudiant').
     * @param array $donneesProfil Les nouvelles données du profil.
     * @return bool True si la mise à jour est réussie.
     * @throws UtilisateurNonTrouveException Si l'utilisateur n'est pas trouvé.
     * @throws OperationImpossibleException Si le type de profil est incorrect, ou en cas d'erreur BDD.
     * @throws EmailNonValideException Si le nouvel email de profil est invalide ou déjà utilisé comme email_principal.
     * @throws ValidationException Si les données de profil sont invalides ou des champs requis sont manquants.
     * @throws PDOException En cas d'erreur de base de données.
     */
    public function mettreAJourProfilUtilisateur(string $numeroUtilisateur, string $typeProfilLibelle, array $donneesProfil): bool;

    /**
     * Met à jour les informations du compte utilisateur de base par un administrateur.
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param array $donneesCompte Les données du compte à mettre à jour (ex: login_utilisateur, id_groupe_utilisateur, photo_profil, statut_compte, email_principal).
     * @return bool True si la mise à jour est réussie.
     * @throws UtilisateurNonTrouveException Si l'utilisateur n'est pas trouvé.
     * @throws OperationImpossibleException En cas d'erreur BDD ou tentative de modification de champ non autorisé (ex: id_type_utilisateur via cette méthode).
     * @throws ValidationException Si les données sont invalides (ex: login ou email_principal déjà utilisé).
     * @throws EmailNonValideException Si le nouvel email_principal est invalide.
     * @throws PDOException En cas d'erreur de base de données.
     */
    public function mettreAJourCompteUtilisateurParAdmin(string $numeroUtilisateur, array $donneesCompte): bool;

    /**
     * Change le statut du compte d'un utilisateur. Gère les actions associées (ex: réinitialiser tentatives si passage à actif).
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $nouveauStatut Le nouveau statut (doit être une valeur de l'ENUM `statut_compte`).
     * @param string|null $raison Raison optionnelle du changement (pour journalisation).
     * @return bool True si le changement est réussi.
     * @throws UtilisateurNonTrouveException Si l'utilisateur n'est pas trouvé.
     * @throws ValidationException Si le nouveau statut est invalide.
     * @throws PDOException En cas d'erreur de base de données.
     */
    public function changerStatutDuCompte(string $numeroUtilisateur, string $nouveauStatut, ?string $raison = null): bool;

    /**
     * Vérifie la robustesse d'un mot de passe selon les politiques définies.
     *
     * @param string $motDePasse Le mot de passe à vérifier.
     * @return array Tableau associatif avec 'valide' (bool) et 'messages_erreur' (array de messages d'erreur de robustesse).
     */
    public function verifierRobustesseMotDePasse(string $motDePasse): array;

    /**
     * Initie la procédure de réinitialisation de mot de passe pour un utilisateur via son email principal.
     *
     * @param string $emailPrincipal L'email principal de l'utilisateur.
     * @return bool True si la demande a été traitée.
     * @throws UtilisateurNonTrouveException Si aucun compte n'est associé à l'email.
     * @throws CompteNonValideException Si le compte n'est pas apte à une réinitialisation (non actif, email non validé).
     * @throws OperationImpossibleException En cas d'erreur système (stockage token, envoi email).
     * @throws PDOException En cas d'erreur de base de données.
     */
    public function demanderReinitialisationMotDePasse(string $emailPrincipal): bool;

    /**
     * Vérifie la validité et l'expiration d'un token de réinitialisation de mot de passe.
     *
     * @param string $token Le token (en clair) à vérifier.
     * @return string Le `numero_utilisateur` associé au token si valide et non expiré.
     * @throws TokenInvalideException Si le token n'est pas trouvé.
     * @throws TokenExpireException Si le token a expiré.
     * @throws PDOException En cas d'erreur de base de données.
     */
    public function validerTokenReinitialisationMotDePasse(string $token): string;

    /**
     * Réinitialise le mot de passe d'un utilisateur après validation d'un token.
     *
     * @param string $token Le token de réinitialisation (en clair) validé.
     * @param string $nouveauMotDePasseClair Le nouveau mot de passe en clair.
     * @return bool True si la réinitialisation est réussie.
     * @throws TokenInvalideException Si le token est invalide.
     * @throws TokenExpireException Si le token a expiré.
     * @throws MotDePasseInvalideException Si le nouveau mot de passe est dans l'historique.
     * @throws ValidationException Si le nouveau mot de passe n'est pas assez robuste.
     * @throws OperationImpossibleException En cas d'erreur système.
     * @throws UtilisateurNonTrouveException Si l'utilisateur associé au token n'est pas trouvé.
     * @throws PDOException En cas d'erreur de base de données.
     */
    public function reinitialiserMotDePasseApresValidationToken(string $token, string $nouveauMotDePasseClair): bool;

    /**
     * Récupère l'email source du profil spécifique d'un utilisateur (étudiant.email, enseignant.email_professionnel, etc.).
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return string|null L'email du profil ou null si non trouvé ou type d'utilisateur inconnu.
     * @throws UtilisateurNonTrouveException Si l'utilisateur de base n'est pas trouvé.
     * @throws PDOException En cas d'erreur de base de données.
     */
    public function recupererEmailSourceDuProfil(string $numeroUtilisateur): ?string;

    /**
     * Vérifie si un mot de passe (en clair) est présent dans l'historique récent d'un utilisateur.
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $nouveauMotDePasseClair Le nouveau mot de passe en clair à vérifier.
     * @param int $limiteHistorique Le nombre d'entrées récentes à vérifier.
     * @return bool True si le mot de passe est trouvé dans l'historique récent, false sinon.
     * @throws UtilisateurNonTrouveException Si l'utilisateur n'est pas trouvé.
     * @throws PDOException En cas d'erreur de base de données.
     */
    public function estNouveauMotDePasseDansHistorique(string $numeroUtilisateur, string $nouveauMotDePasseClair, int $limiteHistorique = 3): bool;

    /**
     * Journalise une action liée à l'authentification via le service de supervision.
     *
     * @param string|null $numeroUtilisateurActeur Le numéro de l'utilisateur effectuant l'action (ou null/identifiant spécifique si système/anonyme).
     * @param string $numeroUtilisateurConcerne Le numéro de l'utilisateur concerné par l'action.
     * @param string $libelleAction Un code ou libellé normalisé décrivant l'action (ex: 'CONNEXION_REUSSIE').
     * @param string $resultat Le résultat ('SUCCES', 'ECHEC', 'INFO', 'ALERTE').
     * @param array|null $details Détails supplémentaires (format JSON-ifiable).
     * @return void
     */
    public function journaliserActionAuthentification(?string $numeroUtilisateurActeur, string $numeroUtilisateurConcerne, string $libelleAction, string $resultat, ?array $details = null): void;
}