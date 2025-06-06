<?php

namespace App\Backend\Service\Authentication;

use PDO;
use PDOException;
use DateTime;
use DateTimeImmutable;
use DateInterval;
// use App\Config\Database;
use App\Backend\Model\Utilisateur as UtilisateurModel;
use App\Backend\Model\Etudiant as EtudiantModel;
use App\Backend\Model\Enseignant as EnseignantModel;
use App\Backend\Model\PersonnelAdministratif as PersonnelAdministratifModel;
use App\Backend\Model\HistoriqueMotDePasse as HistoriqueMotDePasseModel;
use App\Backend\Service\Email\ServiceEmailInterface;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\GestionAcademique\ServiceGestionAcademiqueInterface;
use App\Backend\Service\Permissions\ServicePermissionsInterface;
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
use RobThree\Auth\TwoFactorAuth;
// use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;
use App\Backend\Model\BaseModel;


class ServiceAuthentification implements ServiceAuthenticationInterface
{
    private PDO $db;
    private ServiceEmailInterface $serviceEmail;
    private ServiceSupervisionAdminInterface $serviceSupervision;
    private ServiceGestionAcademiqueInterface $serviceGestionAcademique;
    private ServicePermissionsInterface $servicePermissions;
    private TwoFactorAuth $tfaProvider;
    private UtilisateurModel $utilisateurModel;
    private HistoriqueMotDePasseModel $historiqueMotDePasseModel;
    private EtudiantModel $etudiantModel;
    private EnseignantModel $enseignantModel;
    private PersonnelAdministratifModel $personnelAdministratifModel;

    private const MAX_LOGIN_ATTEMPTS = 5;
    private const ACCOUNT_LOCKOUT_DURATION = 'PT15M';
    private const PASSWORD_RESET_TOKEN_EXPIRY_HOURS = 1;
    private const PASSWORD_HISTORY_LIMIT = 3;
    private const TOKEN_LENGHT_BYTES = 32;

    private const PASSWORD_MIN_LENGTH = 10;
    private const PASSWORD_REQ_UPPERCASE = true;
    private const PASSWORD_REQ_LOWERCASE = true;
    private const PASSWORD_REQ_NUMBER = true;
    private const PASSWORD_REQ_SPECIAL = true;
    private const APP_NAME_FOR_2FA = 'GestionMySoutenance';


    public function __construct(
        PDO $db,
        ServiceEmailInterface $serviceEmail,
        ServiceSupervisionAdminInterface $serviceSupervision,
        ServiceGestionAcademiqueInterface $serviceGestionAcademique,
        ServicePermissionsInterface $servicePermissions,
        TwoFactorAuth $tfaProvider,
        UtilisateurModel $utilisateurModel,
        HistoriqueMotDePasseModel $historiqueMotDePasseModel,
        EtudiantModel $etudiantModel,
        EnseignantModel $enseignantModel,
        PersonnelAdministratifModel $personnelAdministratifModel
    ) {
        $this->db = $db;
        $this->serviceEmail = $serviceEmail;
        $this->serviceSupervision = $serviceSupervision;
        $this->serviceGestionAcademique = $serviceGestionAcademique;
        $this->servicePermissions = $servicePermissions;
        $this->tfaProvider = $tfaProvider;
        $this->utilisateurModel = $utilisateurModel;
        $this->historiqueMotDePasseModel = $historiqueMotDePasseModel;
        $this->etudiantModel = $etudiantModel;
        $this->enseignantModel = $enseignantModel;
        $this->personnelAdministratifModel = $personnelAdministratifModel;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function traiterTentativeConnexionEchoueePourUtilisateur(string $numeroUtilisateur): void
    {
        $this->utilisateurModel->commencerTransaction(); // Commence une transaction via le modèle (qui utilise $this->db)
        try {
            // Ligne 254 (environ) - Correction: suppression de FOR UPDATE
            $stmtInc = $this->db->prepare("UPDATE utilisateur SET tentatives_connexion_echouees = COALESCE(tentatives_connexion_echouees, 0) + 1 WHERE numero_utilisateur = :num_user");
            $stmtInc->bindParam(':num_user', $numeroUtilisateur);
            $stmtInc->execute();

            // Il est généralement plus sûr de récupérer les tentatives *après* l'incrémentation dans la même transaction,
            // ou de se fier au nombre de lignes affectées si la logique de blocage est simple.
            // Cependant, pour être sûr d'avoir la valeur la plus récente *avant* de décider de bloquer :
            $stmtCheck = $this->db->prepare("SELECT tentatives_connexion_echouees FROM utilisateur WHERE numero_utilisateur = :num_user");
            $stmtCheck->bindParam(':num_user', $numeroUtilisateur);
            $stmtCheck->execute();
            $tentatives = (int)$stmtCheck->fetchColumn();

            if ($tentatives >= self::MAX_LOGIN_ATTEMPTS) {
                $dateBlocage = (new DateTimeImmutable())->add(new DateInterval(self::ACCOUNT_LOCKOUT_DURATION));
                $stmtLock = $this->db->prepare("UPDATE utilisateur SET compte_bloque_jusqua = :date_blocage, statut_compte = 'bloque' WHERE numero_utilisateur = :num_user");
                $stmtLock->bindValue(':date_blocage', $dateBlocage->format('Y-m-d H:i:s'));
                $stmtLock->bindParam(':num_user', $numeroUtilisateur);
                $stmtLock->execute();
                $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'COMPTE_BLOQUE_MAX_TENTATIVES', 'ALERTE', ['tentatives' => $tentatives]);
            }
            $this->utilisateurModel->validerTransaction(); // Valide la transaction via le modèle
        } catch (PDOException $e) {
            $this->utilisateurModel->annulerTransaction(); // Annule la transaction via le modèle
            error_log("PDOException dans traiterTentativeConnexionEchoueePourUtilisateur pour $numeroUtilisateur: " . $e->getMessage());
            throw $e;
        }
    }

    // ... (Le reste de vos méthodes de ServiceAuthentification.php reste identique à la version précédente) ...
    // Assurez-vous de copier TOUT le reste du code de l'artefact précédent ici.
    // Je vais inclure les méthodes clés de débogage et les autres pour la complétude.

    public function estUtilisateurConnecteEtSessionValide(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // ---- DEBUT DEBUG (Commenté pour éviter "Headers already sent") ----
        /*
        echo "<pre style='background: #f0f0f0; border: 1px solid #ccc; padding: 10px; margin: 10px;'>DEBUG ServiceAuthentification: Contenu de \$_SESSION au début de estUtilisateurConnecteEtSessionValide():<br>";
        var_dump($_SESSION);
        echo "</pre>";
        */
        // ---- FIN DEBUG ----

        if (!isset($_SESSION['numero_utilisateur'])) {
            // ---- DEBUT DEBUG (Commenté) ----
            // echo "<p style='background: yellow; color: black; padding: 5px; border: 1px dashed red; margin: 2px;'>DEBUG ServiceAuthentification: \$_SESSION['numero_utilisateur'] N'EST PAS DÉFINI. Retour false.</p>";
            // ---- FIN DEBUG ----
            return false;
        }

        if (isset($_SESSION['2fa_authentication_pending']) && $_SESSION['2fa_authentication_pending'] === true) {
            // ---- DEBUT DEBUG (Commenté) ----
            // echo "<p style='background: orange; color: white; padding: 5px; border: 1px dashed red; margin: 2px;'>DEBUG ServiceAuthentification: Vérification 2FA en attente. Session considérée NON VALIDE pour accès complet.</p>";
            // ---- FIN DEBUG ----
            return false;
        }

        $sessionTimeout = 3600;
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $sessionTimeout)) {
            // ---- DEBUT DEBUG (Commenté) ----
            // echo "<p style='background: orange; color: white; padding: 5px; border: 1px dashed red; margin: 2px;'>DEBUG ServiceAuthentification: Session expirée (timeout). Appel de terminerSessionUtilisateur().</p>";
            // ---- FIN DEBUG ----
            $this->terminerSessionUtilisateur();
            return false;
        }
        $_SESSION['last_activity'] = time();
        // ---- DEBUT DEBUG (Commenté) ----
        // echo "<p style='background: lightgreen; color: black; padding: 5px; border: 1px dashed red; margin: 2px;'>DEBUG ServiceAuthentification: \$_SESSION['numero_utilisateur'] EST DÉFINI, 2FA non en attente, et SESSION NON EXPIRÉE. Retour true.</p>";
        // ---- FIN DEBUG ----
        return true;
    }

    public function terminerSessionUtilisateur(): void
    {
        $numeroUtilisateurJournal = 'ANONYME_DECONNEXION';
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // ---- DEBUT DEBUG (Commenté) ----
        /*
        echo "<pre style='background: pink; color: black; padding: 10px; margin: 10px;'>DEBUG ServiceAuthentification: Contenu de \$_SESSION AVANT destruction dans terminerSessionUtilisateur():<br>";
        var_dump($_SESSION);
        echo "</pre>";
        */
        // ---- FIN DEBUG ----

        if (isset($_SESSION['numero_utilisateur'])) {
            $numeroUtilisateurJournal = $_SESSION['numero_utilisateur'];
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        // ---- DEBUT DEBUG (Commenté) ----
        /*
        echo "<pre style='background: lightcoral; color: white; padding: 10px; margin: 10px;'>DEBUG ServiceAuthentification: Contenu de \$_SESSION APRES destruction dans terminerSessionUtilisateur():<br>";
        var_dump($_SESSION);
        echo "</pre>";
        */
        // ---- FIN DEBUG ----

        try {
            $this->journaliserActionAuthentification('SYSTEM_POST_LOGOUT', $numeroUtilisateurJournal, 'DECONNEXION_SESSION', 'SUCCES');
        } catch (\Exception $e) {
            error_log("Erreur lors de la journalisation de la déconnexion pour $numeroUtilisateurJournal: " . $e->getMessage());
        }
    }

    // --- Collez ici TOUTES LES AUTRES MÉTHODES de votre ServiceAuthentification.php ---
    public function tenterConnexion(string $identifiant, string $motDePasse): object
    {
        $utilisateurBase = $this->utilisateurModel->trouverParLoginOuEmailPrincipal($identifiant);

        if (!$utilisateurBase) {
            $this->journaliserActionAuthentification(null, $identifiant, 'TENTATIVE_CONNEXION_IDENTIFIANT_INCONNU', 'ECHEC', ['identifiant_fourni' => $identifiant]);
            throw new UtilisateurNonTrouveException("Identifiant ou mot de passe incorrect.");
        }

        $numeroUtilisateur = $utilisateurBase['numero_utilisateur'];

        if ($this->estCompteActuellementBloque($numeroUtilisateur)) {
            $this->journaliserActionAuthentification(null, $numeroUtilisateur, 'TENTATIVE_CONNEXION_COMPTE_BLOQUE', 'ECHEC');
            throw new CompteBloqueException("Ce compte est temporairement bloqué. Veuillez réessayer plus tard ou contacter le support.");
        }

        if (!password_verify($motDePasse, $utilisateurBase['mot_de_passe'])) {
            $this->traiterTentativeConnexionEchoueePourUtilisateur($numeroUtilisateur);
            $this->journaliserActionAuthentification(null, $numeroUtilisateur, 'TENTATIVE_CONNEXION_MDP_INCORRECT', 'ECHEC');
            throw new IdentifiantsInvalidesException("Identifiant ou mot de passe incorrect.");
        }

        if ($utilisateurBase['statut_compte'] === 'en_attente_validation') {
            $this->journaliserActionAuthentification(null, $numeroUtilisateur, 'TENTATIVE_CONNEXION_COMPTE_NON_VALIDE_EMAIL', 'ECHEC');
            throw new CompteNonValideException("Votre compte n'a pas encore été validé par email. Veuillez vérifier vos emails.");
        }

        if ($utilisateurBase['statut_compte'] !== 'actif') {
            $this->journaliserActionAuthentification(null, $numeroUtilisateur, 'TENTATIVE_CONNEXION_COMPTE_NON_ACTIF', 'ECHEC', ['statut_actuel' => $utilisateurBase['statut_compte']]);
            throw new CompteNonValideException("Ce compte n'est pas actif (statut: " . htmlspecialchars($utilisateurBase['statut_compte']) . "). Veuillez contacter le support.");
        }

        $preferences2FAActive = false;
        if (isset($utilisateurBase['preferences_2fa_active'])) {
            if (is_string($utilisateurBase['preferences_2fa_active'])) {
                $preferences2FAActive = ($utilisateurBase['preferences_2fa_active'] === '1');
            } elseif (is_int($utilisateurBase['preferences_2fa_active'])) {
                $preferences2FAActive = ($utilisateurBase['preferences_2fa_active'] === 1);
            } elseif (is_bool($utilisateurBase['preferences_2fa_active'])) {
                $preferences2FAActive = $utilisateurBase['preferences_2fa_active'];
            }
        }

        if ($preferences2FAActive) {
            $_SESSION['2fa_user_num_pending_verification'] = $numeroUtilisateur;
            $_SESSION['2fa_authentication_pending'] = true;
            $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'CONNEXION_2FA_REQUISE', 'INFO');
            $authException = new AuthenticationException("Authentification à deux facteurs requise.", 1001);
            throw $authException;
        }

        $this->reinitialiserTentativesConnexion($numeroUtilisateur);
        $this->mettreAJourDerniereConnexion($numeroUtilisateur);

        $utilisateurComplet = $this->recupererUtilisateurCompletParNumero($numeroUtilisateur);
        if (!$utilisateurComplet) {
            $this->journaliserActionAuthentification(null, $numeroUtilisateur, 'CONNEXION_ECHEC_RECUP_PROFIL', 'ERREUR_INTERNE');
            throw new OperationImpossibleException("Impossible de récupérer les informations complètes de l'utilisateur après connexion.");
        }
        $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'CONNEXION_REUSSIE', 'SUCCES');
        return $utilisateurComplet;
    }

    public function reinitialiserTentativesConnexion(string $numeroUtilisateur): void
    {
        $user = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['numero_utilisateur']);
        if (!$user) {
            error_log("Tentative de réinitialisation des tentatives pour un utilisateur non trouvé: $numeroUtilisateur");
            return;
        }
        try {
            $success = $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, [
                'tentatives_connexion_echouees' => 0,
                'compte_bloque_jusqua' => null
            ]);
            if (!$success) {
                error_log("Échec de la réinitialisation des tentatives pour l'utilisateur '$numeroUtilisateur'.");
            }
        } catch (PDOException $e) {
            error_log("PDOException lors de la réinitialisation des tentatives pour $numeroUtilisateur: " . $e->getMessage());
        }
    }

    public function estCompteActuellementBloque(string $numeroUtilisateur): bool
    {
        $user = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['compte_bloque_jusqua', 'statut_compte']);
        if (!$user) {
            return false;
        }

        if ($user['statut_compte'] === 'bloque' && $user['compte_bloque_jusqua']) {
            try {
                $dateBlocageFin = new DateTimeImmutable($user['compte_bloque_jusqua']);
                if (new DateTimeImmutable() < $dateBlocageFin) {
                    return true;
                } else {
                    $this->changerStatutDuCompte($numeroUtilisateur, 'actif', 'Déblocage automatique après expiration du délai.');
                    return false;
                }
            } catch (\Exception $e) {
                error_log("Erreur de date pour compte_bloque_jusqua pour $numeroUtilisateur: " . $e->getMessage());
                return true;
            }
        }
        return $user['statut_compte'] === 'bloque';
    }

    public function genererEtStockerSecret2FA(string $numeroUtilisateur): string
    {
        if (!extension_loaded('sodium') && !function_exists('paragonie_sodium_compat_bin2base32')) {
            throw new \RuntimeException("L'extension Sodium ou paragonie/sodium_compat est requise pour l'encodage Base32 du secret 2FA.");
        }
        $user = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['numero_utilisateur', 'email_principal', 'login_utilisateur']);
        if (!$user) {
            throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé pour génération secret 2FA.");
        }

        $secretBase32 = $this->tfaProvider->createSecret(160);

        $success = $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['secret_2fa' => $secretBase32]);
        if (!$success) {
            throw new OperationImpossibleException("Impossible de stocker le secret 2FA pour l'utilisateur '$numeroUtilisateur'.");
        }
        $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'GENERATION_SECRET_2FA', 'SUCCES');

        $label = $user['email_principal'] ?: $user['login_utilisateur'] ?: $numeroUtilisateur;
        return $this->tfaProvider->getQRCodeImageAsDataUri(rawurlencode(self::APP_NAME_FOR_2FA . ':' . $label), $secretBase32);
    }

    public function activerAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTPVerifie): bool
    {
        $user = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['numero_utilisateur', 'secret_2fa']);
        if (!$user) {
            throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé.");
        }
        if (empty($user['secret_2fa'])) {
            throw new OperationImpossibleException("Secret 2FA non configuré pour l'utilisateur '$numeroUtilisateur'. Veuillez d'abord générer un secret.");
        }

        if ($this->tfaProvider->verifyCode($user['secret_2fa'], $codeTOTPVerifie, 2)) {
            $success = $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['preferences_2fa_active' => true]);
            if ($success) {
                $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'ACTIVATION_2FA', 'SUCCES');
                return true;
            }
            throw new OperationImpossibleException("Échec de la mise à jour pour activer la 2FA pour l'utilisateur '$numeroUtilisateur'.");
        }
        $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'ACTIVATION_2FA_CODE_INVALIDE', 'ECHEC');
        throw new MotDePasseInvalideException("Code d'authentification à deux facteurs invalide.");
    }

    public function verifierCodeAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTP): bool
    {
        $user = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['numero_utilisateur', 'secret_2fa', 'preferences_2fa_active']);
        if (!$user) {
            throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé.");
        }

        $pref2FA = false;
        if (isset($user['preferences_2fa_active'])) {
            if (is_string($user['preferences_2fa_active'])) $pref2FA = ($user['preferences_2fa_active'] === '1');
            elseif (is_int($user['preferences_2fa_active'])) $pref2FA = ($user['preferences_2fa_active'] === 1);
            elseif (is_bool($user['preferences_2fa_active'])) $pref2FA = $user['preferences_2fa_active'];
        }

        if (!$pref2FA || empty($user['secret_2fa'])) {
            $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'VERIFICATION_2FA_NON_ACTIVE_OU_SECRET_MANQUANT', 'ECHEC');
            throw new OperationImpossibleException("L'authentification à deux facteurs n'est pas active ou le secret n'est pas configuré pour cet utilisateur.");
        }

        $isValid = $this->tfaProvider->verifyCode($user['secret_2fa'], $codeTOTP, 2);
        if ($isValid) {
            $this->reinitialiserTentativesConnexion($numeroUtilisateur);
            $this->mettreAJourDerniereConnexion($numeroUtilisateur);
            $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'VERIFICATION_2FA_REUSSIE', 'SUCCES');
        } else {
            $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'VERIFICATION_2FA_ECHOUEE', 'ECHEC');
            throw new MotDePasseInvalideException("Code d'authentification à deux facteurs invalide.");
        }
        return $isValid;
    }

    public function desactiverAuthentificationDeuxFacteurs(string $numeroUtilisateur): bool
    {
        $user = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['numero_utilisateur']);
        if (!$user) {
            throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé.");
        }
        $success = $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['preferences_2fa_active' => false, 'secret_2fa' => null]);
        if ($success) {
            $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'DESACTIVATION_2FA', 'SUCCES');
        }
        return $success;
    }

    public function demarrerSessionUtilisateur(object $utilisateurAvecProfil): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);
        $_SESSION['numero_utilisateur'] = $utilisateurAvecProfil->numero_utilisateur;
        $_SESSION['login_utilisateur'] = $utilisateurAvecProfil->login_utilisateur ?? null;
        $_SESSION['id_type_utilisateur'] = $utilisateurAvecProfil->id_type_utilisateur ?? null;
        $_SESSION['id_groupe_utilisateur'] = $utilisateurAvecProfil->id_groupe_utilisateur ?? null;
        $_SESSION['user_complet'] = $utilisateurAvecProfil;
        $_SESSION['last_activity'] = time();

        unset($_SESSION['2fa_user_num_pending_verification']);
        unset($_SESSION['2fa_authentication_pending']);
    }

    public function getUtilisateurConnecteComplet(): ?object
    {
        if ($this->estUtilisateurConnecteEtSessionValide() && isset($_SESSION['user_complet'])) {
            if (is_object($_SESSION['user_complet'])) {
                return $_SESSION['user_complet'];
            } else {
                if(isset($_SESSION['numero_utilisateur'])) {
                    error_log("DEBUG: \$_SESSION['user_complet'] n'était pas un objet. Tentative de reconstruction pour {$_SESSION['numero_utilisateur']}.");
                    $_SESSION['user_complet'] = $this->recupererUtilisateurCompletParNumero($_SESSION['numero_utilisateur']);
                    return $_SESSION['user_complet'];
                }
            }
        }
        return null;
    }

    public function creerCompteUtilisateurComplet(array $donneesUtilisateur, array $donneesProfil, string $typeProfilLibelle, bool $envoyerEmailValidation = true): string
    {
        $this->validerDonneesCreationCompteGlobale($donneesUtilisateur, $donneesProfil, $typeProfilLibelle);

        $numeroUtilisateur = $this->genererNumeroUtilisateurUniqueNonSequentiel();
        $motDePasseHache = password_hash($donneesUtilisateur['mot_de_passe'], PASSWORD_ARGON2ID ?: PASSWORD_DEFAULT);

        $emailProfil = $this->extraireEmailDuProfilConcret($donneesProfil, $typeProfilLibelle);
        if (!$emailProfil || !filter_var($emailProfil, FILTER_VALIDATE_EMAIL)) {
            throw new EmailNonValideException("L'email fourni pour le profil ('$emailProfil') est invalide.");
        }
        if ($this->utilisateurModel->trouverParEmailPrincipal($emailProfil)) {
            throw new EmailNonValideException("L'email principal '$emailProfil' est déjà utilisé par un autre compte.");
        }
        if ($this->utilisateurModel->trouverParLoginUtilisateur($donneesUtilisateur['login_utilisateur'])) {
            throw new ValidationException("Le login utilisateur '{$donneesUtilisateur['login_utilisateur']}' est déjà utilisé.");
        }

        $idTypeUtilisateur = $this->getIdTypeUtilisateurParLibelle($typeProfilLibelle);
        if ($idTypeUtilisateur === null) {
            throw new OperationImpossibleException("Type de profil '$typeProfilLibelle' inconnu.");
        }

        if (strtolower($typeProfilLibelle) === 'etudiant') {
            if (!isset($donneesProfil['numero_carte_etudiant']) || (isset($donneesUtilisateur['id_annee_academique']) && empty($donneesUtilisateur['id_annee_academique']))) {
                throw new ValidationException("Numéro de carte étudiant et ID année académique d'inscription sont requis pour un étudiant.");
            }
            // $statutScolarite = $this->serviceGestionAcademique->verifierStatutScolariteEtudiant($donneesProfil['numero_carte_etudiant'], $donneesUtilisateur['id_annee_academique']);
            // if (!$statutScolarite || !($statutScolarite['eligible_creation_compte'] ?? false)) {
            //     throw new OperationImpossibleException("L'étudiant (carte: {$donneesProfil['numero_carte_etudiant']}) n'est pas éligible à la création de compte selon son statut de scolarité actuel.");
            // }
        }

        $this->db->beginTransaction();
        try {
            $donneesBaseUtilisateur = [
                'numero_utilisateur' => $numeroUtilisateur,
                'login_utilisateur' => $donneesUtilisateur['login_utilisateur'],
                'mot_de_passe' => $motDePasseHache,
                'id_type_utilisateur' => $idTypeUtilisateur,
                'id_groupe_utilisateur' => $donneesUtilisateur['id_groupe_utilisateur'] ?? $this->getDefaultGroupIdForTypeLibelle($typeProfilLibelle),
                'email_principal' => $emailProfil,
                'statut_compte' => $envoyerEmailValidation ? 'en_attente_validation' : 'actif',
                'email_valide' => !$envoyerEmailValidation,
                'photo_profil' => $donneesUtilisateur['photo_profil'] ?? null,
                'id_niveau_acces_donne' => $donneesUtilisateur['id_niveau_acces_donne'] ?? $this->getDefaultNiveauAccesId()
            ];
            $this->utilisateurModel->creer($donneesBaseUtilisateur);

            $this->creerProfilSpecifiqueAssocie($numeroUtilisateur, $donneesProfil, $idTypeUtilisateur);
            $this->ajouterMotDePasseHistorique($numeroUtilisateur, $motDePasseHache);

            if ($envoyerEmailValidation) {
                $tokenValidationData = $this->genererEtStockerTokenPourUtilisateur($numeroUtilisateur, 'token_validation_email');
                $tokenValidation = $tokenValidationData['token_clair'];
                $this->envoyerEmailValidationCompte($numeroUtilisateur, $emailProfil, $tokenValidation);
            }

            $this->db->commit();
            $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'CREATION_COMPTE_' . strtoupper(str_replace(' ', '_', $typeProfilLibelle)), 'SUCCES');
            return $numeroUtilisateur;

        } catch (PDOException $e) {
            $this->db->rollBack();
            $this->journaliserActionAuthentification(null, $numeroUtilisateur ?: 'N/A_CREATION_ECHEC', 'CREATION_COMPTE_ECHEC_DB', 'ECHEC', ['erreur' => $e->getMessage()]);
            if ((int)$e->getCode() === 23000) {
                if (stripos($e->getMessage(), 'login_utilisateur') !== false) {
                    throw new ValidationException("Ce login utilisateur est déjà utilisé.", [], (int)$e->getCode(), $e);
                }
                if (stripos($e->getMessage(), 'email_principal') !== false) {
                    throw new EmailNonValideException("Cet email principal est déjà utilisé.", (int)$e->getCode(), $e);
                }
            }
            throw new OperationImpossibleException("Erreur de base de données lors de la création du compte: " . $e->getMessage(), (int)$e->getCode(), $e);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->journaliserActionAuthentification(null, $numeroUtilisateur ?: 'N/A_CREATION_ECHEC', 'CREATION_COMPTE_ECHEC_GENERAL', 'ECHEC', ['erreur' => $e->getMessage()]);
            throw new OperationImpossibleException("Erreur générale lors de la création du compte: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function genererNumeroUtilisateurUniqueNonSequentiel(): string
    {
        $maxTentatives = 10;
        $tentative = 0;
        do {
            $entropy = bin2hex(random_bytes(6));
            $prefix = 'U' . date('y');
            $numero = $prefix . strtoupper($entropy);
            if (strlen($numero) > 50) {
                $numero = substr($numero, 0, 50);
            }
            $tentative++;
            if ($tentative > $maxTentatives) {
                throw new OperationImpossibleException("Impossible de générer un numero_utilisateur unique après $maxTentatives tentatives.");
            }
        } while ($this->utilisateurModel->trouverParNumeroUtilisateur($numero, ['numero_utilisateur']));
        return $numero;
    }

    public function envoyerEmailValidationCompte(string $numeroUtilisateur, string $emailPrincipal, string $tokenValidation): void
    {
        $utilisateur = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['login_utilisateur']);
        $login = $utilisateur['login_utilisateur'] ?? 'Nouvel utilisateur';

        $sujet = "Validation de votre compte GestionMySoutenance";
        $urlValidation = rtrim(getenv('APP_URL') ?: ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'), '/') . '/validate-email?token=' . urlencode($tokenValidation);
        $corps = "Bonjour " . htmlspecialchars($login) . ",\n\nVeuillez cliquer sur le lien suivant pour valider l'adresse email associée à votre compte : " . $urlValidation . "\n\nSi vous n'avez pas créé de compte, veuillez ignorer cet email.\n\nCordialement,\nL'équipe GestionMySoutenance";

        try {
            $this->serviceEmail->envoyerEmail($emailPrincipal, $sujet, $corps);
            $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'ENVOI_EMAIL_VALIDATION_COMPTE', 'SUCCES', ['email_destinataire' => $emailPrincipal]);
        } catch (\Exception $e) {
            $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'ENVOI_EMAIL_VALIDATION_COMPTE_ECHEC', 'ECHEC', ['erreur' => $e->getMessage(), 'email_destinataire' => $emailPrincipal]);
            throw new OperationImpossibleException("Erreur lors de l'envoi de l'email de validation: " . $e->getMessage(), 0, $e);
        }
    }

    public function validerCompteEmailViaToken(string $tokenValidation): bool
    {
        if (empty($tokenValidation)) {
            throw new TokenInvalideException("Token de validation manquant.");
        }
        $tokenHache = hash('sha256', $tokenValidation);

        $user = $this->utilisateurModel->trouverUnParCritere(['token_validation_email' => $tokenHache], ['numero_utilisateur', 'statut_compte', 'email_valide']);

        if (!$user) {
            throw new TokenInvalideException("Token de validation invalide ou déjà utilisé.");
        }
        if ($user['email_valide'] == 1 || $user['email_valide'] === true) {
            throw new TokenInvalideException("Ce compte a déjà été validé par email.");
        }

        $this->db->beginTransaction();
        try {
            $champsMaj = ['email_valide' => true, 'token_validation_email' => null];
            if ($user['statut_compte'] === 'en_attente_validation') {
                $champsMaj['statut_compte'] = 'actif';
            }
            $this->utilisateurModel->mettreAJourChamps($user['numero_utilisateur'], $champsMaj);
            $this->db->commit();
            $this->journaliserActionAuthentification($user['numero_utilisateur'], $user['numero_utilisateur'], 'VALIDATION_EMAIL_TOKEN_REUSSIE', 'SUCCES');
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            $this->journaliserActionAuthentification(null, $user['numero_utilisateur'], 'VALIDATION_EMAIL_TOKEN_ECHEC_DB', 'ECHEC', ['erreur' => $e->getMessage()]);
            throw new OperationImpossibleException("Erreur de base de données lors de la validation du compte: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    private function construireObjetUtilisateurComplet(array $utilisateurBase): ?object
    {
        if (empty($utilisateurBase) || !isset($utilisateurBase['numero_utilisateur'])) {
            return null;
        }
        $profil = [];
        $tableProfil = $this->getTableProfilParIdType($utilisateurBase['id_type_utilisateur'] ?? null);

        if ($tableProfil && isset($utilisateurBase['id_type_utilisateur'])) {
            $modelProfil = $this->getModelPourTableProfil($tableProfil);
            $profilsData = $modelProfil->trouverParCritere(['numero_utilisateur' => $utilisateurBase['numero_utilisateur']]);
            if (!empty($profilsData)) {
                $profil = $profilsData[0];
            }
        }

        $typeUtilisateur = $this->recupererLibelleTableRef('type_utilisateur', 'id_type_utilisateur', $utilisateurBase['id_type_utilisateur'] ?? null, 'libelle_type_utilisateur');
        $groupeUtilisateur = $this->recupererLibelleTableRef('groupe_utilisateur', 'id_groupe_utilisateur', $utilisateurBase['id_groupe_utilisateur'] ?? null, 'libelle_groupe_utilisateur');
        $niveauAcces = $this->recupererLibelleTableRef('niveau_acces_donne', 'id_niveau_acces_donne', $utilisateurBase['id_niveau_acces_donne'] ?? null, 'libelle_niveau_acces_donne');

        $merged = array_merge($utilisateurBase, $profil);
        $merged['libelle_type_utilisateur'] = $typeUtilisateur;
        $merged['libelle_groupe_utilisateur'] = $groupeUtilisateur;
        $merged['libelle_niveau_acces_donne'] = $niveauAcces;

        return (object) $merged;
    }

    private function recupererLibelleTableRef(string $table, string $colonneId, ?string $valeurId, string $colonneLibelle): ?string
    {
        if ($valeurId === null) return null;
        try {
            $stmt = $this->db->prepare("SELECT `$colonneLibelle` FROM `$table` WHERE `$colonneId` = :id LIMIT 1");
            $stmt->bindParam(':id', $valeurId);
            $stmt->execute();
            return $stmt->fetchColumn() ?: null;
        } catch (PDOException $e) {
            error_log("Erreur SQL dans recupererLibelleTableRef pour $table.$colonneLibelle avec ID $valeurId: " . $e->getMessage());
            return null;
        }
    }

    public function recupererUtilisateurCompletParNumero(string $numeroUtilisateur): ?object
    {
        $utilisateurBase = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur);
        if (!$utilisateurBase) return null;
        return $this->construireObjetUtilisateurComplet($utilisateurBase);
    }

    public function recupererUtilisateurCompletParEmailPrincipal(string $emailPrincipal): ?object
    {
        $utilisateurBase = $this->utilisateurModel->trouverParEmailPrincipal($emailPrincipal);
        if (!$utilisateurBase) return null;
        return $this->construireObjetUtilisateurComplet($utilisateurBase);
    }

    public function recupererUtilisateurCompletParLogin(string $login): ?object
    {
        $utilisateurBase = $this->utilisateurModel->trouverParLoginUtilisateur($login);
        if (!$utilisateurBase) return null;
        return $this->construireObjetUtilisateurComplet($utilisateurBase);
    }

    public function listerUtilisateursAvecProfils(array $criteres = [], int $page = 1, int $elementsParPage = 25): array
    {
        $offset = ($page - 1) * $elementsParPage;

        $idTypeEtudiant = $this->getIdTypeUtilisateurParLibelle('Etudiant');
        $idTypeEnseignant = $this->getIdTypeUtilisateurParLibelle('Enseignant');
        $idTypePersonnelAdmin = $this->getIdTypeUtilisateurParLibelle('Personnel Administratif');

        $selectFields = [
            "u.*",
            "tu.libelle_type_utilisateur",
            "gu.libelle_groupe_utilisateur",
            "na.libelle_niveau_acces_donne",
            "COALESCE(et.nom, en.nom, pa.nom) as nom_profil",
            "COALESCE(et.prenom, en.prenom, pa.prenom) as prenom_profil",
            "COALESCE(et.email_contact_secondaire, en.email_professionnel, pa.email_professionnel) as email_profil_specifique"
        ];

        $fromClause = "utilisateur u";
        $joins = [
            "LEFT JOIN type_utilisateur tu ON u.id_type_utilisateur = tu.id_type_utilisateur",
            "LEFT JOIN groupe_utilisateur gu ON u.id_groupe_utilisateur = gu.id_groupe_utilisateur",
            "LEFT JOIN niveau_acces_donne na ON u.id_niveau_acces_donne = na.id_niveau_acces_donne"
        ];

        if ($idTypeEtudiant) {
            $joins[] = "LEFT JOIN etudiant et ON u.numero_utilisateur = et.numero_utilisateur AND u.id_type_utilisateur = " . $this->db->quote($idTypeEtudiant);
        }
        if ($idTypeEnseignant) {
            $joins[] = "LEFT JOIN enseignant en ON u.numero_utilisateur = en.numero_utilisateur AND u.id_type_utilisateur = " . $this->db->quote($idTypeEnseignant);
        }
        if ($idTypePersonnelAdmin) {
            $joins[] = "LEFT JOIN personnel_administratif pa ON u.numero_utilisateur = pa.numero_utilisateur AND u.id_type_utilisateur = " . $this->db->quote($idTypePersonnelAdmin);
        }

        $whereClauses = [];
        $params = [];

        if (!empty($criteres['statut_compte'])) {
            $whereClauses[] = "u.statut_compte = :statut_compte";
            $params[':statut_compte'] = $criteres['statut_compte'];
        }
        if (!empty($criteres['id_type_utilisateur'])) {
            $whereClauses[] = "u.id_type_utilisateur = :id_type_utilisateur";
            $params[':id_type_utilisateur'] = $criteres['id_type_utilisateur'];
        }
        if (!empty($criteres['id_groupe_utilisateur'])) {
            $whereClauses[] = "u.id_groupe_utilisateur = :id_groupe_utilisateur";
            $params[':id_groupe_utilisateur'] = $criteres['id_groupe_utilisateur'];
        }

        if (!empty($criteres['recherche_generale'])) {
            $searchTerm = '%' . $criteres['recherche_generale'] . '%';
            $searchConditions = [
                "u.login_utilisateur LIKE :recherche",
                "u.email_principal LIKE :recherche",
                "u.numero_utilisateur LIKE :recherche",
                "COALESCE(et.nom, en.nom, pa.nom) LIKE :recherche",
                "COALESCE(et.prenom, en.prenom, pa.prenom) LIKE :recherche",
                "COALESCE(et.email_contact_secondaire, en.email_professionnel, pa.email_professionnel) LIKE :recherche"
            ];
            if ($idTypeEtudiant) {
                $searchConditions[] = "et.numero_carte_etudiant LIKE :recherche";
            }
            $whereClauses[] = "(" . implode(" OR ", $searchConditions) . ")";
            $params[':recherche'] = $searchTerm;
        }

        $sqlWhere = "";
        if (!empty($whereClauses)) {
            $sqlWhere = " WHERE " . implode(" AND ", $whereClauses);
        }

        $sqlCount = "SELECT COUNT(DISTINCT u.numero_utilisateur) FROM " . $fromClause . " " . implode(" ", $joins) . $sqlWhere;
        $stmtCount = $this->db->prepare($sqlCount);
        $stmtCount->execute($params);
        $totalElements = (int)$stmtCount->fetchColumn();

        $sqlQuery = "SELECT " . implode(", ", $selectFields) . " FROM " . $fromClause . " " . implode(" ", $joins) . $sqlWhere . " ORDER BY u.date_creation DESC LIMIT :limit OFFSET :offset";
        $stmtQuery = $this->db->prepare($sqlQuery);

        foreach ($params as $key => $value) {
            $stmtQuery->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmtQuery->bindParam(':limit', $elementsParPage, PDO::PARAM_INT);
        $stmtQuery->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmtQuery->execute();

        $utilisateursData = $stmtQuery->fetchAll(PDO::FETCH_ASSOC);
        $utilisateurs = array_map(fn($data) => (object)$data, $utilisateursData);

        return ['utilisateurs' => $utilisateurs, 'total_elements' => $totalElements];
    }

    public function modifierMotDePasse(string $numeroUtilisateur, string $nouveauMotDePasseClair, ?string $ancienMotDePasseClair = null, bool $parAdmin = false): bool
    {
        $user = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['numero_utilisateur', 'mot_de_passe']);
        if (!$user) {
            throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé pour modification de mot de passe.");
        }

        if (!$parAdmin) {
            if ($ancienMotDePasseClair === null || !password_verify($ancienMotDePasseClair, $user['mot_de_passe'])) {
                $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'MODIF_MDP_ANCIEN_MDP_INCORRECT', 'ECHEC');
                throw new MotDePasseInvalideException("L'ancien mot de passe fourni est incorrect.");
            }
        }

        $robustesse = $this->verifierRobustesseMotDePasse($nouveauMotDePasseClair);
        if (!$robustesse['valide']) {
            $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'MODIF_MDP_NON_ROBUSTE', 'ECHEC', ['erreurs' => $robustesse['messages_erreur']]);
            throw new ValidationException("Le nouveau mot de passe n'est pas assez robuste: " . implode(', ', $robustesse['messages_erreur']));
        }

        if ($this->estNouveauMotDePasseDansHistorique($numeroUtilisateur, $nouveauMotDePasseClair, self::PASSWORD_HISTORY_LIMIT)) {
            $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'MODIF_MDP_DANS_HISTORIQUE', 'ECHEC');
            throw new MotDePasseInvalideException("Le nouveau mot de passe a déjà été utilisé récemment. Veuillez en choisir un autre.");
        }

        $nouveauMotDePasseHache = password_hash($nouveauMotDePasseClair, PASSWORD_ARGON2ID ?: PASSWORD_DEFAULT);

        $this->db->beginTransaction();
        try {
            $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['mot_de_passe' => $nouveauMotDePasseHache, 'date_derniere_modif_mdp' => (new DateTimeImmutable())->format('Y-m-d H:i:s')]);
            $this->ajouterMotDePasseHistorique($numeroUtilisateur, $nouveauMotDePasseHache);
            $this->nettoyerHistoriqueMotDePasse($numeroUtilisateur);
            $this->db->commit();
            $this->journaliserActionAuthentification($parAdmin ? 'ADMIN' : $numeroUtilisateur, $numeroUtilisateur, 'MODIF_MDP_REUSSIE', 'SUCCES', ['par_admin' => $parAdmin]);
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            $this->journaliserActionAuthentification($parAdmin ? 'ADMIN' : $numeroUtilisateur, $numeroUtilisateur, 'MODIF_MDP_ECHEC_DB', 'ECHEC', ['erreur' => $e->getMessage()]);
            throw new OperationImpossibleException("Erreur de base de données lors de la modification du mot de passe: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function mettreAJourProfilUtilisateur(string $numeroUtilisateur, string $typeProfilLibelle, array $donneesProfil): bool
    {
        $userBase = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['numero_utilisateur', 'id_type_utilisateur', 'email_principal']);
        if (!$userBase) {
            throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé.");
        }

        $idTypeProfilAttendu = $this->getIdTypeUtilisateurParLibelle($typeProfilLibelle);
        if ($userBase['id_type_utilisateur'] != $idTypeProfilAttendu) {
            throw new OperationImpossibleException("Le type de profil '$typeProfilLibelle' fourni ne correspond pas à l'utilisateur '$numeroUtilisateur'.");
        }

        $tableProfil = $this->getTableProfilParIdType($userBase['id_type_utilisateur']);
        if (!$tableProfil) {
            return true;
        }

        $modelProfil = $this->getModelPourTableProfil($tableProfil);
        $champsAMettreAJourProfil = [];
        $nouvelEmailProfil = null;
        $emailProfilChampNom = $this->getChampEmailProfilParIdType($userBase['id_type_utilisateur']);
        $colonnesProfil = $this->getColonnesAttenduesPourTableProfil($tableProfil);

        foreach ($donneesProfil as $champ => $valeur) {
            if (!in_array($champ, $colonnesProfil) || $champ === 'numero_utilisateur' || $champ === $modelProfil->getClePrimaire()) {
                continue;
            }
            $valeurTraitee = ($valeur === '') ? null : $valeur;
            $champsAMettreAJourProfil[$champ] = $valeurTraitee;

            if ($champ === $emailProfilChampNom) {
                $nouvelEmailProfil = $valeurTraitee;
            }
        }

        if (empty($champsAMettreAJourProfil) && ($nouvelEmailProfil === null || $nouvelEmailProfil === $userBase['email_principal'])) {
            return true;
        }

        $this->db->beginTransaction();
        try {
            if(!empty($champsAMettreAJourProfil)) {
                if (method_exists($modelProfil, 'mettreAJourParIdentifiantComposite')) {
                    $modelProfil->mettreAJourParIdentifiantComposite(['numero_utilisateur' => $numeroUtilisateur], $champsAMettreAJourProfil);
                } else {
                    $modelProfil->mettreAJourParCritere(['numero_utilisateur' => $numeroUtilisateur], $champsAMettreAJourProfil);
                }
            }

            if ($nouvelEmailProfil !== null && $nouvelEmailProfil !== $userBase['email_principal']) {
                if (!filter_var($nouvelEmailProfil, FILTER_VALIDATE_EMAIL)) {
                    $this->db->rollBack();
                    throw new EmailNonValideException("Le nouvel email de profil '$nouvelEmailProfil' est invalide.");
                }
                $existingUserWithEmail = $this->utilisateurModel->trouverParEmailPrincipal($nouvelEmailProfil);
                if ($existingUserWithEmail && $existingUserWithEmail['numero_utilisateur'] !== $numeroUtilisateur) {
                    $this->db->rollBack();
                    throw new EmailNonValideException("Ce nouvel email '$nouvelEmailProfil' est déjà utilisé par un autre compte.");
                }
                $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, [
                    'email_principal' => $nouvelEmailProfil,
                    'email_valide' => false,
                    'token_validation_email' => null
                ]);
                $tokenData = $this->genererEtStockerTokenPourUtilisateur($numeroUtilisateur, 'token_validation_email');
                $this->envoyerEmailValidationCompte($numeroUtilisateur, $nouvelEmailProfil, $tokenData['token_clair']);
                $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'MAJ_PROFIL_NOUVEL_EMAIL_VALIDATION_REQUISE', 'INFO', ['nouvel_email' => $nouvelEmailProfil]);
            }
            $this->db->commit();
            $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'MAJ_PROFIL_UTILISATEUR', 'SUCCES', ['type_profil' => $typeProfilLibelle]);
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            $this->journaliserActionAuthentification(null, $numeroUtilisateur, 'MAJ_PROFIL_UTILISATEUR_ECHEC_DB', 'ECHEC', ['erreur' => $e->getMessage()]);
            throw new OperationImpossibleException("Erreur de base de données lors de la mise à jour du profil: " . $e->getMessage(), (int)$e->getCode(), $e);
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function mettreAJourCompteUtilisateurParAdmin(string $numeroUtilisateur, array $donneesCompte): bool
    {
        $userBase = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['numero_utilisateur', 'id_type_utilisateur', 'email_principal']);
        if (!$userBase) {
            throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé.");
        }

        $champsAMettreAJour = [];
        $logDetails = [];

        $champsModifiablesUtilisateur = ['login_utilisateur', 'id_groupe_utilisateur', 'photo_profil', 'statut_compte', 'id_niveau_acces_donne', 'email_principal', 'preferences_2fa_active'];

        foreach ($donneesCompte as $champ => $valeur) {
            if (in_array($champ, $champsModifiablesUtilisateur)) {
                if ($champ === 'preferences_2fa_active') {
                    $valeur = filter_var($valeur, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                }
                $champsAMettreAJour[$champ] = $valeur;
                $logDetails[$champ] = $valeur;
            }
            if ($champ === 'id_type_utilisateur' && $valeur != $userBase['id_type_utilisateur']) {
                throw new OperationImpossibleException("Le changement de type d'utilisateur n'est pas supporté par cette méthode. Veuillez utiliser une procédure de migration dédiée.");
            }
        }

        if (isset($champsAMettreAJour['email_principal']) && $champsAMettreAJour['email_principal'] !== $userBase['email_principal']) {
            if (!filter_var($champsAMettreAJour['email_principal'], FILTER_VALIDATE_EMAIL)) {
                throw new EmailNonValideException("Le nouvel email principal '{$champsAMettreAJour['email_principal']}' est invalide.");
            }
            $existingUserWithEmail = $this->utilisateurModel->trouverParEmailPrincipal($champsAMettreAJour['email_principal']);
            if ($existingUserWithEmail && $existingUserWithEmail['numero_utilisateur'] !== $numeroUtilisateur) {
                throw new EmailNonValideException("Ce nouvel email principal '{$champsAMettreAJour['email_principal']}' est déjà utilisé.");
            }
            $champsAMettreAJour['email_valide'] = false;
            $champsAMettreAJour['token_validation_email'] = null;
            $logDetails['email_valide'] = false;
        }


        if (empty($champsAMettreAJour)) {
            return true;
        }

        try {
            $success = $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, $champsAMettreAJour);
            if ($success) {
                $this->journaliserActionAuthentification('ADMIN', $numeroUtilisateur, 'MAJ_COMPTE_UTILISATEUR_PAR_ADMIN', 'SUCCES', ['donnees_modifiees' => $logDetails]);
                if (isset($logDetails['email_principal']) && ($champsAMettreAJour['email_valide'] ?? true) === false) {
                    $tokenData = $this->genererEtStockerTokenPourUtilisateur($numeroUtilisateur, 'token_validation_email');
                    $this->envoyerEmailValidationCompte($numeroUtilisateur, $logDetails['email_principal'], $tokenData['token_clair']);
                }
            }
            return $success;
        } catch (PDOException $e) {
            if ((int)$e->getCode() === 23000 ) {
                if (stripos($e->getMessage(), 'login_utilisateur') !== false) {
                    throw new ValidationException("Ce login utilisateur est déjà utilisé.", [], (int)$e->getCode(), $e);
                }
                if (stripos($e->getMessage(), 'email_principal') !== false) {
                    throw new EmailNonValideException("Cet email principal est déjà utilisé.", (int)$e->getCode(), $e);
                }
            }
            $this->journaliserActionAuthentification('ADMIN', $numeroUtilisateur, 'MAJ_COMPTE_UTILISATEUR_PAR_ADMIN_ECHEC_DB', 'ECHEC', ['erreur' => $e->getMessage()]);
            throw new OperationImpossibleException("Erreur de base de données lors de la mise à jour du compte: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function changerStatutDuCompte(string $numeroUtilisateur, string $nouveauStatut, ?string $raison = null): bool
    {
        $statutsValides = ['actif', 'inactif', 'bloque', 'en_attente_validation', 'archive'];
        if (!in_array($nouveauStatut, $statutsValides)) {
            throw new ValidationException("Statut de compte invalide fourni: '$nouveauStatut'.");
        }
        $user = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['numero_utilisateur', 'statut_compte']);
        if (!$user) {
            throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé pour changement de statut.");
        }

        $champsAMettreAJour = ['statut_compte' => $nouveauStatut];
        if ($nouveauStatut === 'actif' && ($user['statut_compte'] === 'bloque' || $user['statut_compte'] === 'en_attente_validation')) {
            $champsAMettreAJour['tentatives_connexion_echouees'] = 0;
            $champsAMettreAJour['compte_bloque_jusqua'] = null;
        } elseif ($nouveauStatut === 'bloque' && $user['statut_compte'] !== 'bloque') {
            if(empty($raison) || stripos($raison, 'automatique') === false){
                $champsAMettreAJour['compte_bloque_jusqua'] = null;
            }
        }

        $success = $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, $champsAMettreAJour);
        if ($success) {
            $this->journaliserActionAuthentification(null, $numeroUtilisateur, 'CHANGEMENT_STATUT_COMPTE', 'SUCCES', ['nouveau_statut' => $nouveauStatut, 'ancien_statut' => $user['statut_compte'], 'raison' => $raison]);
        }
        return $success;
    }

    public function verifierRobustesseMotDePasse(string $motDePasse): array
    {
        $erreurs = [];
        $messages = [];
        if (strlen($motDePasse) < self::PASSWORD_MIN_LENGTH) {
            $erreurs[] = 'longueur_minimale';
            $messages[] = 'Longueur minimale de ' . self::PASSWORD_MIN_LENGTH . ' caractères non atteinte.';
        }
        if (self::PASSWORD_REQ_UPPERCASE && !preg_match('/[A-Z]/u', $motDePasse)) {
            $erreurs[] = 'manque_majuscule';
            $messages[] = 'Doit contenir au moins une lettre majuscule.';
        }
        if (self::PASSWORD_REQ_LOWERCASE && !preg_match('/[a-z]/u', $motDePasse)) {
            $erreurs[] = 'manque_minuscule';
            $messages[] = 'Doit contenir au moins une lettre minuscule.';
        }
        if (self::PASSWORD_REQ_NUMBER && !preg_match('/[0-9]/u', $motDePasse)) {
            $erreurs[] = 'manque_chiffre';
            $messages[] = 'Doit contenir au moins un chiffre.';
        }
        if (self::PASSWORD_REQ_SPECIAL && !preg_match('/[\W_]/u', $motDePasse)) {
            $erreurs[] = 'manque_special';
            $messages[] = 'Doit contenir au moins un caractère spécial.';
        }
        return ['valide' => empty($erreurs), 'codes_erreur' => $erreurs, 'messages_erreur' => $messages];
    }

    public function demanderReinitialisationMotDePasse(string $emailPrincipal): bool
    {
        $user = $this->utilisateurModel->trouverParEmailPrincipal($emailPrincipal, ['numero_utilisateur', 'statut_compte', 'email_valide', 'login_utilisateur']);
        if (!$user) {
            $this->journaliserActionAuthentification(null, $emailPrincipal, 'DEMANDE_RESET_MDP_EMAIL_INCONNU', 'INFO');
            return true;
        }
        if ($user['statut_compte'] !== 'actif' || !($user['email_valide'] == 1 || $user['email_valide'] === true)) {
            $this->journaliserActionAuthentification(null, $user['numero_utilisateur'], 'DEMANDE_RESET_MDP_COMPTE_INVALIDE', 'ECHEC', ['statut' => $user['statut_compte'], 'email_valide' => $user['email_valide']]);
            return true;
        }

        $tokenData = $this->genererEtStockerTokenPourUtilisateur($user['numero_utilisateur'], 'token_reset_mdp');
        $tokenClair = $tokenData['token_clair'];

        $sujet = "Réinitialisation de votre mot de passe - " . (getenv('APP_NAME') ?: 'GestionMySoutenance');
        $urlReset = rtrim(getenv('APP_URL') ?: ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'), '/') . '/reset-password?token=' . urlencode($tokenClair);
        $corps = "Bonjour " . htmlspecialchars($user['login_utilisateur'] ?: 'Utilisateur') . ",\n\nPour réinitialiser votre mot de passe, veuillez cliquer sur le lien suivant : " . $urlReset . "\n\nCe lien expirera dans " . self::PASSWORD_RESET_TOKEN_EXPIRY_HOURS . " heure(s).\nSi vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet email.\n\nCordialement,\nL'équipe " . (getenv('APP_NAME') ?: 'GestionMySoutenance');

        try {
            $this->serviceEmail->envoyerEmail($emailPrincipal, $sujet, $corps);
            $this->journaliserActionAuthentification(null, $user['numero_utilisateur'], 'DEMANDE_RESET_MDP_EMAIL_ENVOYE', 'SUCCES');
            return true;
        } catch (\Exception $e) {
            $this->journaliserActionAuthentification(null, $user['numero_utilisateur'], 'DEMANDE_RESET_MDP_EMAIL_ENVOI_ECHEC', 'ECHEC', ['erreur' => $e->getMessage()]);
            error_log("Échec de l'envoi de l'email de réinitialisation pour {$user['numero_utilisateur']}: " . $e->getMessage());
            return true;
        }
    }

    public function validerTokenReinitialisationMotDePasse(string $token): string
    {
        if (empty($token)) {
            throw new TokenInvalideException("Token de réinitialisation manquant.");
        }
        $tokenHache = hash('sha256', $token);
        $user = $this->utilisateurModel->trouverUnParCritere(['token_reset_mdp' => $tokenHache], ['numero_utilisateur', 'date_expiration_token_reset', 'statut_compte']);

        if (!$user) {
            throw new TokenInvalideException("Token de réinitialisation invalide ou déjà utilisé.");
        }
        if ($user['statut_compte'] !== 'actif') {
            $this->utilisateurModel->mettreAJourChamps($user['numero_utilisateur'], ['token_reset_mdp' => null, 'date_expiration_token_reset' => null]);
            throw new CompteNonValideException("Le compte associé à ce token n'est plus actif.");
        }
        if ($user['date_expiration_token_reset']) {
            try {
                $dateExpiration = new DateTimeImmutable($user['date_expiration_token_reset']);
                if (new DateTimeImmutable() > $dateExpiration) {
                    $this->utilisateurModel->mettreAJourChamps($user['numero_utilisateur'], ['token_reset_mdp' => null, 'date_expiration_token_reset' => null]);
                    throw new TokenExpireException("Le token de réinitialisation a expiré.");
                }
            } catch (\Exception $e) {
                $this->utilisateurModel->mettreAJourChamps($user['numero_utilisateur'], ['token_reset_mdp' => null, 'date_expiration_token_reset' => null]);
                error_log("Erreur de date pour date_expiration_token_reset pour {$user['numero_utilisateur']}: " . $e->getMessage());
                throw new TokenInvalideException("Erreur avec la date d'expiration du token.");
            }
        } else {
            $this->utilisateurModel->mettreAJourChamps($user['numero_utilisateur'], ['token_reset_mdp' => null]);
            throw new TokenInvalideException("Token de réinitialisation invalide (date d'expiration non définie).");
        }
        return $user['numero_utilisateur'];
    }

    public function reinitialiserMotDePasseApresValidationToken(string $token, string $nouveauMotDePasseClair): bool
    {
        $numeroUtilisateur = $this->validerTokenReinitialisationMotDePasse($token);

        $success = $this->modifierMotDePasse($numeroUtilisateur, $nouveauMotDePasseClair, null, true);

        if ($success) {
            $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['token_reset_mdp' => null, 'date_expiration_token_reset' => null]);
            $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'RESET_MDP_VIA_TOKEN_REUSSI', 'SUCCES');
        } else {
            $this->journaliserActionAuthentification(null, $numeroUtilisateur, 'RESET_MDP_VIA_TOKEN_ECHEC_MODIF_MDP', 'ECHEC');
        }
        return $success;
    }

    public function recupererEmailSourceDuProfil(string $numeroUtilisateur): ?string
    {
        $userBase = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['id_type_utilisateur']);
        if (!$userBase || !isset($userBase['id_type_utilisateur'])) {
            throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé ou type manquant.");
        }

        $idTypeUtilisateur = $userBase['id_type_utilisateur'];
        $tableProfil = $this->getTableProfilParIdType($idTypeUtilisateur);
        $champEmailProfil = $this->getChampEmailProfilParIdType($idTypeUtilisateur);

        if ($tableProfil && $champEmailProfil) {
            $modelProfil = $this->getModelPourTableProfil($tableProfil);
            $profilsData = $modelProfil->trouverParCritere(['numero_utilisateur' => $numeroUtilisateur], [$champEmailProfil]);
            if (!empty($profilsData) && isset($profilsData[0][$champEmailProfil])) {
                return $profilsData[0][$champEmailProfil];
            }
        }
        return null;
    }

    public function estNouveauMotDePasseDansHistorique(string $numeroUtilisateur, string $nouveauMotDePasseClair, int $limiteHistorique = 3): bool
    {
        if ($limiteHistorique <= 0) return false;
        $user = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['numero_utilisateur']);
        if (!$user) {
            return false;
        }

        $historiqueHaches = $this->historiqueMotDePasseModel->recupererHistoriquePourUtilisateur($numeroUtilisateur, $limiteHistorique);

        foreach ($historiqueHaches as $ancienHachageEnregistrement) {
            if (isset($ancienHachageEnregistrement['mot_de_passe_hache']) && password_verify($nouveauMotDePasseClair, $ancienHachageEnregistrement['mot_de_passe_hache'])) {
                return true;
            }
        }
        return false;
    }

    public function journaliserActionAuthentification(?string $numeroUtilisateurActeur, string $numeroUtilisateurConcerne, string $libelleAction, string $resultat, ?array $details = null): void
    {
        $acteurFinal = $numeroUtilisateurActeur;
        if ($acteurFinal === null) {
            if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['numero_utilisateur']) && !(isset($_SESSION['2fa_authentication_pending']) && $_SESSION['2fa_authentication_pending'] === true) ) {
                $acteurFinal = $_SESSION['numero_utilisateur'];
            } else {
                if (str_starts_with($libelleAction, 'TENTATIVE_CONNEXION') || str_starts_with($libelleAction, 'DEMANDE_RESET_MDP')) {
                    $acteurFinal = 'IP:' . ($_SERVER['REMOTE_ADDR'] ?? 'N/A');
                } else if ($libelleAction === 'DECONNEXION_SESSION' && $numeroUtilisateurConcerne !== 'ANONYME_DECONNEXION') {
                    $acteurFinal = $numeroUtilisateurConcerne;
                }
                else {
                    $acteurFinal = 'SYSTEME_OU_ANONYME';
                }
            }
        }

        $entiteId = $numeroUtilisateurConcerne;
        if (filter_var($numeroUtilisateurConcerne, FILTER_VALIDATE_EMAIL) && $libelleAction === 'DEMANDE_RESET_MDP_EMAIL_INCONNU') {
            $entiteId = 'non_applicable';
        }

        try {
            if (!$this->serviceSupervision) {
                error_log("ServiceSupervision non initialisé lors de la tentative de journalisation de l'action: $libelleAction");
                return;
            }
            $idActionSysteme = $this->serviceSupervision->recupererOuCreerIdActionParLibelle($libelleAction, 'AUTHENTIFICATION');

            $this->serviceSupervision->enregistrerAction(
                $acteurFinal,
                $idActionSysteme,
                $_SERVER['REMOTE_ADDR'] ?? 'N/A',
                $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
                'utilisateur',
                $entiteId,
                array_merge($details ?? [], ['resultat_svc_auth' => $resultat])
            );
        } catch (\Exception $e) {
            error_log("Erreur lors de la journalisation de l'action d'authentification ($libelleAction pour $numeroUtilisateurConcerne par $acteurFinal): " . $e->getMessage());
        }
    }

    private function mettreAJourDerniereConnexion(string $numeroUtilisateur): void
    {
        try {
            $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['derniere_connexion' => (new DateTimeImmutable())->format('Y-m-d H:i:s')]);
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de la dernière connexion pour $numeroUtilisateur: " . $e->getMessage());
        }
    }

    private function getIdTypeUtilisateurParLibelle(string $libelleTypeUtilisateur): ?string
    {
        try {
            $stmt = $this->db->prepare("SELECT id_type_utilisateur FROM type_utilisateur WHERE libelle_type_utilisateur = :libelle LIMIT 1");
            $stmt->bindParam(':libelle', $libelleTypeUtilisateur);
            $stmt->execute();
            $result = $stmt->fetchColumn();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erreur SQL dans getIdTypeUtilisateurParLibelle pour '$libelleTypeUtilisateur': " . $e->getMessage());
            return null;
        }
    }

    private function getIdGroupeUtilisateurParLibelle(string $libelleGroupeUtilisateur): ?string
    {
        try {
            $stmt = $this->db->prepare("SELECT id_groupe_utilisateur FROM groupe_utilisateur WHERE libelle_groupe_utilisateur = :libelle LIMIT 1");
            $stmt->bindParam(':libelle', $libelleGroupeUtilisateur);
            $stmt->execute();
            $result = $stmt->fetchColumn();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erreur SQL dans getIdGroupeUtilisateurParLibelle pour '$libelleGroupeUtilisateur': " . $e->getMessage());
            return null;
        }
    }

    private function getDefaultGroupIdForTypeLibelle(string $typeProfilLibelle): ?string
    {
        $mapTypeToGroupeLibelle = [
            'Etudiant' => 'Etudiants',
            'Enseignant' => 'Enseignants',
            'Personnel Administratif' => 'Personnel_Admin',
            'Administrateur' => 'Administrateur_systeme'
        ];
        $libelleGroupe = $mapTypeToGroupeLibelle[$typeProfilLibelle] ?? 'GRP_UTILISATEUR_STANDARD';

        $idGroupe = $this->getIdGroupeUtilisateurParLibelle($libelleGroupe);
        if ($idGroupe === null && $libelleGroupe === 'GRP_UTILISATEUR_STANDARD') {
            $idGroupe = $this->getIdGroupeUtilisateurParLibelle('Standard') ?? $this->getIdGroupeUtilisateurParLibelle('Défaut');
        }
        if ($idGroupe === null) {
            throw new OperationImpossibleException("Impossible de déterminer le groupe par défaut pour le type '$typeProfilLibelle' (libellé de groupe testé: '$libelleGroupe'). Veuillez configurer les groupes.");
        }
        return $idGroupe;
    }

    private function getDefaultNiveauAccesId(): string
    {
        return 'ACCES_STANDARD';
    }

    private function getTableProfilParIdType(?string $idTypeUtilisateur): ?string
    {
        if ($idTypeUtilisateur === null) return null;
        $stmt = $this->db->prepare("SELECT libelle_type_utilisateur FROM type_utilisateur WHERE id_type_utilisateur = :id LIMIT 1");
        $stmt->bindParam(':id', $idTypeUtilisateur);
        $stmt->execute();
        $libelle = $stmt->fetchColumn();

        if (!$libelle) return null;

        switch (strtolower($libelle)) {
            case 'etudiant': return 'etudiant';
            case 'enseignant': return 'enseignant';
            case 'personnel administratif': return 'personnel_administratif';
            case 'administrateur': return null;
            default:
                error_log("Aucune table de profil définie pour le type d'utilisateur ID: $idTypeUtilisateur ($libelle)");
                return null;
        }
    }

    private function getChampEmailProfilParIdType(?string $idTypeUtilisateur): ?string
    {
        if ($idTypeUtilisateur === null) return null;
        $stmt = $this->db->prepare("SELECT libelle_type_utilisateur FROM type_utilisateur WHERE id_type_utilisateur = :id LIMIT 1");
        $stmt->bindParam(':id', $idTypeUtilisateur);
        $stmt->execute();
        $libelle = $stmt->fetchColumn();

        if (!$libelle) return null;

        switch (strtolower($libelle)) {
            case 'etudiant': return 'email_contact_secondaire';
            case 'enseignant': return 'email_professionnel';
            case 'personnel administratif': return 'email_professionnel';
            default: return null;
        }
    }

    private function validerDonneesCreationCompteGlobale(array $donneesUtilisateur, array $donneesProfil, string $typeProfilLibelle): void
    {
        if (empty($donneesUtilisateur['login_utilisateur']) || empty($donneesUtilisateur['mot_de_passe'])) {
            throw new ValidationException("Login et mot de passe sont requis pour la création du compte.", ['login_utilisateur' => 'Requis', 'mot_de_passe' => 'Requis']);
        }
        $robustesse = $this->verifierRobustesseMotDePasse($donneesUtilisateur['mot_de_passe']);
        if (!$robustesse['valide']) {
            throw new ValidationException("Le mot de passe fourni n'est pas assez robuste: " . implode(' ', $robustesse['messages_erreur']), $robustesse['codes_erreur'] ?? []);
        }

        $emailProfil = $this->extraireEmailDuProfilConcret($donneesProfil, $typeProfilLibelle);
        if (empty($emailProfil) || !filter_var($emailProfil, FILTER_VALIDATE_EMAIL)) {
            $champEmailProfil = $this->getChampEmailProfilParIdType($this->getIdTypeUtilisateurParLibelle($typeProfilLibelle)) ?: 'email_profil';
            throw new ValidationException("L'email du profil ($champEmailProfil) est requis et doit être un email valide.", [$champEmailProfil => "Requis et doit être un email valide."]);
        }
    }

    private function extraireEmailDuProfilConcret(array $donneesProfil, string $typeProfilLibelle): ?string
    {
        $idType = $this->getIdTypeUtilisateurParLibelle($typeProfilLibelle);
        if (!$idType) return null;
        $champEmail = $this->getChampEmailProfilParIdType($idType);
        if(!$champEmail) return null;
        return $donneesProfil[$champEmail] ?? null;
    }

    private function creerProfilSpecifiqueAssocie(string $numeroUtilisateur, array $donneesProfil, string $idTypeUtilisateur): void
    {
        $tableProfil = $this->getTableProfilParIdType($idTypeUtilisateur);
        if (!$tableProfil) {
            return;
        }

        $modelProfil = $this->getModelPourTableProfil($tableProfil);
        $donneesProfilPourTable = ['numero_utilisateur' => $numeroUtilisateur];
        $colonnesAttendues = $this->getColonnesAttenduesPourTableProfil($tableProfil);

        foreach ($colonnesAttendues as $colonneSpec) {
            if ($colonneSpec === 'numero_utilisateur') continue;

            $valeurFournie = $donneesProfil[$colonneSpec] ?? null;
            $infoColonne = $this->getInfosColonneTableProfil($tableProfil, $colonneSpec);

            $estNullable = (isset($infoColonne['Null']) && strtoupper($infoColonne['Null']) === 'YES');
            $aValeurParDefaut = (isset($infoColonne['Default']));
            $estClePrimaireProfil = ($infoColonne['Key'] ?? '') === 'PRI';

            if (!$estNullable && !$aValeurParDefaut && ($valeurFournie === null || $valeurFournie === '')) {
                if ($estClePrimaireProfil && $colonneSpec !== 'numero_utilisateur') {
                    throw new ValidationException("L'identifiant métier '$colonneSpec' est requis pour le profil.");
                }
            }
            $donneesProfilPourTable[$colonneSpec] = ($valeurFournie === '') ? null : $valeurFournie;
        }
        $modelProfil->creer($donneesProfilPourTable);
    }

    private function getInfosColonneTableProfil(string $nomTable, string $nomColonne): ?array
    {
        try {
            $stmt = $this->db->prepare("SHOW COLUMNS FROM `$nomTable` LIKE :colonne");
            $stmt->bindParam(':colonne', $nomColonne);
            $stmt->execute();
            $info = $stmt->fetch(PDO::FETCH_ASSOC);
            return $info ?: null;
        } catch (PDOException $e) {
            error_log("Erreur lors de la description de la colonne $nomTable.$nomColonne: " . $e->getMessage());
            return null;
        }
    }

    private function getColonnesAttenduesPourTableProfil(string $tableProfil): array {
        try {
            $stmt = $this->db->query("DESCRIBE `$tableProfil`");
            $colonnes = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return $colonnes ?: [];
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des colonnes pour la table $tableProfil: " . $e->getMessage());
            return [];
        }
    }

    private function getModelPourTableProfil(string $tableProfil): BaseModel
    {
        switch ($tableProfil) {
            case 'etudiant':
                return $this->etudiantModel;
            case 'enseignant':
                return $this->enseignantModel;
            case 'personnel_administratif':
                return $this->personnelAdministratifModel;
            default:
                throw new OperationImpossibleException("Modèle de profil inconnu pour la table: '$tableProfil'.");
        }
    }

    private function ajouterMotDePasseHistorique(string $numeroUtilisateur, string $motDePasseHache): void
    {
        $dataToCreate = [
            'numero_utilisateur' => $numeroUtilisateur,
            'mot_de_passe_hache' => $motDePasseHache,
            'date_creation' => (new DateTimeImmutable())->format('Y-m-d H:i:s')
        ];
        // Si id_historique_mdp n'est PAS auto-incrémenté et est VARCHAR:
        // $idHistorique = $this->genererIdUniquePourTable('historique_mot_de_passe', 'HISTMDP_');
        // $dataToCreate['id_historique_mdp'] = $idHistorique;

        $this->historiqueMotDePasseModel->creer($dataToCreate);
    }

    private function nettoyerHistoriqueMotDePasse(string $numeroUtilisateur): void
    {
        $sql = "SELECT id_historique_mdp FROM historique_mot_de_passe 
                WHERE numero_utilisateur = :num_user 
                ORDER BY date_creation DESC"; // Les plus récents en premier
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':num_user', $numeroUtilisateur);
        $stmt->execute();
        $historiqueIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (count($historiqueIds) > self::PASSWORD_HISTORY_LIMIT) {
            $idsASupprimer = array_slice($historiqueIds, self::PASSWORD_HISTORY_LIMIT);
            if (!empty($idsASupprimer)) {
                if (method_exists($this->historiqueMotDePasseModel, 'supprimerPlusieursParIdentifiants')) {
                    $this->historiqueMotDePasseModel->supprimerPlusieursParIdentifiants($idsASupprimer);
                } else {
                    $placeholders = implode(',', array_fill(0, count($idsASupprimer), '?'));
                    $stmtDelete = $this->db->prepare("DELETE FROM historique_mot_de_passe WHERE id_historique_mdp IN ($placeholders)");
                    try {
                        $stmtDelete->execute($idsASupprimer);
                    } catch (PDOException $e) {
                        error_log("Erreur lors du nettoyage de l'historique des mots de passe pour $numeroUtilisateur: " . $e->getMessage());
                    }
                }
            }
        }
    }

    private function genererEtStockerTokenPourUtilisateur(string $numeroUtilisateur, string $nomChampToken): array
    {
        $tokenClair = bin2hex(random_bytes(self::TOKEN_LENGHT_BYTES));
        $tokenHache = hash('sha256', $tokenClair);
        $champsAMettreAJour = [];

        if ($nomChampToken === 'token_reset_mdp') {
            $dateExpiration = (new DateTimeImmutable())->add(new DateInterval('PT' . self::PASSWORD_RESET_TOKEN_EXPIRY_HOURS . 'H'));
            $champsAMettreAJour[$nomChampToken] = $tokenHache;
            $champsAMettreAJour['date_expiration_token_reset'] = $dateExpiration->format('Y-m-d H:i:s');
        } elseif ($nomChampToken === 'token_validation_email') {
            $champsAMettreAJour[$nomChampToken] = $tokenHache;
        } else {
            throw new OperationImpossibleException("Type de token inconnu : $nomChampToken");
        }

        $success = $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, $champsAMettreAJour);
        if (!$success) {
            throw new OperationImpossibleException("Impossible de stocker le token '$nomChampToken' pour l'utilisateur '$numeroUtilisateur'.");
        }
        return ['token_clair' => $tokenClair, 'token_hache' => $tokenHache];
    }

    private function genererIdUniquePourTable(string $table, string $prefix = '', int $longueurSuffixeHex = 10): string
    {
        $modelGenerique = new class($this->db) extends BaseModel {
            public function __construct(PDO $db) {
                parent::__construct($db);
            }
            public function configure(string $tableName, string $primaryKeyName) {
                $this->table = $tableName;
                $this->clePrimaire = $primaryKeyName;
            }
        };

        $pkName = '';
        if ($table === 'historique_mot_de_passe') $pkName = 'id_historique_mdp';
        else {
            throw new \InvalidArgumentException("Configuration de clé primaire manquante pour la table $table dans genererIdUniquePourTable.");
        }
        $modelGenerique->configure($table, $pkName);

        $longueurMaxPK = 50;
        $prefix = strtoupper($prefix);
        $maxTentativesGen = 10;
        $tentativeGen = 0;
        do {
            $suffixe = bin2hex(random_bytes(intval(ceil($longueurSuffixeHex / 2))));
            $idGenere = $prefix . $suffixe;
            if (strlen($idGenere) > $longueurMaxPK) {
                $idGenere = substr($idGenere, 0, $longueurMaxPK);
            }
            $tentativeGen++;
            if ($tentativeGen > $maxTentativesGen) {
                throw new OperationImpossibleException("Impossible de générer un ID unique pour la table $table après $maxTentativesGen tentatives.");
            }
        } while ($modelGenerique->trouverParIdentifiant($idGenere)); // trouverParIdentifiant utilise $this->clePrimaire
        return $idGenere;
    }
}
