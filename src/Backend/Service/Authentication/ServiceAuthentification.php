<?php

namespace App\Backend\Service\Authentication;

use PDO;
use PDOException;
use DateTimeImmutable;
use DateInterval;
use App\Config\Database;
use App\Backend\Model\Utilisateur as UtilisateurModel;
use App\Backend\Model\Etudiant as EtudiantModel;
use App\Backend\Model\Enseignant as EnseignantModel;
use App\Backend\Model\PersonnelAdministratif as PersonnelAdministratifModel;
use App\Backend\Model\HistoriqueMotDePasse as HistoriqueMotDePasseModel;
use App\Backend\Model\TypeUtilisateur as TypeUtilisateurModel;
use App\Backend\Model\GroupeUtilisateur as GroupeUtilisateurModel;
use App\Backend\Model\NiveauAccesDonne as NiveauAccesDonneModel;
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
use const Sodium\SODIUM_BASE32_VARIANT_RFC4648;

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
    private TypeUtilisateurModel $typeUtilisateurModel;
    private GroupeUtilisateurModel $groupeUtilisateurModel;
    private NiveauAccesDonneModel $niveauAccesDonneModel;

    private const MAX_LOGIN_ATTEMPTS = 5;
    private const ACCOUNT_LOCKOUT_DURATION_INTERVAL = 'PT15M'; // ISO 8601 duration
    private const PASSWORD_RESET_TOKEN_EXPIRY_HOURS = 1;
    private const PASSWORD_HISTORY_LIMIT = 3;
    private const TOKEN_LENGHT_BYTES = 32;

    private const PASSWORD_MIN_LENGTH = 10;
    private const PASSWORD_REQ_UPPERCASE = true;
    private const PASSWORD_REQ_LOWERCASE = true;
    private const PASSWORD_REQ_NUMBER = true;
    private const PASSWORD_REQ_SPECIAL = true;
    private string $appNameFor2FA;

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
        $this->typeUtilisateurModel = new TypeUtilisateurModel($db);
        $this->groupeUtilisateurModel = new GroupeUtilisateurModel($db);
        $this->niveauAccesDonneModel = new NiveauAccesDonneModel($db);
        $this->appNameFor2FA = $_ENV['APP_NAME_FOR_2FA'] ?? 'GestionMySoutenance';

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function tenterConnexion(string $identifiant, string $motDePasse): object
    {
        $utilisateurBase = $this->utilisateurModel->trouverParLoginOuEmailPrincipal($identifiant);

        if (!$utilisateurBase) {
            $this->journaliserActionAuthentification(null, $identifiant, 'AUTH_LOGIN_UNKNOWN_ID', 'ECHEC', ['identifiant_fourni' => $identifiant]);
            throw new UtilisateurNonTrouveException("Identifiant ou mot de passe incorrect.");
        }

        $numeroUtilisateur = $utilisateurBase['numero_utilisateur'];

        if ($this->estCompteActuellementBloque($numeroUtilisateur)) {
            $this->journaliserActionAuthentification(null, $numeroUtilisateur, 'AUTH_LOGIN_ACCOUNT_LOCKED', 'ECHEC');
            throw new CompteBloqueException("Ce compte est temporairement bloqué. Veuillez réessayer plus tard ou contacter le support.");
        }

        if (!password_verify($motDePasse, $utilisateurBase['mot_de_passe'])) {
            $this->traiterTentativeConnexionEchoueePourUtilisateur($numeroUtilisateur);
            $this->journaliserActionAuthentification(null, $numeroUtilisateur, 'AUTH_LOGIN_WRONG_PASSWORD', 'ECHEC');
            throw new IdentifiantsInvalidesException("Identifiant ou mot de passe incorrect.");
        }

        if ($utilisateurBase['statut_compte'] === 'en_attente_validation' && !$utilisateurBase['email_valide']) {
            $this->journaliserActionAuthentification(null, $numeroUtilisateur, 'AUTH_LOGIN_EMAIL_NOT_VALIDATED', 'ECHEC');
            throw new CompteNonValideException("Votre compte n'a pas encore été validé par email. Veuillez vérifier vos emails.");
        }

        if ($utilisateurBase['statut_compte'] !== 'actif') {
            $this->journaliserActionAuthentification(null, $numeroUtilisateur, 'AUTH_LOGIN_ACCOUNT_NOT_ACTIVE', 'ECHEC', ['statut_actuel' => $utilisateurBase['statut_compte']]);
            throw new CompteNonValideException("Ce compte n'est pas actif (statut: " . htmlspecialchars($utilisateurBase['statut_compte']) . "). Veuillez contacter le support.");
        }

        $preferences2FAActive = (bool) $utilisateurBase['preferences_2fa_active'];
        if ($preferences2FAActive) {
            $_SESSION['2fa_user_num_pending_verification'] = $numeroUtilisateur;
            $_SESSION['2fa_authentication_pending'] = true;
            $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'AUTH_LOGIN_2FA_REQUIRED', 'INFO');
            throw new AuthenticationException("Authentification à deux facteurs requise.", 1001); // Code spécifique pour redirection
        }

        $this->reinitialiserTentativesConnexion($numeroUtilisateur);
        $this->mettreAJourDerniereConnexion($numeroUtilisateur);

        $utilisateurComplet = $this->recupererUtilisateurCompletParNumero($numeroUtilisateur);
        if (!$utilisateurComplet) {
            $this->journaliserActionAuthentification(null, $numeroUtilisateur, 'AUTH_LOGIN_PROFILE_LOAD_ERROR', 'ERREUR_INTERNE');
            throw new OperationImpossibleException("Impossible de récupérer les informations complètes de l'utilisateur après connexion.");
        }
        $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'AUTH_LOGIN_SUCCESS', 'SUCCES');
        return $utilisateurComplet;
    }

    public function traiterTentativeConnexionEchoueePourUtilisateur(string $numeroUtilisateur): void
    {
        $this->utilisateurModel->commencerTransaction();
        try {
            $stmtInc = $this->db->prepare("UPDATE `utilisateur` SET `tentatives_connexion_echouees` = `tentatives_connexion_echouees` + 1 WHERE `numero_utilisateur` = :num_user FOR UPDATE");
            $stmtInc->bindParam(':num_user', $numeroUtilisateur, PDO::PARAM_STR);
            $stmtInc->execute();

            $stmtCheck = $this->db->prepare("SELECT `tentatives_connexion_echouees` FROM `utilisateur` WHERE `numero_utilisateur` = :num_user");
            $stmtCheck->bindParam(':num_user', $numeroUtilisateur, PDO::PARAM_STR);
            $stmtCheck->execute();
            $tentatives = (int)$stmtCheck->fetchColumn();

            if ($tentatives >= self::MAX_LOGIN_ATTEMPTS) {
                $dateBlocage = (new DateTimeImmutable())->add(new DateInterval(self::ACCOUNT_LOCKOUT_DURATION_INTERVAL));
                $stmtLock = $this->db->prepare("UPDATE `utilisateur` SET `compte_bloque_jusqua` = :date_blocage, `statut_compte` = 'bloque' WHERE `numero_utilisateur` = :num_user");
                $stmtLock->bindValue(':date_blocage', $dateBlocage->format('Y-m-d H:i:s'));
                $stmtLock->bindParam(':num_user', $numeroUtilisateur, PDO::PARAM_STR);
                $stmtLock->execute();
                $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'AUTH_ACCOUNT_LOCKED_MAX_ATTEMPTS', 'ALERTE', ['tentatives' => $tentatives]);
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
        if (!$user) throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé.");

        $success = $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, [
            'tentatives_connexion_echouees' => 0,
            'compte_bloque_jusqua' => null
        ]);
        if (!$success) throw new OperationImpossibleException("Échec de la réinitialisation des tentatives pour l'utilisateur '$numeroUtilisateur'.");
    }

    public function estCompteActuellementBloque(string $numeroUtilisateur): bool
    {
        $user = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['compte_bloque_jusqua', 'statut_compte']);
        if (!$user) throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé.");

        if ($user['statut_compte'] === 'bloque' && $user['compte_bloque_jusqua']) {
            try {
                $dateBlocageFin = new DateTimeImmutable($user['compte_bloque_jusqua']);
                if (new DateTimeImmutable() < $dateBlocageFin) {
                    return true;
                } else {
                    $this->changerStatutDuCompte($numeroUtilisateur, 'actif', 'Déblocage automatique après expiration.');
                    return false;
                }
            } catch (\Exception $e) {
                error_log("Erreur de date pour compte_bloque_jusqua pour user $numeroUtilisateur: " . $e->getMessage());
                return true; // Par précaution, considérer comme bloqué si la date est invalide
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
        if (!$user) throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé.");

        $secretBinary = $this->tfaProvider->createSecret(160); // Default bits
        $secretBase32 = \Sodium\sodium_bin2base32($secretBinary, SODIUM_BASE32_VARIANT_RFC4648);

        $success = $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['secret_2fa' => $secretBase32]);
        if (!$success) throw new OperationImpossibleException("Impossible de stocker le secret 2FA pour l'utilisateur '$numeroUtilisateur'.");

        $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'AUTH_2FA_SECRET_GENERATED', 'SUCCES');
        $label = $user['email_principal'] ?: ($user['login_utilisateur'] ?: $numeroUtilisateur);
        return $this->tfaProvider->getQRCodeImageAsDataUri(rawurlencode($this->appNameFor2FA . ':' . $label), $secretBase32);
    }

    public function activerAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTPVerifie): bool
    {
        $user = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['numero_utilisateur', 'secret_2fa']);
        if (!$user) throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé.");
        if (empty($user['secret_2fa'])) throw new OperationImpossibleException("Secret 2FA non configuré pour '$numeroUtilisateur'.");

        if ($this->tfaProvider->verifyCode($user['secret_2fa'], $codeTOTPVerifie, 2)) { // Tolérance de 2*30 secondes
            $success = $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['preferences_2fa_active' => true]);
            if ($success) {
                $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'AUTH_2FA_ACTIVATED', 'SUCCES');
                return true;
            }
            throw new OperationImpossibleException("Échec de la mise à jour pour activer la 2FA pour '$numeroUtilisateur'.");
        }
        $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'AUTH_2FA_ACTIVATION_INVALID_CODE', 'ECHEC');
        throw new MotDePasseInvalideException("Code d'authentification à deux facteurs invalide.");
    }

    public function verifierCodeAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTP): bool
    {
        $user = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['numero_utilisateur', 'secret_2fa', 'preferences_2fa_active']);
        if (!$user) throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé.");

        $pref2FA = (bool) $user['preferences_2fa_active'];
        if (!$pref2FA || empty($user['secret_2fa'])) {
            $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'AUTH_2FA_VERIFY_NOT_ACTIVE', 'ECHEC');
            throw new OperationImpossibleException("L'authentification 2FA n'est pas active ou configurée.");
        }

        $isValid = $this->tfaProvider->verifyCode($user['secret_2fa'], $codeTOTP, 2);
        if ($isValid) {
            unset($_SESSION['2fa_authentication_pending'], $_SESSION['2fa_user_num_pending_verification']);
            $this->reinitialiserTentativesConnexion($numeroUtilisateur);
            $this->mettreAJourDerniereConnexion($numeroUtilisateur);
            $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'AUTH_2FA_VERIFY_SUCCESS', 'SUCCES');
        } else {
            $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'AUTH_2FA_VERIFY_FAILED', 'ECHEC');
            throw new MotDePasseInvalideException("Code d'authentification à deux facteurs invalide.");
        }
        return $isValid;
    }

    public function desactiverAuthentificationDeuxFacteurs(string $numeroUtilisateur): bool
    {
        $user = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['numero_utilisateur']);
        if (!$user) throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé.");

        $success = $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['preferences_2fa_active' => false, 'secret_2fa' => null]);
        if ($success) $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'AUTH_2FA_DEACTIVATED', 'SUCCES');
        return $success;
    }

    public function demarrerSessionUtilisateur(object $utilisateurAvecProfil): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_regenerate_id(true);
        $_SESSION['numero_utilisateur'] = $utilisateurAvecProfil->numero_utilisateur;
        $_SESSION['login_utilisateur'] = $utilisateurAvecProfil->login_utilisateur;
        $_SESSION['id_type_utilisateur'] = $utilisateurAvecProfil->id_type_utilisateur;
        $_SESSION['id_groupe_utilisateur'] = $utilisateurAvecProfil->id_groupe_utilisateur;
        $_SESSION['libelle_type_utilisateur'] = $utilisateurAvecProfil->libelle_type_utilisateur ?? 'N/A';
        $_SESSION['libelle_groupe_utilisateur'] = $utilisateurAvecProfil->libelle_groupe_utilisateur ?? 'N/A';
        $_SESSION['user_complet'] = $utilisateurAvecProfil;
        $_SESSION['last_activity'] = time();
    }

    public function estUtilisateurConnecteEtSessionValide(): bool
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['numero_utilisateur'])) return false;

        $sessionTimeout = $_ENV['SESSION_LIFETIME'] ?? 3600;
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > (int)$sessionTimeout)) {
            $this->terminerSessionUtilisateur();
            return false;
        }
        $_SESSION['last_activity'] = time();
        return true;
    }

    public function getUtilisateurConnecteComplet(): ?object
    {
        return ($this->estUtilisateurConnecteEtSessionValide() && isset($_SESSION['user_complet'])) ? $_SESSION['user_complet'] : null;
    }

    public function terminerSessionUtilisateur(): void
    {
        $numUserJournal = 'ANONYME_DECONNEXION_SANS_SESSION';
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (isset($_SESSION['numero_utilisateur'])) $numUserJournal = $_SESSION['numero_utilisateur'];

        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
        $this->journaliserActionAuthentification(null, $numUserJournal, 'AUTH_LOGOUT_SESSION_ENDED', 'SUCCES');
    }

    public function creerCompteUtilisateurComplet(array $donneesUtilisateur, array $donneesProfil, string $idTypeUtilisateurProfil, bool $envoyerEmailValidation = true): string
    {
        $this->validerDonneesCreationCompte($donneesUtilisateur, $donneesProfil, $idTypeUtilisateurProfil);

        $numeroUtilisateur = $this->genererNumeroUtilisateurUniqueNonSequentiel();
        $motDePasseHache = password_hash($donneesUtilisateur['mot_de_passe'], PASSWORD_ARGON2ID ?: PASSWORD_DEFAULT);
        $emailProfil = $this->extraireEmailDuProfilConcret($donneesProfil, $idTypeUtilisateurProfil);

        if ($this->utilisateurModel->emailPrincipalExiste($emailProfil)) {
            throw new EmailNonValideException("L'email principal '$emailProfil' est déjà utilisé.");
        }
        if ($this->utilisateurModel->loginExiste($donneesUtilisateur['login_utilisateur'])) {
            throw new ValidationException("Le login utilisateur '{$donneesUtilisateur['login_utilisateur']}' est déjà utilisé.");
        }

        $typeUtilisateur = $this->typeUtilisateurModel->trouverParIdentifiant($idTypeUtilisateurProfil);
        if(!$typeUtilisateur) throw new OperationImpossibleException("Type utilisateur ID '$idTypeUtilisateurProfil' inconnu.");

        if ($idTypeUtilisateurProfil === ($_ENV['ID_TYPE_ETUDIANT'] ?? 'TYPE_ETUD')) {
            if (!isset($donneesProfil['numero_carte_etudiant']) || empty($donneesUtilisateur['id_annee_academique_inscription'])) {
                throw new ValidationException("Numéro carte étudiant et année d'inscription sont requis pour un étudiant.");
            }
            $statutScolarite = $this->serviceGestionAcademique->verifierStatutScolariteEtudiant($donneesProfil['numero_carte_etudiant'], $donneesUtilisateur['id_annee_academique_inscription']);
            if (!$statutScolarite['eligible_creation_compte']) {
                throw new OperationImpossibleException("L'étudiant n'est pas éligible à la création de compte: " . ($statutScolarite['raison_ineligibilite'] ?? 'Raison inconnue'));
            }
        }

        $this->db->beginTransaction();
        try {
            $idGroupe = $donneesUtilisateur['id_groupe_utilisateur'] ?? $this->getDefaultGroupIdForTypeId($idTypeUtilisateurProfil);
            $idNiveauAcces = $donneesUtilisateur['id_niveau_acces_donne'] ?? $this->getDefaultNiveauAccesId();

            $donneesBaseUtilisateur = [
                'numero_utilisateur' => $numeroUtilisateur,
                'login_utilisateur' => $donneesUtilisateur['login_utilisateur'],
                'mot_de_passe' => $motDePasseHache,
                'id_type_utilisateur' => $idTypeUtilisateurProfil,
                'id_groupe_utilisateur' => $idGroupe,
                'email_principal' => $emailProfil,
                'statut_compte' => 'en_attente_validation',
                'photo_profil' => $donneesUtilisateur['photo_profil'] ?? null,
                'id_niveau_acces_donne' => $idNiveauAcces,
                'email_valide' => false,
                'date_creation' => (new DateTimeImmutable())->format('Y-m-d H:i:s')
            ];
            $this->utilisateurModel->creer($donneesBaseUtilisateur);
            $this->creerProfilSpecifiqueAssocie($numeroUtilisateur, $donneesProfil, $idTypeUtilisateurProfil);
            $this->ajouterMotDePasseHistorique($numeroUtilisateur, $motDePasseHache);

            if ($envoyerEmailValidation) {
                $tokenData = $this->genererEtStockerTokenPourUtilisateur($numeroUtilisateur, 'token_validation_email');
                $this->envoyerEmailValidationCompte($numeroUtilisateur, $emailProfil, $tokenData['token_clair']);
            }

            $this->db->commit();
            $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'AUTH_ACCOUNT_CREATED_' . strtoupper(str_replace(' ', '_', $typeUtilisateur['libelle_type_utilisateur'] ?? $idTypeUtilisateurProfil)), 'SUCCES');
            return $numeroUtilisateur;

        } catch (PDOException $e) {
            $this->db->rollBack();
            $this->journaliserActionAuthentification(null, $numeroUtilisateur ?: 'N/A_CREATION_ECHEC', 'AUTH_ACCOUNT_CREATE_DB_ERROR', 'ECHEC', ['erreur' => $e->getMessage()]);
            if ((int)$e->getCode() === 23000 ) {
                if (stripos($e->getMessage(), $this->utilisateurModel->getTable().'.login_utilisateur') !== false) throw new ValidationException("Ce login est déjà utilisé.", [], (int)$e->getCode(), $e);
                if (stripos($e->getMessage(), $this->utilisateurModel->getTable().'.email_principal') !== false) throw new EmailNonValideException("Cet email principal est déjà utilisé.", (int)$e->getCode(), $e);
            }
            throw new OperationImpossibleException("Erreur BDD création compte: " . $e->getMessage(), (int)$e->getCode(), $e);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->journaliserActionAuthentification(null, $numeroUtilisateur ?: 'N/A_CREATION_ECHEC', 'AUTH_ACCOUNT_CREATE_ERROR', 'ECHEC', ['erreur' => $e->getMessage()]);
            throw new OperationImpossibleException("Erreur création compte: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function genererNumeroUtilisateurUniqueNonSequentiel(): string
    {
        $maxTentatives = 10; $tentative = 0;
        do {
            $entropy = bin2hex(random_bytes(8));
            $prefix = 'USR' . date('ymd');
            $numero = strtoupper(substr($prefix . $entropy, 0, 50));
            $tentative++;
            if ($tentative > $maxTentatives) throw new OperationImpossibleException("Impossible de générer un numero_utilisateur unique.");
        } while ($this->utilisateurModel->trouverParNumeroUtilisateur($numero, ['numero_utilisateur']));
        return $numero;
    }

    public function envoyerEmailValidationCompte(string $numeroUtilisateur, string $emailPrincipal, string $tokenValidation): void
    {
        $utilisateur = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['login_utilisateur']);
        $login = $utilisateur['login_utilisateur'] ?? 'Nouvel utilisateur';
        $appName = $_ENV['APP_NAME'] ?? 'GestionMySoutenance';
        $sujet = "Validation de votre compte {$appName}";
        $urlValidation = rtrim(getenv('APP_URL') ?: ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'), '/') . '/validate-email?token=' . urlencode($tokenValidation);
        $corps = "Bonjour " . htmlspecialchars($login) . ",\n\nVeuillez cliquer sur le lien suivant pour valider l'adresse email associée à votre compte : " . $urlValidation . "\n\nSi vous n'avez pas créé de compte, veuillez ignorer cet email.\n\nCordialement,\nL'équipe {$appName}";
        try {
            $this->serviceEmail->envoyerEmail($emailPrincipal, $sujet, $corps);
            $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'AUTH_VALIDATION_EMAIL_SENT', 'SUCCES', ['email_destinataire' => $emailPrincipal]);
        } catch (\Exception $e) {
            $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'AUTH_VALIDATION_EMAIL_SEND_ERROR', 'ECHEC', ['erreur' => $e->getMessage(), 'email_destinataire' => $emailPrincipal]);
            throw new OperationImpossibleException("Erreur envoi email de validation: " . $e->getMessage(), 0, $e);
        }
    }

    public function validerCompteEmailViaToken(string $tokenValidation): bool
    {
        if (empty($tokenValidation)) throw new TokenInvalideException("Token de validation manquant.");
        $tokenHache = hash('sha256', $tokenValidation);
        $user = $this->utilisateurModel->trouverParTokenValidationEmailHache($tokenHache, ['numero_utilisateur', 'statut_compte', 'email_valide']);
        if (!$user || $user['email_valide']) { // Soit token non trouvé, soit email déjà validé
            $this->journaliserActionAuthentification(null, 'TOKEN_EMAIL_VALIDATION_ATTEMPT', 'AUTH_VALIDATION_EMAIL_TOKEN_INVALID_OR_USED', 'ECHEC', ['token_fourni_partiel' => substr($tokenValidation,0,10)]);
            throw new TokenInvalideException("Token de validation invalide ou déjà utilisé.");
        }

        $this->db->beginTransaction();
        try {
            $champsMaj = ['email_valide' => true, 'token_validation_email' => null];
            if ($user['statut_compte'] === 'en_attente_validation') $champsMaj['statut_compte'] = 'actif';
            $this->utilisateurModel->mettreAJourChamps($user['numero_utilisateur'], $champsMaj);
            $this->db->commit();
            $this->journaliserActionAuthentification($user['numero_utilisateur'], $user['numero_utilisateur'], 'AUTH_EMAIL_VALIDATION_SUCCESS', 'SUCCES');
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            $this->journaliserActionAuthentification(null, $user['numero_utilisateur'], 'AUTH_EMAIL_VALIDATION_DB_ERROR', 'ECHEC', ['erreur' => $e->getMessage()]);
            throw new OperationImpossibleException("Erreur BDD validation compte: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    private function construireObjetUtilisateurComplet(array $utilisateurBase): ?object
    {
        if (empty($utilisateurBase['numero_utilisateur'])) return null;
        $profilData = [];
        $tableProfil = $this->getTableProfilParIdType($utilisateurBase['id_type_utilisateur']);
        if ($tableProfil) {
            $modelProfil = $this->getModelPourTableProfil($tableProfil);
            $profilData = $modelProfil->trouverUnParCritere(['numero_utilisateur' => $utilisateurBase['numero_utilisateur']]) ?: [];
        }
        $typeUtilisateur = $this->typeUtilisateurModel->trouverParIdentifiant($utilisateurBase['id_type_utilisateur']);
        $groupeUtilisateur = $this->groupeUtilisateurModel->trouverParIdentifiant($utilisateurBase['id_groupe_utilisateur']);
        $niveauAcces = $this->niveauAccesDonneModel->trouverParIdentifiant($utilisateurBase['id_niveau_acces_donne']);

        $merged = array_merge($utilisateurBase, $profilData);
        $merged['libelle_type_utilisateur'] = $typeUtilisateur['libelle_type_utilisateur'] ?? $utilisateurBase['id_type_utilisateur'];
        $merged['libelle_groupe_utilisateur'] = $groupeUtilisateur['libelle_groupe_utilisateur'] ?? $utilisateurBase['id_groupe_utilisateur'];
        $merged['libelle_niveau_acces_donne'] = $niveauAcces['libelle_niveau_acces_donne'] ?? $utilisateurBase['id_niveau_acces_donne'];
        return (object) $merged;
    }

    public function recupererUtilisateurCompletParNumero(string $numeroUtilisateur): ?object
    {
        $utilisateurBase = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur);
        return $utilisateurBase ? $this->construireObjetUtilisateurComplet($utilisateurBase) : null;
    }

    public function recupererUtilisateurCompletParEmailPrincipal(string $emailPrincipal): ?object
    {
        $utilisateurBase = $this->utilisateurModel->trouverParEmailPrincipal($emailPrincipal);
        return $utilisateurBase ? $this->construireObjetUtilisateurComplet($utilisateurBase) : null;
    }

    public function recupererUtilisateurCompletParLogin(string $login): ?object
    {
        $utilisateurBase = $this->utilisateurModel->trouverParLoginUtilisateur($login);
        return $utilisateurBase ? $this->construireObjetUtilisateurComplet($utilisateurBase) : null;
    }

    public function listerUtilisateursAvecProfils(array $criteres = [], int $page = 1, int $elementsParPage = 25): array
    {
        $offset = ($page - 1) * $elementsParPage;
        $idTypeEtudiant = $_ENV['ID_TYPE_ETUDIANT'] ?? 'TYPE_ETUD';
        $idTypeEnseignant = $_ENV['ID_TYPE_ENSEIGNANT'] ?? 'TYPE_ENS';
        $idTypePersonnelAdmin = $_ENV['ID_TYPE_PERS_ADMIN'] ?? 'TYPE_PERS_ADMIN';

        $selectFields = "u.*, tu.libelle_type_utilisateur, gu.libelle_groupe_utilisateur, na.libelle_niveau_acces_donne,
                         COALESCE(et.nom, en.nom, pa.nom) as nom_profil,
                         COALESCE(et.prenom, en.prenom, pa.prenom) as prenom_profil,
                         COALESCE(et.email_contact_secondaire, en.email_professionnel, pa.email_professionnel) as email_profil_specifique";
        $fromClause = "`utilisateur` u
                       LEFT JOIN `type_utilisateur` tu ON u.id_type_utilisateur = tu.id_type_utilisateur
                       LEFT JOIN `groupe_utilisateur` gu ON u.id_groupe_utilisateur = gu.id_groupe_utilisateur
                       LEFT JOIN `niveau_acces_donne` na ON u.id_niveau_acces_donne = na.id_niveau_acces_donne
                       LEFT JOIN `etudiant` et ON u.numero_utilisateur = et.numero_utilisateur AND u.id_type_utilisateur = '$idTypeEtudiant'
                       LEFT JOIN `enseignant` en ON u.numero_utilisateur = en.numero_utilisateur AND u.id_type_utilisateur = '$idTypeEnseignant'
                       LEFT JOIN `personnel_administratif` pa ON u.numero_utilisateur = pa.numero_utilisateur AND u.id_type_utilisateur = '$idTypePersonnelAdmin'";

        $whereClauses = []; $params = [];
        if (!empty($criteres['statut_compte'])) { $whereClauses[] = "u.statut_compte = :statut_compte"; $params[':statut_compte'] = $criteres['statut_compte']; }
        if (!empty($criteres['id_type_utilisateur'])) { $whereClauses[] = "u.id_type_utilisateur = :id_type_utilisateur"; $params[':id_type_utilisateur'] = $criteres['id_type_utilisateur']; }
        if (!empty($criteres['id_groupe_utilisateur'])) { $whereClauses[] = "u.id_groupe_utilisateur = :id_groupe_utilisateur"; $params[':id_groupe_utilisateur'] = $criteres['id_groupe_utilisateur']; }
        if (!empty($criteres['recherche_generale'])) {
            $searchTerm = '%' . $criteres['recherche_generale'] . '%';
            $searchConditions = ["u.login_utilisateur LIKE :recherche", "u.email_principal LIKE :recherche", "u.numero_utilisateur LIKE :recherche",
                "COALESCE(et.nom, en.nom, pa.nom) LIKE :recherche", "COALESCE(et.prenom, en.prenom, pa.prenom) LIKE :recherche",
                "COALESCE(et.numero_carte_etudiant) LIKE :recherche" ];
            $whereClauses[] = "(" . implode(" OR ", $searchConditions) . ")";
            $params[':recherche'] = $searchTerm;
        }
        $sqlWhere = !empty($whereClauses) ? " WHERE " . implode(" AND ", $whereClauses) : "";

        $sqlCount = "SELECT COUNT(DISTINCT u.numero_utilisateur) FROM " . $fromClause . $sqlWhere;
        $stmtCount = $this->db->prepare($sqlCount);
        foreach ($params as $key => $value) $stmtCount->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        $stmtCount->execute();
        $totalElements = (int)$stmtCount->fetchColumn();

        $sqlQuery = "SELECT " . $selectFields . " FROM " . $fromClause . $sqlWhere . " ORDER BY u.date_creation DESC LIMIT :limit OFFSET :offset";
        $stmtQuery = $this->db->prepare($sqlQuery);
        foreach ($params as $key => $value) $stmtQuery->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
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
        if (!$user) throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé.");

        if (!$parAdmin) {
            if ($ancienMotDePasseClair === null || !password_verify($ancienMotDePasseClair, $user['mot_de_passe'])) {
                $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'AUTH_CHANGE_PWD_OLD_PWD_INVALID', 'ECHEC');
                throw new MotDePasseInvalideException("L'ancien mot de passe fourni est incorrect.");
            }
        }
        $robustesse = $this->verifierRobustesseMotDePasse($nouveauMotDePasseClair);
        if (!$robustesse['valide']) {
            $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'AUTH_CHANGE_PWD_NEW_PWD_WEAK', 'ECHEC', ['erreurs' => $robustesse['messages_erreur']]);
            throw new ValidationException("Le nouveau mot de passe n'est pas assez robuste: " . implode(', ', $robustesse['messages_erreur']));
        }
        if ($this->estNouveauMotDePasseDansHistorique($numeroUtilisateur, $nouveauMotDePasseClair, self::PASSWORD_HISTORY_LIMIT)) {
            $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'AUTH_CHANGE_PWD_NEW_PWD_IN_HISTORY', 'ECHEC');
            throw new MotDePasseInvalideException("Le nouveau mot de passe a déjà été utilisé récemment.");
        }
        $nouveauMotDePasseHache = password_hash($nouveauMotDePasseClair, PASSWORD_ARGON2ID ?: PASSWORD_DEFAULT);

        $this->db->beginTransaction();
        try {
            $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['mot_de_passe' => $nouveauMotDePasseHache]);
            $this->ajouterMotDePasseHistorique($numeroUtilisateur, $nouveauMotDePasseHache);
            $this->nettoyerHistoriqueMotDePasse($numeroUtilisateur);
            $this->db->commit();
            $this->journaliserActionAuthentification($parAdmin ? 'ADMIN_ACTION' : $numeroUtilisateur, $numeroUtilisateur, 'AUTH_CHANGE_PWD_SUCCESS', 'SUCCES', ['par_admin' => $parAdmin]);
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            $this->journaliserActionAuthentification($parAdmin ? 'ADMIN_ACTION' : $numeroUtilisateur, $numeroUtilisateur, 'AUTH_CHANGE_PWD_DB_ERROR', 'ECHEC', ['erreur' => $e->getMessage()]);
            throw new OperationImpossibleException("Erreur BDD modification mot de passe: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function mettreAJourProfilUtilisateur(string $numeroUtilisateur, string $idTypeUtilisateurProfil, array $donneesProfil): bool
    {
        $userBase = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['numero_utilisateur', 'id_type_utilisateur', 'email_principal']);
        if (!$userBase) throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé.");
        if ($userBase['id_type_utilisateur'] != $idTypeUtilisateurProfil) throw new OperationImpossibleException("Type de profil ne correspond pas pour '$numeroUtilisateur'.");

        $tableProfil = $this->getTableProfilParIdType($userBase['id_type_utilisateur']);
        if (!$tableProfil) throw new OperationImpossibleException("Type de profil inconnu pour '$numeroUtilisateur'.");

        $modelProfil = $this->getModelPourTableProfil($tableProfil);
        $champsProfilMaj = []; $nouvelEmailProfil = null;
        $emailProfilChampNom = $this->getChampEmailProfilParIdType($userBase['id_type_utilisateur']);

        foreach ($donneesProfil as $champ => $valeur) {
            if ($champ === 'numero_utilisateur' || $champ === $modelProfil->getClePrimaire()) continue;
            $champsProfilMaj[$champ] = ($valeur === '') ? null : $valeur;
            if ($champ === $emailProfilChampNom) $nouvelEmailProfil = ($valeur === '') ? null : $valeur;
        }
        if (empty($champsProfilMaj)) return true;

        $this->db->beginTransaction();
        try {
            $pkProfil = $modelProfil->getClePrimaire();
            $conditionsWhere = [$pkProfil => $donneesProfil[$pkProfil] ?? $numeroUtilisateur]; // Si PK profil != numero_utilisateur
            if ($pkProfil === 'numero_utilisateur') $conditionsWhere = ['numero_utilisateur' => $numeroUtilisateur]; // Standard

            $modelProfil->mettreAJourParIdentifiantComposite($conditionsWhere, $champsProfilMaj); // Supposant cette méthode

            if ($nouvelEmailProfil !== null && $nouvelEmailProfil !== $userBase['email_principal']) {
                if (!filter_var($nouvelEmailProfil, FILTER_VALIDATE_EMAIL)) { $this->db->rollBack(); throw new EmailNonValideException("Nouvel email profil '$nouvelEmailProfil' invalide."); }
                if ($this->utilisateurModel->emailPrincipalExiste($nouvelEmailProfil, $numeroUtilisateur)) { $this->db->rollBack(); throw new EmailNonValideException("Nouvel email profil '$nouvelEmailProfil' déjà utilisé."); }

                $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['email_principal' => $nouvelEmailProfil, 'email_valide' => false, 'token_validation_email' => null]);
                $tokenData = $this->genererEtStockerTokenPourUtilisateur($numeroUtilisateur, 'token_validation_email');
                $this->envoyerEmailValidationCompte($numeroUtilisateur, $nouvelEmailProfil, $tokenData['token_clair']);
                $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'AUTH_PROFILE_UPDATE_NEW_EMAIL_VALIDATION_REQ', 'INFO', ['new_email' => $nouvelEmailProfil]);
            }
            $this->db->commit();
            $typeUtilisateur = $this->typeUtilisateurModel->trouverParIdentifiant($idTypeUtilisateurProfil);
            $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'AUTH_PROFILE_UPDATE_SUCCESS', 'SUCCES', ['type_profil' => $typeUtilisateur['libelle_type_utilisateur'] ?? $idTypeUtilisateurProfil]);
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            $this->journaliserActionAuthentification(null, $numeroUtilisateur, 'AUTH_PROFILE_UPDATE_DB_ERROR', 'ECHEC', ['erreur' => $e->getMessage()]);
            throw new OperationImpossibleException("Erreur BDD mise à jour profil: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function mettreAJourCompteUtilisateurParAdmin(string $numeroUtilisateur, array $donneesCompte): bool
    {
        $userBase = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['numero_utilisateur', 'id_type_utilisateur', 'email_principal']);
        if (!$userBase) throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé.");

        $champsMaj = []; $logDetails = [];
        $champsModifiables = ['login_utilisateur', 'id_groupe_utilisateur', 'photo_profil', 'statut_compte', 'id_niveau_acces_donne', 'email_principal'];

        foreach ($donneesCompte as $champ => $valeur) {
            if (in_array($champ, $champsModifiables)) { $champsMaj[$champ] = $valeur; $logDetails[$champ] = $valeur; }
            if ($champ === 'id_type_utilisateur' && $valeur != $userBase['id_type_utilisateur']) throw new OperationImpossibleException("Changement de type utilisateur non supporté ici.");
        }
        if (isset($champsMaj['email_principal']) && $champsMaj['email_principal'] !== $userBase['email_principal']) {
            if (!filter_var($champsMaj['email_principal'], FILTER_VALIDATE_EMAIL)) throw new EmailNonValideException("Email principal invalide.");
            if ($this->utilisateurModel->emailPrincipalExiste($champsMaj['email_principal'], $numeroUtilisateur)) throw new EmailNonValideException("Email principal déjà utilisé.");
            $champsMaj['email_valide'] = false; $champsMaj['token_validation_email'] = null; $logDetails['email_valide'] = false;
        }
        if (empty($champsMaj)) return true;

        try {
            $success = $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, $champsMaj);
            if ($success) {
                $this->journaliserActionAuthentification('ADMIN_ACTION', $numeroUtilisateur, 'AUTH_ACCOUNT_UPDATE_BY_ADMIN', 'SUCCES', ['donnees_modifiees' => $logDetails]);
                if (isset($logDetails['email_principal']) && ($champsMaj['email_valide'] ?? true) === false) {
                    $tokenData = $this->genererEtStockerTokenPourUtilisateur($numeroUtilisateur, 'token_validation_email');
                    $this->envoyerEmailValidationCompte($numeroUtilisateur, $logDetails['email_principal'], $tokenData['token_clair']);
                }
            }
            return $success;
        } catch (PDOException $e) {
            if ((int)$e->getCode() === 23000) {
                if (stripos($e->getMessage(), $this->utilisateurModel->getTable().'.login_utilisateur') !== false) throw new ValidationException("Login déjà utilisé.", [], (int)$e->getCode(), $e);
                if (stripos($e->getMessage(), $this->utilisateurModel->getTable().'.email_principal') !== false) throw new EmailNonValideException("Email principal déjà utilisé.", (int)$e->getCode(), $e);
            }
            $this->journaliserActionAuthentification('ADMIN_ACTION', $numeroUtilisateur, 'AUTH_ACCOUNT_UPDATE_BY_ADMIN_DB_ERROR', 'ECHEC', ['erreur' => $e->getMessage()]);
            throw new OperationImpossibleException("Erreur BDD MàJ compte: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function changerStatutDuCompte(string $numeroUtilisateur, string $nouveauStatut, ?string $raison = null): bool
    {
        $statutsValides = ['actif', 'inactif', 'bloque', 'en_attente_validation', 'archive'];
        if (!in_array($nouveauStatut, $statutsValides)) throw new ValidationException("Statut de compte invalide: '$nouveauStatut'.");

        $user = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['numero_utilisateur', 'statut_compte']);
        if (!$user) throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé.");

        $champsMaj = ['statut_compte' => $nouveauStatut];
        if ($nouveauStatut === 'actif' && ($user['statut_compte'] === 'bloque' || $user['statut_compte'] === 'en_attente_validation')) {
            $champsMaj['tentatives_connexion_echouees'] = 0; $champsMaj['compte_bloque_jusqua'] = null;
        } elseif ($nouveauStatut === 'bloque' && $user['statut_compte'] !== 'bloque' && (empty($raison) || stripos($raison, 'automatique') === false)) {
            $champsMaj['compte_bloque_jusqua'] = null;
        }
        $success = $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, $champsMaj);
        if ($success) $this->journaliserActionAuthentification(null, $numeroUtilisateur, 'AUTH_ACCOUNT_STATUS_CHANGED', 'SUCCES', ['nouveau_statut' => $nouveauStatut, 'ancien_statut' => $user['statut_compte'], 'raison' => $raison]);
        return $success;
    }

    public function verifierRobustesseMotDePasse(string $motDePasse): array
    {
        $erreurs = []; $messages = [];
        if (strlen($motDePasse) < self::PASSWORD_MIN_LENGTH) { $erreurs[] = 'longueur_minimale'; $messages[] = 'Longueur min ' . self::PASSWORD_MIN_LENGTH . ' car.'; }
        if (self::PASSWORD_REQ_UPPERCASE && !preg_match('/[A-Z]/u', $motDePasse)) { $erreurs[] = 'manque_majuscule'; $messages[] = 'Au moins une majuscule.'; }
        if (self::PASSWORD_REQ_LOWERCASE && !preg_match('/[a-z]/u', $motDePasse)) { $erreurs[] = 'manque_minuscule'; $messages[] = 'Au moins une minuscule.'; }
        if (self::PASSWORD_REQ_NUMBER && !preg_match('/[0-9]/u', $motDePasse)) { $erreurs[] = 'manque_chiffre'; $messages[] = 'Au moins un chiffre.'; }
        if (self::PASSWORD_REQ_SPECIAL && !preg_match('/[\W_]/u', $motDePasse)) { $erreurs[] = 'manque_special'; $messages[] = 'Au moins un caractère spécial.'; }
        return ['valide' => empty($erreurs), 'codes_erreur' => $erreurs, 'messages_erreur' => $messages];
    }

    public function demanderReinitialisationMotDePasse(string $emailPrincipal): bool
    {
        $user = $this->utilisateurModel->trouverParEmailPrincipal($emailPrincipal, ['numero_utilisateur', 'statut_compte', 'email_valide', 'login_utilisateur']);
        if (!$user) throw new UtilisateurNonTrouveException("Aucun compte n'est associé à cet email.");
        if ($user['statut_compte'] !== 'actif' || !((bool)$user['email_valide'])) {
            $this->journaliserActionAuthentification(null, $user['numero_utilisateur'], 'AUTH_RESET_PWD_REQUEST_ACCOUNT_INVALID', 'ECHEC', ['statut' => $user['statut_compte'], 'email_valide' => $user['email_valide']]);
            throw new CompteNonValideException("Compte non actif ou email non validé.");
        }
        $tokenData = $this->genererEtStockerTokenPourUtilisateur($user['numero_utilisateur'], 'token_reset_mdp');
        $tokenClair = $tokenData['token_clair']; $appName = $_ENV['APP_NAME'] ?? 'GestionMySoutenance';
        $sujet = "Réinitialisation de mot de passe - {$appName}";
        $urlReset = rtrim(getenv('APP_URL') ?: ($_SERVER['REQUEST_SCHEME'] ?? 'http').'://'.($_SERVER['HTTP_HOST'] ?? 'localhost'), '/') . '/reset-password?token=' . urlencode($tokenClair);
        $corps = "Bonjour ".htmlspecialchars($user['login_utilisateur']).",\n\nCliquez ici pour réinitialiser: ".$urlReset."\nCe lien expire dans ".self::PASSWORD_RESET_TOKEN_EXPIRY_HOURS." heure(s).\nSi non demandé, ignorez.\n\nL'équipe {$appName}";
        try {
            $this->serviceEmail->envoyerEmail($emailPrincipal, $sujet, $corps);
            $this->journaliserActionAuthentification(null, $user['numero_utilisateur'], 'AUTH_RESET_PWD_EMAIL_SENT', 'SUCCES');
            return true;
        } catch (\Exception $e) {
            $this->journaliserActionAuthentification(null, $user['numero_utilisateur'], 'AUTH_RESET_PWD_EMAIL_SEND_ERROR', 'ECHEC', ['erreur' => $e->getMessage()]);
            throw new OperationImpossibleException("Erreur envoi email réinitialisation: " . $e->getMessage(), 0, $e);
        }
    }

    public function validerTokenReinitialisationMotDePasse(string $token): string
    {
        if (empty($token)) throw new TokenInvalideException("Token manquant.");
        $tokenHache = hash('sha256', $token);
        $user = $this->utilisateurModel->trouverUnParCritere(['token_reset_mdp' => $tokenHache], ['numero_utilisateur', 'date_expiration_token_reset']);
        if (!$user) throw new TokenInvalideException("Token invalide ou déjà utilisé.");
        if ($user['date_expiration_token_reset']) {
            $dateExpiration = new DateTimeImmutable($user['date_expiration_token_reset']);
            if (new DateTimeImmutable() > $dateExpiration) {
                $this->utilisateurModel->mettreAJourChamps($user['numero_utilisateur'], ['token_reset_mdp' => null, 'date_expiration_token_reset' => null]);
                throw new TokenExpireException("Token de réinitialisation expiré.");
            }
        } else throw new TokenInvalideException("Token invalide (pas de date d'expiration).");
        return $user['numero_utilisateur'];
    }

    public function reinitialiserMotDePasseApresValidationToken(string $token, string $nouveauMotDePasseClair): bool
    {
        $numeroUtilisateur = $this->validerTokenReinitialisationMotDePasse($token);
        $success = $this->modifierMotDePasse($numeroUtilisateur, $nouveauMotDePasseClair, null, true); // parAdmin=true pour bypasser ancien mdp
        if ($success) {
            $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['token_reset_mdp' => null, 'date_expiration_token_reset' => null]);
            $this->journaliserActionAuthentification($numeroUtilisateur, $numeroUtilisateur, 'AUTH_RESET_PWD_VIA_TOKEN_SUCCESS', 'SUCCES');
        } else $this->journaliserActionAuthentification(null, $numeroUtilisateur, 'AUTH_RESET_PWD_VIA_TOKEN_PWD_CHANGE_FAIL', 'ECHEC');
        return $success;
    }

    public function recupererEmailSourceDuProfil(string $numeroUtilisateur): ?string
    {
        $userBase = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['id_type_utilisateur']);
        if (!$userBase) throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé.");
        $idTypeUtilisateur = $userBase['id_type_utilisateur'];
        $champEmailProfil = $this->getChampEmailProfilParIdType($idTypeUtilisateur);
        if (!$champEmailProfil) return null;
        $tableProfil = $this->getTableProfilParIdType($idTypeUtilisateur);
        if (!$tableProfil) return null;
        $modelProfil = $this->getModelPourTableProfil($tableProfil);
        $profil = $modelProfil->trouverUnParCritere(['numero_utilisateur' => $numeroUtilisateur], [$champEmailProfil]);
        return $profil[$champEmailProfil] ?? null;
    }

    public function estNouveauMotDePasseDansHistorique(string $numeroUtilisateur, string $nouveauMotDePasseClair, int $limiteHistorique = 3): bool
    {
        if ($limiteHistorique <= 0) return false;
        if (!$this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['numero_utilisateur'])) {
            throw new UtilisateurNonTrouveException("Utilisateur '$numeroUtilisateur' non trouvé pour vérif historique MDP.");
        }
        $historiqueHaches = $this->historiqueMotDePasseModel->recupererHistoriquePourUtilisateur($numeroUtilisateur, $limiteHistorique);
        foreach ($historiqueHaches as $enregistrement) {
            if (password_verify($nouveauMotDePasseClair, $enregistrement['mot_de_passe_hache'])) return true;
        }
        return false;
    }

    public function journaliserActionAuthentification(?string $numeroUtilisateurActeur, string $numeroUtilisateurConcerne, string $idActionSysteme, string $resultat, ?array $details = null): void
    {
        if ($numeroUtilisateurActeur === null) {
            $numeroUtilisateurActeur = isset($_SESSION['numero_utilisateur']) ? $_SESSION['numero_utilisateur'] : ('IP:' . ($_SERVER['REMOTE_ADDR'] ?? 'N/A'));
        } elseif ($numeroUtilisateurActeur === 'ADMIN_ACTION' && isset($_SESSION['numero_utilisateur'])) {
            $numeroUtilisateurActeur = $_SESSION['numero_utilisateur']; // L'admin connecté
        }
        $this->serviceSupervision->enregistrerActionSysteme(
            $numeroUtilisateurActeur, $idActionSysteme,
            $_SERVER['REMOTE_ADDR'] ?? 'N/A', $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
            'UTILISATEUR', $numeroUtilisateurConcerne,
            array_merge($details ?? [], ['resultat_auth_svc' => $resultat]), session_id() ?: null
        );
    }

    private function mettreAJourDerniereConnexion(string $numeroUtilisateur): void
    {
        $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['derniere_connexion' => (new DateTimeImmutable())->format('Y-m-d H:i:s')]);
    }

    private function getTableProfilParIdType(?string $idTypeUtilisateur): ?string
    {
        if ($idTypeUtilisateur === ($_ENV['ID_TYPE_ETUDIANT'] ?? 'TYPE_ETUD')) return 'etudiant';
        if ($idTypeUtilisateur === ($_ENV['ID_TYPE_ENSEIGNANT'] ?? 'TYPE_ENS')) return 'enseignant';
        if ($idTypeUtilisateur === ($_ENV['ID_TYPE_PERS_ADMIN'] ?? 'TYPE_PERS_ADMIN')) return 'personnel_administratif';
        if ($idTypeUtilisateur === ($_ENV['ID_TYPE_ADMIN'] ?? 'TYPE_ADMIN')) return null; // Pas de table profil dédiée
        throw new OperationImpossibleException("Table de profil inconnue pour type ID: $idTypeUtilisateur");
    }

    private function getChampEmailProfilParIdType(?string $idTypeUtilisateur): ?string
    {
        if ($idTypeUtilisateur === ($_ENV['ID_TYPE_ETUDIANT'] ?? 'TYPE_ETUD')) return 'email_contact_secondaire'; // Ou email si c'est le principal du profil etudiant
        if ($idTypeUtilisateur === ($_ENV['ID_TYPE_ENSEIGNANT'] ?? 'TYPE_ENS')) return 'email_professionnel';
        if ($idTypeUtilisateur === ($_ENV['ID_TYPE_PERS_ADMIN'] ?? 'TYPE_PERS_ADMIN')) return 'email_professionnel';
        return null;
    }

    private function validerDonneesCreationCompte(array $donneesUtilisateur, array $donneesProfil, string $idTypeUtilisateurProfil): void
    {
        if (empty($donneesUtilisateur['login_utilisateur']) || empty($donneesUtilisateur['mot_de_passe'])) {
            throw new ValidationException("Login et mot de passe sont requis.", ['login_utilisateur' => 'Requis', 'mot_de_passe' => 'Requis']);
        }
        $robustesse = $this->verifierRobustesseMotDePasse($donneesUtilisateur['mot_de_passe']);
        if (!$robustesse['valide']) throw new ValidationException("Mot de passe non robuste.", $robustesse['erreurs'] ?? []);
        $emailProfil = $this->extraireEmailDuProfilConcret($donneesProfil, $idTypeUtilisateurProfil);
        if (empty($emailProfil) || !filter_var($emailProfil, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException("Email du profil requis et doit être valide.", ['email_profil' => "Requis et valide."]);
        }
    }

    private function extraireEmailDuProfilConcret(array $donneesProfil, string $idTypeUtilisateurProfil): ?string
    {
        $champEmail = $this->getChampEmailProfilParIdType($idTypeUtilisateurProfil);
        return ($champEmail && isset($donneesProfil[$champEmail])) ? (string)$donneesProfil[$champEmail] : null;
    }

    private function creerProfilSpecifiqueAssocie(string $numeroUtilisateur, array $donneesProfil, string $idTypeUtilisateur): void
    {
        $tableProfil = $this->getTableProfilParIdType($idTypeUtilisateur);
        if (!$tableProfil) return;
        $modelProfil = $this->getModelPourTableProfil($tableProfil);
        $donneesProfilPourTable = ['numero_utilisateur' => $numeroUtilisateur];

        $pkProfilName = $modelProfil->getClePrimaire();
        if ($pkProfilName !== 'numero_utilisateur' && !isset($donneesProfil[$pkProfilName])) {
            throw new ValidationException("Clé primaire '$pkProfilName' manquante pour le profil $tableProfil.");
        }
        if ($pkProfilName !== 'numero_utilisateur') {
            $donneesProfilPourTable[$pkProfilName] = $donneesProfil[$pkProfilName];
        }

        foreach ($donneesProfil as $champ => $valeur) {
            if ($champ === 'numero_utilisateur' || $champ === $pkProfilName) continue;
            $donneesProfilPourTable[$champ] = ($valeur === '') ? null : $valeur;
        }
        if (!$modelProfil->creer($donneesProfilPourTable)) {
            throw new OperationImpossibleException("Échec de la création du profil spécifique $tableProfil pour '$numeroUtilisateur'.");
        }
    }

    private function getModelPourTableProfil(string $tableProfil): BaseModel
    {
        if ($tableProfil === 'etudiant') return $this->etudiantModel;
        if ($tableProfil === 'enseignant') return $this->enseignantModel;
        if ($tableProfil === 'personnel_administratif') return $this->personnelAdministratifModel;
        throw new OperationImpossibleException("Modèle de profil inconnu pour table: '$tableProfil'.");
    }

    private function ajouterMotDePasseHistorique(string $numeroUtilisateur, string $motDePasseHache): void
    {
        $idHistorique = 'HISTMDP_' . strtoupper(bin2hex(random_bytes(10))); // Ajuster la longueur de l'ID
        $idHistorique = substr($idHistorique, 0, 50); // S'assurer que ça ne dépasse pas VARCHAR(50)

        // Vérifier l'unicité de id_historique_mdp avant insertion (si nécessaire, bien que peu probable avec random_bytes)
        // Pour simplifier, on suppose que c'est assez unique.

        $this->historiqueMotDePasseModel->creer([
            'id_historique_mdp' => $idHistorique,
            'numero_utilisateur' => $numeroUtilisateur,
            'mot_de_passe_hache' => $motDePasseHache,
            'date_changement' => (new DateTimeImmutable())->format('Y-m-d H:i:s')
        ]);
    }

    private function nettoyerHistoriqueMotDePasse(string $numeroUtilisateur): void
    {
        $historique = $this->historiqueMotDePasseModel->recupererHistoriquePourUtilisateur($numeroUtilisateur, self::PASSWORD_HISTORY_LIMIT + 5);
        if (count($historique) > self::PASSWORD_HISTORY_LIMIT) {
            $idsASupprimer = array_map(fn($entry) => $entry['id_historique_mdp'], array_slice($historique, self::PASSWORD_HISTORY_LIMIT));
            if (!empty($idsASupprimer)) $this->historiqueMotDePasseModel->supprimerPlusieursParIdentifiants($idsASupprimer);
        }
    }

    private function genererEtStockerTokenPourUtilisateur(string $numeroUtilisateur, string $nomChampTokenDb): array
    {
        $tokenClair = bin2hex(random_bytes(self::TOKEN_LENGHT_BYTES));
        $tokenHache = hash('sha256', $tokenClair);
        $champsMaj = [$nomChampTokenDb => $tokenHache];

        if ($nomChampTokenDb === 'token_reset_mdp') {
            $dateExpiration = (new DateTimeImmutable())->add(new DateInterval('PT' . self::PASSWORD_RESET_TOKEN_EXPIRY_HOURS . 'H'));
            $champsMaj['date_expiration_token_reset'] = $dateExpiration->format('Y-m-d H:i:s');
        } elseif ($nomChampTokenDb === 'token_validation_email') {
            // Pas de date d'expiration dans la BDD pour ce token
        } else throw new OperationImpossibleException("Type de token inconnu : $nomChampTokenDb");

        if (!$this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, $champsMaj)) {
            throw new OperationImpossibleException("Stockage token '$nomChampTokenDb' échoué pour '$numeroUtilisateur'.");
        }
        return ['token_clair' => $tokenClair, 'token_hache' => $tokenHache];
    }

    private function getDefaultGroupIdForTypeId(string $idTypeUtilisateur): string
    {
        $typeUtilisateur = $this->typeUtilisateurModel->trouverParIdentifiant($idTypeUtilisateur);
        if(!$typeUtilisateur) throw new UtilisateurNonTrouveException("Type utilisateur ID '$idTypeUtilisateur' non trouvé.");

        $mapTypeLibelleToGroupeId = [
            'Etudiant' => $_ENV['ID_GROUPE_ETUDIANT_DEFAUT'] ?? 'GRP_ETUDIANT',
            'Enseignant' => $_ENV['ID_GROUPE_ENSEIGNANT_DEFAUT'] ?? 'GRP_ENSEIGNANT',
            'Personnel Administratif' => $_ENV['ID_GROUPE_PERS_ADMIN_DEFAUT'] ?? 'GRP_PERS_ADMIN',
            'Administrateur' => $_ENV['ID_GROUPE_ADMIN_SYS_DEFAUT'] ?? 'GRP_ADMIN_SYS'
        ];
        $libelleType = $typeUtilisateur['libelle_type_utilisateur'];
        $idGroupeDefaut = $mapTypeLibelleToGroupeId[$libelleType] ?? ($_ENV['ID_GROUPE_UTILISATEUR_STANDARD_DEFAUT'] ?? 'GRP_UTILISATEUR_STANDARD');

        if(!$this->groupeUtilisateurModel->trouverParIdentifiant($idGroupeDefaut)) {
            throw new OperationImpossibleException("Groupe par défaut ID '$idGroupeDefaut' pour type '$libelleType' non trouvé.");
        }
        return $idGroupeDefaut;
    }

    private function getDefaultNiveauAccesId(): string
    {
        $idNiveauDefaut = $_ENV['ID_NIVEAU_ACCES_DEFAUT'] ?? 'ACCES_RESTREINT';
        if(!$this->niveauAccesDonneModel->trouverParIdentifiant($idNiveauDefaut)) {
            throw new OperationImpossibleException("Niveau d'accès par défaut ID '$idNiveauDefaut' non trouvé.");
        }
        return $idNiveauDefaut;
    }
}