<?php

namespace App\Backend\Service\Authentication;

use PDO;
use PDOException;
use DateTime;
use DateTimeImmutable;
use DateInterval;
use App\Config\Database;
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
use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;


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
            throw new CompteNonValideException("Ce compte n'est pas actif (statut: " . $utilisateurBase['statut_compte'] . "). Veuillez contacter le support.");
        }

        $preferences2FAActive = false;
        if (is_string($utilisateurBase['preferences_2fa_active'])) {
            $preferences2FAActive = ($utilisateurBase['preferences_2fa_active'] === '1');
        } elseif (is_int($utilisateurBase['preferences_2fa_active'])) {
            $preferences2FAActive = ($utilisateurBase['preferences_2fa_active'] === 1);
        } elseif (is_bool($utilisateurBase['preferences_2fa_active'])) {
            $preferences2FAActive = $utilisateurBase['preferences_2fa_active'];
        }


        if ($preferences2FAActive) {
            $_SESSION['2fa_user_num_pending_verification'] = $numeroUtilisateur;
            $_SESSION['2fa_authentication_pending'] = true;
            $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'CONNEXION_2FA_REQUISE', 'INFO');
            throw new AuthenticationException("Authentification à deux facteurs requise.", 1001);
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

    public function traiterTentativeConnexionEchoueePourUtilisateur(string $numeroUtilisateur): void
    {
        $this->utilisateurModel->commencerTransaction();
        try {
            $stmtInc = $this->db->prepare("UPDATE utilisateur SET tentatives_connexion_echouees = tentatives_connexion_echouees + 1 WHERE numero_utilisateur = :num_user FOR UPDATE");
            $stmtInc->bindParam(':num_user', $numeroUtilisateur);
            $stmtInc->execute();

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
            $this->utilisateurModel->validerTransaction();
        } catch (PDOException $e) {
            $this->utilisateurModel->annulerTransaction();
            throw $e;
        }
    }

    public function reinitialiserTentativesConnexion(string $numeroUtilisateur): void
    {
        $user = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['numero_utilisateur']);
        if (!$user) {
            throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé pour réinitialisation des tentatives.");
        }
        $success = $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, [
            'tentatives_connexion_echouees' => 0,
            'compte_bloque_jusqua' => null
        ]);
        if (!$success) {
            throw new OperationImpossibleException("Échec de la réinitialisation des tentatives pour l'utilisateur '$numeroUtilisateur'.");
        }
    }

    public function estCompteActuellementBloque(string $numeroUtilisateur): bool
    {
        $user = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['compte_bloque_jusqua', 'statut_compte']);
        if (!$user) {
            throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé pour vérification de blocage.");
        }

        if ($user['statut_compte'] === 'bloque' && $user['compte_bloque_jusqua']) {
            $dateBlocageFin = new DateTimeImmutable($user['compte_bloque_jusqua']);
            if (new DateTimeImmutable() < $dateBlocageFin) {
                return true;
            } else {
                $this->changerStatutDuCompte($numeroUtilisateur, 'actif', 'Déblocage automatique après expiration.');
                return false;
            }
        }
        return $user['statut_compte'] === 'bloque';
    }

    public function genererEtStockerSecret2FA(string $numeroUtilisateur): string
    {
        if (!extension_loaded('sodium')) {
            throw new \RuntimeException("L'extension Sodium est requise pour l'encodage Base32 du secret 2FA.");
        }
        $user = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['numero_utilisateur', 'email_principal', 'login_utilisateur']);
        if (!$user) {
            throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé pour génération secret 2FA.");
        }

        $secretBinary = $this->tfaProvider->createSecret(160, false);
        $secretBase32 = \Sodium\sodium_bin2base32($secretBinary, \Sodium\SODIUM_BASE32_VARIANT_RFC4648);

        $success = $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['secret_2fa' => $secretBase32]);
        if (!$success) {
            throw new OperationImpossibleException("Impossible de stocker le secret 2FA pour l'utilisateur '$numeroUtilisateur'.");
        }
        $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'GENERATION_SECRET_2FA', 'SUCCES');

        $label = $user['email_principal'] ?: $user['login_utilisateur'];
        return $this->tfaProvider->getQRCodeImageAsDataUri(rawurlencode(self::APP_NAME_FOR_2FA . ':' . $label), $secretBase32);
    }

    public function activerAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTPVerifie): bool
    {
        $user = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['numero_utilisateur', 'secret_2fa']);
        if (!$user) {
            throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé.");
        }
        if (empty($user['secret_2fa'])) {
            throw new OperationImpossibleException("Secret 2FA non configuré pour l'utilisateur '$numeroUtilisateur'. Impossible d'activer.");
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
        if (is_string($user['preferences_2fa_active'])) $pref2FA = ($user['preferences_2fa_active'] === '1');
        elseif (is_int($user['preferences_2fa_active'])) $pref2FA = ($user['preferences_2fa_active'] === 1);
        elseif (is_bool($user['preferences_2fa_active'])) $pref2FA = $user['preferences_2fa_active'];

        if (!$pref2FA || empty($user['secret_2fa'])) {
            $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'VERIFICATION_2FA_NON_ACTIVE_OU_SECRET_MANQUANT', 'ECHEC');
            throw new OperationImpossibleException("L'authentification à deux facteurs n'est pas active ou le secret n'est pas configuré pour cet utilisateur.");
        }

        $isValid = $this->tfaProvider->verifyCode($user['secret_2fa'], $codeTOTP, 2);
        if ($isValid) {
            unset($_SESSION['2fa_authentication_pending']);
            unset($_SESSION['2fa_user_num_pending_verification']);
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
        $_SESSION['login_utilisateur'] = $utilisateurAvecProfil->login_utilisateur;
        $_SESSION['id_type_utilisateur'] = $utilisateurAvecProfil->id_type_utilisateur;
        $_SESSION['id_groupe_utilisateur'] = $utilisateurAvecProfil->id_groupe_utilisateur;
        $_SESSION['user_complet'] = $utilisateurAvecProfil;
        $_SESSION['last_activity'] = time();
    }

    public function estUtilisateurConnecteEtSessionValide(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // ---- DEBUT DEBUG ----
        echo "<pre>Contenu de \$_SESSION au début de estUtilisateurConnecteEtSessionValide():<br>";
        var_dump($_SESSION);
        echo "</pre>";
        // ---- FIN DEBUG ----

        if (!isset($_SESSION['numero_utilisateur'])) {
            // ---- DEBUT DEBUG ----
            echo "<p>DEBUG: \$_SESSION['numero_utilisateur'] N'EST PAS DÉFINI. Retour false.</p>";
            // ---- FIN DEBUG ----
            return false;
        }

        $sessionTimeout = 3600; // 1 heure
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $sessionTimeout)) {
            // ---- DEBUT DEBUG ----
            echo "<p>DEBUG: Session expirée. Appel de terminerSessionUtilisateur().</p>";
            // ---- FIN DEBUG ----
            $this->terminerSessionUtilisateur();
            return false;
        }
        $_SESSION['last_activity'] = time();
        // ---- DEBUT DEBUG ----
        echo "<p>DEBUG: \$_SESSION['numero_utilisateur'] EST DÉFINI ET SESSION NON EXPIRÉE. Retour true.</p>";
        // ---- FIN DEBUG ----
        return true;
    }

    public function getUtilisateurConnecteComplet(): ?object
    {
        if ($this->estUtilisateurConnecteEtSessionValide() && isset($_SESSION['user_complet'])) {
            return $_SESSION['user_complet'];
        }
        return null;
    }

    public function terminerSessionUtilisateur(): void
    {
        $numeroUtilisateurJournal = 'ANONYME_DECONNEXION';
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
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
        $this->journaliserActionAuthentification(null, $numeroUtilisateurJournal, 'DECONNEXION_SESSION', 'SUCCES');
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
            $statutScolarite = $this->serviceGestionAcademique->verifierStatutScolariteEtudiant($donneesProfil['numero_carte_etudiant'], $donneesUtilisateur['id_annee_academique']);
            if (!$statutScolarite || !($statutScolarite['eligible_creation_compte'] ?? false)) {
                throw new OperationImpossibleException("L'étudiant (carte: {$donneesProfil['numero_carte_etudiant']}) n'est pas éligible à la création de compte selon son statut de scolarité actuel.");
            }
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
                'statut_compte' => 'en_attente_validation',
                'photo_profil' => $donneesUtilisateur['photo_profil'] ?? null,
                'id_niveau_acces_donne' => $donneesUtilisateur['id_niveau_acces_donne'] ?? $this->getDefaultNiveauAccesId()
            ];
            $this->utilisateurModel->creer($donneesBaseUtilisateur);

            $this->creerProfilSpecifiqueAssocie($numeroUtilisateur, $donneesProfil, $idTypeUtilisateur);
            $this->ajouterMotDePasseHistorique($numeroUtilisateur, $motDePasseHache);

            $tokenValidation = null;
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
            if ((int)$e->getCode() === 23000) { // Integrity constraint violation
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
        $urlValidation = rtrim(getenv('APP_URL') ?: ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'), '/') . '/validate-email?token=' . $tokenValidation;
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

        $user = $this->utilisateurModel->trouverUnParCritere(['token_validation_email' => $tokenHache, 'email_valide' => false], ['numero_utilisateur', 'statut_compte']);

        if (!$user) {
            throw new TokenInvalideException("Token de validation invalide, déjà utilisé, ou compte déjà validé.");
        }
        // Pas de vérification d'expiration de token ici comme convenu

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
        $profil = null;
        $tableProfil = $this->getTableProfilParIdType($utilisateurBase['id_type_utilisateur']);
        $modelProfil = null;

        if ($tableProfil) {
            $modelProfil = $this->getModelPourTableProfil($tableProfil);
            $profilData = $modelProfil->trouverParCritere(['numero_utilisateur' => $utilisateurBase['numero_utilisateur']]);
            if (!empty($profilData)) {
                $profil = $profilData[0];
            }
        }

        $typeUtilisateur = $this->recupererLibelleTableRef('type_utilisateur', 'id_type_utilisateur', $utilisateurBase['id_type_utilisateur'], 'libelle_type_utilisateur');
        $groupeUtilisateur = $this->recupererLibelleTableRef('groupe_utilisateur', 'id_groupe_utilisateur', $utilisateurBase['id_groupe_utilisateur'], 'libelle_groupe_utilisateur');
        $niveauAcces = $this->recupererLibelleTableRef('niveau_acces_donne', 'id_niveau_acces_donne', $utilisateurBase['id_niveau_acces_donne'], 'libelle_niveau_acces_donne');

        $merged = array_merge($utilisateurBase, $profil ?: []);
        $merged['libelle_type_utilisateur'] = $typeUtilisateur;
        $merged['libelle_groupe_utilisateur'] = $groupeUtilisateur;
        $merged['libelle_niveau_acces_donne'] = $niveauAcces;

        return (object) $merged;
    }

    private function recupererLibelleTableRef(string $table, string $colonneId, string $valeurId, string $colonneLibelle): ?string
    {
        $stmt = $this->db->prepare("SELECT $colonneLibelle FROM `$table` WHERE $colonneId = :id LIMIT 1");
        $stmt->bindParam(':id', $valeurId);
        $stmt->execute();
        return $stmt->fetchColumn() ?: null;
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
            $joins[] = "LEFT JOIN etudiant et ON u.numero_utilisateur = et.numero_utilisateur AND u.id_type_utilisateur = '$idTypeEtudiant'";
        }
        if ($idTypeEnseignant) {
            $joins[] = "LEFT JOIN enseignant en ON u.numero_utilisateur = en.numero_utilisateur AND u.id_type_utilisateur = '$idTypeEnseignant'";
        }
        if ($idTypePersonnelAdmin) {
            $joins[] = "LEFT JOIN personnel_administratif pa ON u.numero_utilisateur = pa.numero_utilisateur AND u.id_type_utilisateur = '$idTypePersonnelAdmin'";
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
            if ($idTypeEtudiant) { // Only add if etudiant type exists
                $searchConditions[] = "et.numero_carte_etudiant LIKE :recherche";
            }
            $whereClauses[] = "(" . implode(" OR ", $searchConditions) . ")";
            $params[':recherche'] = $searchTerm;
        }

        $sqlWhere = "";
        if (!empty($whereClauses)) {
            $sqlWhere = " WHERE " . implode(" AND ", $whereClauses);
        }

        $sqlCount = "SELECT COUNT(DISTINCT u.numero_utilisateur) FROM " . $fromClause . implode(" ", $joins) . $sqlWhere;
        $stmtCount = $this->db->prepare($sqlCount);
        $stmtCount->execute($params);
        $totalElements = (int)$stmtCount->fetchColumn();

        $sqlQuery = "SELECT " . implode(", ", $selectFields) . " FROM " . $fromClause . implode(" ", $joins) . $sqlWhere . " ORDER BY u.date_creation DESC LIMIT :limit OFFSET :offset";
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
            $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['mot_de_passe' => $nouveauMotDePasseHache]);
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
            throw new OperationImpossibleException("Type de profil inconnu pour la mise à jour de l'utilisateur '$numeroUtilisateur'.");
        }

        $modelProfil = $this->getModelPourTableProfil($tableProfil);
        $champsAMettreAJourProfil = [];
        $nouvelEmailProfil = null;
        $emailProfilChampNom = $this->getChampEmailProfilParIdType($userBase['id_type_utilisateur']);

        foreach ($donneesProfil as $champ => $valeur) {
            if ($champ === 'numero_utilisateur' || $champ === $modelProfil->getClePrimaire()) continue;

            $infoColonne = $this->getInfosColonneTableProfil($tableProfil, $champ);
            if(!$infoColonne) continue;

            $valeurTraitee = ($valeur === '') ? null : $valeur;
            $champsAMettreAJourProfil[$champ] = $valeurTraitee;

            if ($champ === $emailProfilChampNom) {
                $nouvelEmailProfil = $valeurTraitee;
            }
        }

        if (empty($champsAMettreAJourProfil)) {
            return true;
        }

        $this->db->beginTransaction();
        try {
            $modelProfil->mettreAJourParIdentifiantComposite(['numero_utilisateur' => $numeroUtilisateur], $champsAMettreAJourProfil);

            if ($nouvelEmailProfil !== null && $nouvelEmailProfil !== $userBase['email_principal']) {
                if (!filter_var($nouvelEmailProfil, FILTER_VALIDATE_EMAIL)) {
                    $this->db->rollBack();
                    throw new EmailNonValideException("Le nouvel email de profil '$nouvelEmailProfil' est invalide.");
                }
                if ($this->utilisateurModel->trouverParEmailPrincipal($nouvelEmailProfil, ['numero_utilisateur'], $numeroUtilisateur)) {
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

        $champsModifiablesUtilisateur = ['login_utilisateur', 'id_groupe_utilisateur', 'photo_profil', 'statut_compte', 'id_niveau_acces_donne', 'email_principal'];

        foreach ($donneesCompte as $champ => $valeur) {
            if (in_array($champ, $champsModifiablesUtilisateur)) {
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
            if ($this->utilisateurModel->trouverParEmailPrincipal($champsAMettreAJour['email_principal'], ['numero_utilisateur'], $numeroUtilisateur)) {
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
            throw new UtilisateurNonTrouveException("Aucun compte n'est associé à cet email principal.");
        }
        if ($user['statut_compte'] !== 'actif' || !($user['email_valide'] == 1 || $user['email_valide'] === true)) {
            $this->journaliserActionAuthentification(null, $user['numero_utilisateur'], 'DEMANDE_RESET_MDP_COMPTE_INVALIDE', 'ECHEC', ['statut' => $user['statut_compte'], 'email_valide' => $user['email_valide']]);
            throw new CompteNonValideException("Le compte associé à cet email n'est pas actif ou l'email n'est pas validé.");
        }

        $tokenData = $this->genererEtStockerTokenPourUtilisateur($user['numero_utilisateur'], 'token_reset_mdp');
        $tokenClair = $tokenData['token_clair'];

        $sujet = "Réinitialisation de votre mot de passe - GestionMySoutenance";
        $urlReset = rtrim(getenv('APP_URL') ?: ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'), '/') . '/reset-password?token=' . $tokenClair;
        $corps = "Bonjour " . htmlspecialchars($user['login_utilisateur']) . ",\n\nPour réinitialiser votre mot de passe, veuillez cliquer sur le lien suivant : " . $urlReset . "\n\nCe lien expirera dans " . self::PASSWORD_RESET_TOKEN_EXPIRY_HOURS . " heure(s).\nSi vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet email.\n\nCordialement,\nL'équipe GestionMySoutenance";

        try {
            $this->serviceEmail->envoyerEmail($emailPrincipal, $sujet, $corps);
            $this->journaliserActionAuthentification(null, $user['numero_utilisateur'], 'DEMANDE_RESET_MDP_EMAIL_ENVOYE', 'SUCCES');
            return true;
        } catch (\Exception $e) {
            $this->journaliserActionAuthentification(null, $user['numero_utilisateur'], 'DEMANDE_RESET_MDP_EMAIL_ENVOI_ECHEC', 'ECHEC', ['erreur' => $e->getMessage()]);
            throw new OperationImpossibleException("Erreur lors de l'envoi de l'email de réinitialisation: " . $e->getMessage(), 0, $e);
        }
    }

    public function validerTokenReinitialisationMotDePasse(string $token): string
    {
        if (empty($token)) {
            throw new TokenInvalideException("Token de réinitialisation manquant.");
        }
        $tokenHache = hash('sha256', $token);
        $user = $this->utilisateurModel->trouverUnParCritere(['token_reset_mdp' => $tokenHache], ['numero_utilisateur', 'date_expiration_token_reset']);

        if (!$user) {
            throw new TokenInvalideException("Token de réinitialisation invalide ou déjà utilisé.");
        }
        if ($user['date_expiration_token_reset']) {
            $dateExpiration = new DateTimeImmutable($user['date_expiration_token_reset']);
            if (new DateTimeImmutable() > $dateExpiration) {
                $this->utilisateurModel->mettreAJourChamps($user['numero_utilisateur'], ['token_reset_mdp' => null, 'date_expiration_token_reset' => null]);
                throw new TokenExpireException("Le token de réinitialisation a expiré.");
            }
        } else {
            throw new TokenInvalideException("Token de réinitialisation invalide (pas de date d'expiration définie).");
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
        if (!$userBase) {
            throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé.");
        }

        $idTypeUtilisateur = $userBase['id_type_utilisateur'];
        $tableProfil = $this->getTableProfilParIdType($idTypeUtilisateur);
        $champEmailProfil = $this->getChampEmailProfilParIdType($idTypeUtilisateur);

        if ($tableProfil && $champEmailProfil) {
            $modelProfil = $this->getModelPourTableProfil($tableProfil);
            $profil = $modelProfil->trouverUnParCritere(['numero_utilisateur' => $numeroUtilisateur], [$champEmailProfil]);
            return $profil[$champEmailProfil] ?? null;
        }
        return null;
    }

    public function estNouveauMotDePasseDansHistorique(string $numeroUtilisateur, string $nouveauMotDePasseClair, int $limiteHistorique = 3): bool
    {
        if ($limiteHistorique <= 0) return false;
        $user = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['numero_utilisateur']);
        if (!$user) {
            throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé pour vérification de l'historique des mots de passe.");
        }

        $historiqueHaches = $this->historiqueMotDePasseModel->recupererHistoriquePourUtilisateur($numeroUtilisateur, $limiteHistorique);

        foreach ($historiqueHaches as $ancienHachageEnregistrement) {
            if (password_verify($nouveauMotDePasseClair, $ancienHachageEnregistrement['mot_de_passe_hache'])) {
                return true;
            }
        }
        return false;
    }

    public function journaliserActionAuthentification(?string $numeroUtilisateurActeur, string $numeroUtilisateurConcerne, string $libelleAction, string $resultat, ?array $details = null): void
    {
        if ($numeroUtilisateurActeur === null) {
            if (isset($_SESSION['numero_utilisateur'])) {
                $numeroUtilisateurActeur = $_SESSION['numero_utilisateur'];
            } else {
                // Si l'action concerne une tentative de connexion ou un reset avant que l'utilisateur soit en session
                if (str_starts_with($libelleAction, 'TENTATIVE_CONNEXION') || str_starts_with($libelleAction, 'DEMANDE_RESET_MDP')) {
                    $numeroUtilisateurActeur = 'IP:' . ($_SERVER['REMOTE_ADDR'] ?? 'N/A');
                } else {
                    $numeroUtilisateurActeur = 'SYSTEME_OU_ANONYME';
                }
            }
        }

        $idActionSysteme = $this->serviceSupervision->recupererOuCreerIdActionParLibelle($libelleAction, 'AUTHENTIFICATION');

        $this->serviceSupervision->enregistrerAction(
            $numeroUtilisateurActeur,
            $idActionSysteme,
            $_SERVER['REMOTE_ADDR'] ?? 'N/A',
            $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
            'utilisateur',
            $numeroUtilisateurConcerne,
            array_merge($details ?? [], ['resultat_svc_auth' => $resultat])
        );
    }

    private function mettreAJourDerniereConnexion(string $numeroUtilisateur): void
    {
        $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['derniere_connexion' => (new DateTimeImmutable())->format('Y-m-d H:i:s')]);
    }

    private function getIdTypeUtilisateurParLibelle(string $libelleTypeUtilisateur): ?string
    {
        $stmt = $this->db->prepare("SELECT id_type_utilisateur FROM type_utilisateur WHERE libelle_type_utilisateur = :libelle LIMIT 1");
        $stmt->bindParam(':libelle', $libelleTypeUtilisateur);
        $stmt->execute();
        $result = $stmt->fetchColumn();
        if(!$result) throw new UtilisateurNonTrouveException("Type utilisateur avec libellé '$libelleTypeUtilisateur' non trouvé.");
        return $result;
    }

    private function getIdGroupeUtilisateurParLibelle(string $libelleGroupeUtilisateur): ?string
    {
        $stmt = $this->db->prepare("SELECT id_groupe_utilisateur FROM groupe_utilisateur WHERE libelle_groupe_utilisateur = :libelle LIMIT 1");
        $stmt->bindParam(':libelle', $libelleGroupeUtilisateur);
        $stmt->execute();
        $result = $stmt->fetchColumn();
        if(!$result) throw new UtilisateurNonTrouveException("Groupe utilisateur avec libellé '$libelleGroupeUtilisateur' non trouvé.");
        return $result;
    }

    private function getDefaultGroupIdForTypeLibelle(string $typeProfilLibelle): ?string
    {
        $mapTypeToGroupeLibelle = [
            'Etudiant' => 'Etudiants',
            'Enseignant' => 'Enseignants',
            'Personnel Administratif' => 'Personnel_Admin',
            'Administrateur' => 'Adminstrateur_systeme'
        ];
        $libelleGroupe = $mapTypeToGroupeLibelle[$typeProfilLibelle] ?? 'GRP_UTILISATEUR_STANDARD'; // GRP_UTILISATEUR_STANDARD doit exister
        return $this->getIdGroupeUtilisateurParLibelle($libelleGroupe);
    }

    private function getDefaultNiveauAccesId(): string
    {
        return 'ACCES_RESTREINT';
    }

    private function getTableProfilParIdType(?string $idTypeUtilisateur): ?string
    {
        $stmt = $this->db->prepare("SELECT libelle_type_utilisateur FROM type_utilisateur WHERE id_type_utilisateur = :id LIMIT 1");
        $stmt->bindParam(':id', $idTypeUtilisateur);
        $stmt->execute();
        $libelle = $stmt->fetchColumn();

        switch (strtolower($libelle ?: '')) {
            case 'etudiant': return 'etudiant';
            case 'enseignant': return 'enseignant';
            case 'personnel administratif': return 'personnel_administratif';
            case 'administrateur': return null; // Les admins n'ont pas de table de profil séparée dans ce modèle
            default:
                throw new OperationImpossibleException("Aucune table de profil définie pour le type d'utilisateur ID: $idTypeUtilisateur ($libelle)");
        }
    }

    private function getChampEmailProfilParIdType(?string $idTypeUtilisateur): ?string
    {
        $stmt = $this->db->prepare("SELECT libelle_type_utilisateur FROM type_utilisateur WHERE id_type_utilisateur = :id LIMIT 1");
        $stmt->bindParam(':id', $idTypeUtilisateur);
        $stmt->execute();
        $libelle = $stmt->fetchColumn();

        switch (strtolower($libelle ?: '')) {
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
            throw new ValidationException("Le mot de passe fourni n'est pas assez robuste.", $robustesse['erreurs'] ?? []);
        }

        $emailProfil = $this->extraireEmailDuProfilConcret($donneesProfil, $typeProfilLibelle);
        if (empty($emailProfil) || !filter_var($emailProfil, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException("L'email du profil est requis et doit être un email valide.", ['email_profil' => "Requis et doit être un email valide."]);
        }
    }

    private function extraireEmailDuProfilConcret(array $donneesProfil, string $typeProfilLibelle): ?string
    {
        $idType = $this->getIdTypeUtilisateurParLibelle($typeProfilLibelle);
        $champEmail = $this->getChampEmailProfilParIdType($idType);
        if(!$champEmail) return null;
        return $donneesProfil[$champEmail] ?? null;
    }

    private function creerProfilSpecifiqueAssocie(string $numeroUtilisateur, array $donneesProfil, string $idTypeUtilisateur): void
    {
        $tableProfil = $this->getTableProfilParIdType($idTypeUtilisateur);
        if (!$tableProfil) { // Cas admin ou type sans profil dédié
            return;
        }

        $modelProfil = $this->getModelPourTableProfil($tableProfil);
        $donneesProfilPourTable = ['numero_utilisateur' => $numeroUtilisateur];
        $colonnesAttendues = $this->getColonnesAttenduesPourTableProfil($tableProfil);

        foreach ($colonnesAttendues as $colonneSpec) {
            if ($colonneSpec === 'numero_utilisateur') continue;

            $valeurFournie = $donneesProfil[$colonneSpec] ?? null;
            $infoColonne = $this->getInfosColonneTableProfil($tableProfil, $colonneSpec);

            $estNullable = isset($infoColonne['Null']) && strtoupper($infoColonne['Null']) === 'YES';
            $aValeurParDefaut = isset($infoColonne['Default']) && $infoColonne['Default'] !== null;

            if (!$estNullable && !$aValeurParDefaut && ($valeurFournie === null || $valeurFournie === '')) {
                $pkProfilSpecifique = null;
                if ($tableProfil === 'etudiant') $pkProfilSpecifique = 'numero_carte_etudiant';
                elseif ($tableProfil === 'enseignant') $pkProfilSpecifique = 'numero_enseignant'; // Le schéma du 31 Mai utilise numero_enseignant comme PK
                elseif ($tableProfil === 'personnel_administratif') $pkProfilSpecifique = 'numero_personnel_administratif';

                if ($colonneSpec === $pkProfilSpecifique && empty($valeurFournie)) {
                    throw new ValidationException("L'identifiant métier '$colonneSpec' est requis pour le profil.");
                } else if ($colonneSpec !== $pkProfilSpecifique) {
                    throw new ValidationException("Champ requis '$colonneSpec' manquant ou vide pour le profil.");
                }
            }
            $donneesProfilPourTable[$colonneSpec] = ($valeurFournie === '') ? null : $valeurFournie;
        }
        $modelProfil->creer($donneesProfilPourTable);
    }

    private function getInfosColonneTableProfil(string $nomTable, string $nomColonne): ?array
    {
        try {
            $stmt = $this->db->query("DESCRIBE `$nomTable` `$nomColonne`");
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
        $idHistorique = $this->genererIdUniquePourTable('historique_mot_de_passe', 'HISTMDP_', 10);
        $this->historiqueMotDePasseModel->creer([
            'id_historique_mdp' => $idHistorique,
            'numero_utilisateur' => $numeroUtilisateur,
            'mot_de_passe_hache' => $motDePasseHache
        ]);
    }

    private function nettoyerHistoriqueMotDePasse(string $numeroUtilisateur): void
    {
        $historique = $this->historiqueMotDePasseModel->recupererHistoriquePourUtilisateur($numeroUtilisateur, self::PASSWORD_HISTORY_LIMIT + 5); // Récupérer un peu plus pour être sûr

        if (count($historique) > self::PASSWORD_HISTORY_LIMIT) {
            $idsASupprimer = array_slice(array_column($historique, 'id_historique_mdp'), self::PASSWORD_HISTORY_LIMIT);
            if (!empty($idsASupprimer)) {
                $this->historiqueMotDePasseModel->supprimerPlusieursParIdentifiants($idsASupprimer);
            }
        }
    }

    private function genererEtStockerTokenPourUtilisateur(string $numeroUtilisateur, string $nomChampToken): array
    {
        $tokenClair = bin2hex(random_bytes(self::TOKEN_LENGHT_BYTES));
        $tokenHache = hash('sha256', $tokenClair);
        $champDateExpiration = null;
        $dateExpiration = null;
        $champsAMettreAJour = [];

        if ($nomChampToken === 'token_reset_mdp') {
            $champDateExpiration = 'date_expiration_token_reset';
            $dateExpiration = (new DateTimeImmutable())->add(new DateInterval('PT' . self::PASSWORD_RESET_TOKEN_EXPIRY_HOURS . 'H'));
            $champsAMettreAJour[$nomChampToken] = $tokenHache;
            $champsAMettreAJour[$champDateExpiration] = $dateExpiration->format('Y-m-d H:i:s');
        } elseif ($nomChampToken === 'token_validation_email') {
            // Pas de date d'expiration en BDD pour ce token comme convenu
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

    private function genererIdUniquePourTable(string $table, string $prefix = '', int $longueurSuffixeHex = 10): string {
        $modelGenerique = new class($this->db) extends BaseModel {
            public function __construct(PDO $db, ?string $customTable = null, ?string $customPk = null) {
                parent::__construct($db);
                if($customTable) $this->table = $customTable;
                if($customPk) $this->clePrimaire = $customPk;
            }
            public function setTableAndPk(string $table, string $pk){ // Méthode pour configurer dynamiquement
                $this->table = $table;
                $this->clePrimaire = $pk;
            }
        };
        // Déterminer la clé primaire pour la table donnée
        // Le schéma du 31 Mai utilise des PKs VARCHAR pour ces tables de référence
        $pkName = '';
        if ($table === 'historique_mot_de_passe') $pkName = 'id_historique_mdp';
        else if ($table === 'action') $pkName = 'id_action';
        // ... ajouter d'autres cas si nécessaire pour d'autres tables où des ID uniques sont générés.
        // Pour l'instant, seul historique_mot_de_passe l'utilise.
        else { throw new \InvalidArgumentException("Configuration de clé primaire manquante pour la table $table dans genererIdUniquePourTable");}

        $modelGenerique->setTableAndPk($table, $pkName);

        $longueurMaxPK = 50;
        $prefix = strtoupper($prefix);

        do {
            $suffixe = bin2hex(random_bytes(intval(ceil($longueurSuffixeHex / 2))));
            $idGenere = $prefix . $suffixe;
            if (strlen($idGenere) > $longueurMaxPK) {
                $idGenere = substr($idGenere, 0, $longueurMaxPK);
            }
        } while ($modelGenerique->trouverParIdentifiant($idGenere, [$pkName]));
        return $idGenere;
    }
}