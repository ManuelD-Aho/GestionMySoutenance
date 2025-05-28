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
     * @return object|null L'objet utilisateur complet (avec profil) si la connexion réussit (avant étape 2FA), null sinon, ou lève une exception.
     * @throws IdentifiantsInvalidesException Si les identifiants sont incorrects.
     * @throws CompteBloqueException Si le compte est temporairement bloqué.
     * @throws CompteNonValideException Si le compte n'est pas actif ou l'email non validé.
     * @throws AuthenticationException Si la 2FA est requise.
     * @throws UtilisateurNonTrouveException Si l'identifiant n'est pas trouvé.
     */
    public function tenterConnexion(string $identifiant, string $motDePasse): ?object;

    /**
     * Traite une tentative de connexion échouée pour un identifiant donné, en incrémentant le compteur et en gérant le blocage.
     *
     * @param string $identifiant L'identifiant (login ou email) pour lequel la tentative a échoué.
     * @return void
     * @throws UtilisateurNonTrouveException Si l'utilisateur associé à l'identifiant n'est pas trouvé.
     */
    public function traiterTentativeConnexionEchouee(string $identifiant): void;

    /**
     * Réinitialise le compteur de tentatives de connexion échouées pour un utilisateur.
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return void
     * @throws OperationImpossibleException En cas d'échec de la mise à jour.
     * @throws UtilisateurNonTrouveException Si l'utilisateur n'est pas trouvé.
     */
    public function reinitialiserTentativesConnexion(string $numeroUtilisateur): void;

    /**
     * Vérifie si un compte utilisateur est actuellement bloqué en raison de tentatives de connexion échouées.
     * Gère le déblocage automatique si la période de blocage est expirée.
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return bool True si le compte est actuellement bloqué, false sinon.
     * @throws UtilisateurNonTrouveException Si l'utilisateur n'est pas trouvé.
     */
    public function estCompteActuellementBloque(string $numeroUtilisateur): bool;

    /**
     * Génère un nouveau secret 2FA (TOTP) pour un utilisateur et le stocke (temporairement ou définitivement).
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return string L'URI otpauth:// à utiliser pour générer un QR code (incluant le secret encodé en Base32).
     * @throws OperationImpossibleException En cas d'échec de la génération ou du stockage.
     * @throws UtilisateurNonTrouveException Si l'utilisateur n'est pas trouvé.
     */
    public function genererEtStockerSecret2FA(string $numeroUtilisateur): string;

    /**
     * Valide un code TOTP fourni par l'utilisateur et active la 2FA pour son compte si le code est correct.
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $codeTOTPVerifie Le code TOTP à vérifier.
     * @return bool True si la 2FA est activée avec succès, false sinon.
     * @throws OperationImpossibleException Si le secret 2FA n'a pas été préalablement configuré ou en cas d'erreur.
     * @throws MotDePasseInvalideException Si le code TOTP est invalide.
     * @throws UtilisateurNonTrouveException Si l'utilisateur n'est pas trouvé.
     */
    public function activerAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTPVerifie): bool;

    /**
     * Vérifie un code TOTP fourni par l'utilisateur lors d'une tentative de connexion.
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $codeTOTP Le code TOTP à vérifier.
     * @return bool True si le code est valide, false sinon.
     * @throws OperationImpossibleException Si la 2FA n'est pas active ou le secret non configuré.
     * @throws UtilisateurNonTrouveException Si l'utilisateur n'est pas trouvé.
     */
    public function verifierCodeAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTP): bool;

    /**
     * Désactive la 2FA pour le compte d'un utilisateur.
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return bool True si la désactivation est réussie.
     * @throws UtilisateurNonTrouveException Si l'utilisateur n'est pas trouvé.
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
     * Vérifie si un utilisateur est actuellement connecté et si sa session est encore valide (non expirée).
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
     * Crée un nouveau compte utilisateur et son profil associé (étudiant, enseignant, personnel).
     * Gère la transaction, le hachage du mot de passe, la création du profil et l'envoi optionnel d'email de validation.
     *
     * @param array $donneesUtilisateur Données pour la table `utilisateur` (login, mot_de_passe, id_groupe_utilisateur, etc.).
     * @param array $donneesProfil Données spécifiques au profil (nom, prénom, email du profil, etc.).
     * @param string $typeProfil Type de profil à créer ('etudiant', 'enseignant', 'personnel_administratif').
     * @param bool $envoyerEmailValidation Indique si un email de validation doit être envoyé.
     * @return string|null Le `numero_utilisateur` généré si succès, null sinon.
     * @throws ValidationException Si les données fournies sont invalides.
     * @throws EmailNonValideException Si l'email du profil est invalide ou déjà utilisé.
     * @throws OperationImpossibleException En cas d'erreur système ou de type de profil inconnu.
     * @throws PDOException En cas d'erreur de base de données.
     */
    public function creerCompteUtilisateurComplet(array $donneesUtilisateur, array $donneesProfil, string $typeProfil, bool $envoyerEmailValidation = true): ?string;

    /**
     * Génère un `numero_utilisateur` unique et non séquentiel.
     *
     * @return string Le numéro utilisateur unique.
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
     * Met à jour le statut du compte et l'état de validation de l'email.
     *
     * @param string $tokenValidation Le token de validation (en clair) reçu par l'utilisateur.
     * @return bool True si la validation est réussie.
     * @throws TokenInvalideException Si le token est invalide ou non trouvé.
     * @throws TokenExpireException Si le token a expiré.
     */
    public function validerCompteEmailViaToken(string $tokenValidation): bool;

    /**
     * Récupère les informations complètes (compte + profil) d'un utilisateur par son numéro.
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return object|null L'objet utilisateur complet ou null si non trouvé.
     */
    public function recupererUtilisateurCompletParNumero(string $numeroUtilisateur): ?object;

    /**
     * Récupère les informations complètes (compte + profil) d'un utilisateur par son email principal.
     *
     * @param string $emailPrincipal L'email principal de l'utilisateur.
     * @return object|null L'objet utilisateur complet ou null si non trouvé.
     */
    public function recupererUtilisateurCompletParEmailPrincipal(string $emailPrincipal): ?object;

    /**
     * Récupère les informations complètes (compte + profil) d'un utilisateur par son login.
     *
     * @param string $login Le login de l'utilisateur.
     * @return object|null L'objet utilisateur complet ou null si non trouvé.
     */
    public function recupererUtilisateurCompletParLogin(string $login): ?object;

    /**
     * Liste les utilisateurs avec leurs profils, avec options de filtrage et de pagination.
     *
     * @param array $criteres Critères de filtrage (ex: statut_compte, id_type_utilisateur, recherche_generale).
     * @param int $page Numéro de la page.
     * @param int $elementsParPage Nombre d'éléments par page.
     * @return array Un tableau contenant 'utilisateurs' (liste des objets utilisateurs complets) et 'total_elements'.
     */
    public function listerUtilisateursAvecProfils(array $criteres = [], int $page = 1, int $elementsParPage = 25): array;

    /**
     * Modifie le mot de passe d'un utilisateur.
     * Gère la vérification de l'ancien mot de passe (sauf si modifié par un admin),
     * la robustesse du nouveau mot de passe et l'historique des mots de passe.
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $nouveauMotDePasse Le nouveau mot de passe en clair.
     * @param string|null $ancienMotDePasse L'ancien mot de passe (requis si non modifié par admin).
     * @param bool $parAdmin True si la modification est effectuée par un administrateur (ne vérifie pas l'ancien mdp).
     * @return bool True si la modification est réussie.
     * @throws UtilisateurNonTrouveException Si l'utilisateur n'est pas trouvé.
     * @throws MotDePasseInvalideException Si l'ancien mot de passe est incorrect ou le nouveau déjà utilisé.
     * @throws ValidationException Si le nouveau mot de passe n'est pas assez robuste.
     * @throws OperationImpossibleException En cas d'erreur de base de données.
     */
    public function modifierMotDePasse(string $numeroUtilisateur, string $nouveauMotDePasse, ?string $ancienMotDePasse = null, bool $parAdmin = false): bool;

    /**
     * Met à jour les informations du profil spécifique d'un utilisateur (étudiant, enseignant, etc.).
     * Synchronise l'email principal si l'email du profil est modifié.
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $typeProfil Le type de profil ('etudiant', 'enseignant', 'personnel_administratif').
     * @param array $donneesProfil Les nouvelles données du profil.
     * @return bool True si la mise à jour est réussie.
     * @throws UtilisateurNonTrouveException Si l'utilisateur n'est pas trouvé.
     * @throws OperationImpossibleException Si le type de profil est incorrect ou en cas d'erreur BDD.
     * @throws EmailNonValideException Si le nouvel email de profil est déjà utilisé.
     * @throws ValidationException Si les données de profil sont invalides.
     */
    public function mettreAJourProfilUtilisateur(string $numeroUtilisateur, string $typeProfil, array $donneesProfil): bool;

    /**
     * Met à jour les informations du compte utilisateur de base par un administrateur.
     * (Ex: login, id_groupe_utilisateur, photo_profil, statut_compte).
     * Interdit le changement de id_type_utilisateur via cette méthode (nécessite une procédure plus complexe).
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param array $donneesCompte Les données du compte à mettre à jour.
     * @return bool True si la mise à jour est réussie.
     * @throws UtilisateurNonTrouveException Si l'utilisateur n'est pas trouvé.
     * @throws OperationImpossibleException En cas d'erreur BDD ou de tentative de modification de champ non autorisé.
     * @throws ValidationException Si les données sont invalides.
     */
    public function mettreAJourCompteUtilisateurParAdmin(string $numeroUtilisateur, array $donneesCompte): bool;

    /**
     * Change le statut du compte d'un utilisateur.
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $nouveauStatut Le nouveau statut (ex: 'actif', 'inactif', 'bloque', 'archive').
     * @param string|null $raison Raison optionnelle du changement de statut (pour journalisation).
     * @return bool True si le changement est réussi.
     * @throws UtilisateurNonTrouveException Si l'utilisateur n'est pas trouvé.
     * @throws ValidationException Si le nouveau statut est invalide.
     */
    public function changerStatutDuCompte(string $numeroUtilisateur, string $nouveauStatut, ?string $raison = null): bool;

    /**
     * Vérifie la robustesse d'un mot de passe selon les politiques définies.
     *
     * @param string $motDePasse Le mot de passe à vérifier.
     * @return array Tableau associatif avec 'valide' (bool) et 'erreurs' (array de codes d'erreur de robustesse).
     */
    public function verifierRobustesseMotDePasse(string $motDePasse): array;

    /**
     * Initie la procédure de réinitialisation de mot de passe pour un utilisateur via son email principal.
     * Génère un token, le stocke et envoie un email.
     *
     * @param string $emailPrincipal L'email principal de l'utilisateur.
     * @return bool True si la demande a été traitée (email envoyé ou envoi tenté).
     * @throws UtilisateurNonTrouveException Si aucun compte n'est associé à l'email.
     * @throws CompteNonValideException Si le compte n'est pas actif ou l'email non validé.
     * @throws OperationImpossibleException En cas d'erreur système.
     */
    public function demanderReinitialisationMotDePasse(string $emailPrincipal): bool;

    /**
     * Vérifie la validité et l'expiration d'un token de réinitialisation de mot de passe.
     *
     * @param string $token Le token (en clair) à vérifier.
     * @return string|null Le `numero_utilisateur` associé au token si valide et non expiré, null sinon.
     * @throws TokenInvalideException Si le token n'est pas trouvé.
     * @throws TokenExpireException Si le token a expiré.
     */
    public function validerTokenReinitialisationMotDePasse(string $token): ?string;

    /**
     * Réinitialise le mot de passe d'un utilisateur après validation d'un token.
     * Gère la robustesse et l'historique du nouveau mot de passe.
     *
     * @param string $token Le token de réinitialisation (en clair) validé.
     * @param string $nouveauMotDePasse Le nouveau mot de passe en clair.
     * @return bool True si la réinitialisation est réussie.
     * @throws TokenInvalideException Si le token est invalide après une nouvelle vérification.
     * @throws MotDePasseInvalideException Si le nouveau mot de passe est dans l'historique.
     * @throws ValidationException Si le nouveau mot de passe n'est pas assez robuste.
     * @throws OperationImpossibleException En cas d'erreur système.
     * @throws UtilisateurNonTrouveException Si l'utilisateur associé au token n'est pas trouvé.
     */
    public function reinitialiserMotDePasseApresValidationToken(string $token, string $nouveauMotDePasse): bool;

    /**
     * Récupère l'email principal d'un utilisateur en interrogeant sa table de profil spécifique.
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return string|null L'email du profil ou null si non trouvé ou type d'utilisateur inconnu.
     * @throws UtilisateurNonTrouveException Si l'utilisateur de base n'est pas trouvé.
     */
    public function recupererEmailPrincipalPourUtilisateur(string $numeroUtilisateur): ?string;

    /**
     * Vérifie si un mot de passe haché est présent dans l'historique récent d'un utilisateur.
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $nouveauMotDePasseHache Le nouveau mot de passe déjà haché.
     * @param int $limiteHistorique Le nombre d'entrées récentes à vérifier dans l'historique.
     * @return bool True si le mot de passe est trouvé dans l'historique récent, false sinon.
     * @throws UtilisateurNonTrouveException Si l'utilisateur n'est pas trouvé.
     */
    public function estMotDePasseDansHistorique(string $numeroUtilisateur, string $nouveauMotDePasseHache, int $limiteHistorique = 5): bool;

    /**
     * Journalise une action liée à l'authentification via le service de supervision.
     *
     * @param string $numeroUtilisateurConcerne Le numéro de l'utilisateur concerné par l'action.
     * @param string $libelleAction Un code ou libellé décrivant l'action (ex: 'CONNEXION_REUSSIE').
     * @param string $resultat Le résultat de l'action ('SUCCES', 'ECHEC', 'INFO', 'ALERTE').
     * @param array|null $details Détails supplémentaires sur l'action (format JSON-ifiable).
     * @return void
     */
    public function journaliserActionAuthentification(string $numeroUtilisateurConcerne, string $libelleAction, string $resultat, ?array $details = null): void;
}