<?php

namespace App\Backend\Service\Authentication;

use PDO;
use PDOException;
use DateTime;
use DateInterval;
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

class ServiceAuthentification implements ServiceAuthenticationInterface
{
    private PDO $db;
    private ServiceEmailInterface $serviceEmail;
    private ServiceSupervisionAdminInterface $serviceSupervision;
    private ServiceGestionAcademiqueInterface $serviceGestionAcademique;
    private ServicePermissionsInterface $servicePermissions;

    private const MAX_LOGIN_ATTEMPTS = 5;
    private const ACCOUNT_LOCKOUT_DURATION = 'PT15M';
    private const PASSWORD_RESET_TOKEN_EXPIRY_HOURS = 1;
    private const EMAIL_VALIDATION_TOKEN_EXPIRY_DAYS = 2;
    private const PASSWORD_HISTORY_LIMIT = 5;
    private const TOKEN_LENGHT_BYTES = 32;

    private const PASSWORD_MIN_LENGTH = 8;
    private const PASSWORD_REQ_UPPERCASE = true;
    private const PASSWORD_REQ_LOWERCASE = true;
    private const PASSWORD_REQ_NUMBER = true;
    private const PASSWORD_REQ_SPECIAL = true;

    public function __construct(
        PDO $db,
        ServiceEmailInterface $serviceEmail,
        ServiceSupervisionAdminInterface $serviceSupervision,
        ServiceGestionAcademiqueInterface $serviceGestionAcademique,
        ServicePermissionsInterface $servicePermissions
    ) {
        $this->db = $db;
        $this->serviceEmail = $serviceEmail;
        $this->serviceSupervision = $serviceSupervision;
        $this->serviceGestionAcademique = $serviceGestionAcademique;
        $this->servicePermissions = $servicePermissions;
    }

    public function tenterConnexion(string $identifiant, string $motDePasse): ?object
    {
        $utilisateurBase = $this->recupererUtilisateurBaseParIdentifiant($identifiant);

        if (!$utilisateurBase) {
            $this->journaliserActionAuthentification($identifiant, 'TENTATIVE_CONNEXION_IDENTIFIANT_INCONNU', 'ECHEC', ['identifiant' => $identifiant]);
            throw new UtilisateurNonTrouveException("Identifiant ou mot de passe incorrect.");
        }

        if ($this->estCompteActuellementBloque($utilisateurBase['numero_utilisateur'])) {
            $this->journaliserActionAuthentification($utilisateurBase['numero_utilisateur'], 'TENTATIVE_CONNEXION_COMPTE_BLOQUE', 'ECHEC');
            throw new CompteBloqueException("Ce compte est temporairement bloqué. Veuillez réessayer plus tard.");
        }

        if (!password_verify($motDePasse, $utilisateurBase['mot_de_passe'])) {
            $this->traiterTentativeConnexionEchouee($utilisateurBase['numero_utilisateur']);
            $this->journaliserActionAuthentification($utilisateurBase['numero_utilisateur'], 'TENTATIVE_CONNEXION_MDP_INCORRECT', 'ECHEC');
            throw new IdentifiantsInvalidesException("Identifiant ou mot de passe incorrect.");
        }

        if ($utilisateurBase['statut_compte'] !== 'actif') {
            if ($utilisateurBase['statut_compte'] === 'en_attente_validation' && !$utilisateurBase['email_valide']) {
                $this->journaliserActionAuthentification($utilisateurBase['numero_utilisateur'], 'TENTATIVE_CONNEXION_EMAIL_NON_VALIDE', 'ECHEC');
                throw new CompteNonValideException("Votre compte n'a pas encore été validé. Veuillez vérifier vos emails.");
            }
            $this->journaliserActionAuthentification($utilisateurBase['numero_utilisateur'], 'TENTATIVE_CONNEXION_COMPTE_NON_ACTIF', 'ECHEC', ['statut' => $utilisateurBase['statut_compte']]);
            throw new CompteNonValideException("Ce compte n'est pas actif. Statut: " . $utilisateurBase['statut_compte']);
        }

        if (!$utilisateurBase['email_valide']) {
            $this->journaliserActionAuthentification($utilisateurBase['numero_utilisateur'], 'TENTATIVE_CONNEXION_EMAIL_NON_VALIDE', 'ECHEC');
            throw new CompteNonValideException("L'adresse email associée à ce compte n'a pas été validée.");
        }

        if ($utilisateurBase['preferences_2fa_active']) {
            $_SESSION['2fa_user_num_pending_verification'] = $utilisateurBase['numero_utilisateur'];
            $_SESSION['2fa_authentication_pending'] = true;
            $this->journaliserActionAuthentification($utilisateurBase['numero_utilisateur'], 'CONNEXION_2FA_REQUISE', 'INFO');
            throw new AuthenticationException("Authentification à deux facteurs requise.", 1001);
        }

        $this->reinitialiserTentativesConnexion($utilisateurBase['numero_utilisateur']);
        $this->mettreAJourDerniereConnexion($utilisateurBase['numero_utilisateur']);

        $utilisateurComplet = $this->recupererUtilisateurCompletParNumero($utilisateurBase['numero_utilisateur']);
        if ($utilisateurComplet) {
            $this->journaliserActionAuthentification($utilisateurBase['numero_utilisateur'], 'CONNEXION_REUSSIE', 'SUCCES');
        }
        return $utilisateurComplet;
    }

    public function traiterTentativeConnexionEchouee(string $identifiant): void
    {
        $user = $this->recupererUtilisateurBaseParIdentifiant($identifiant);
        if (!$user) {
            throw new UtilisateurNonTrouveException("Utilisateur non trouvé pour l'identifiant: " . $identifiant);
        }
        $numeroUtilisateur = $user['numero_utilisateur'];

        $stmt = $this->db->prepare("UPDATE utilisateur SET tentatives_connexion_echouees = tentatives_connexion_echouees + 1 WHERE numero_utilisateur = :num");
        $stmt->bindParam(':num', $numeroUtilisateur);
        $stmt->execute();

        $stmt = $this->db->prepare("SELECT tentatives_connexion_echouees FROM utilisateur WHERE numero_utilisateur = :num");
        $stmt->bindParam(':num', $numeroUtilisateur);
        $stmt->execute();
        $tentatives = $stmt->fetchColumn();

        if ($tentatives >= self::MAX_LOGIN_ATTEMPTS) {
            $dateBlocage = (new DateTime())->add(DateInterval::createFromDateString(self::ACCOUNT_LOCKOUT_DURATION_STRING_EQUIVALENT()));
            $stmtUpdate = $this->db->prepare("UPDATE utilisateur SET compte_bloque_jusqua = :dateBlocage, statut_compte = 'bloque' WHERE numero_utilisateur = :num");
            $stmtUpdate->bindValue(':dateBlocage', $dateBlocage->format('Y-m-d H:i:s'));
            $stmtUpdate->bindParam(':num', $numeroUtilisateur);
            $stmtUpdate->execute();
            $this->journaliserActionAuthentification($numeroUtilisateur, 'COMPTE_BLOQUE_TENTATIVES_MAX', 'ALERTE');
        }
    }

    private static function ACCOUNT_LOCKOUT_DURATION_STRING_EQUIVALENT(): string
    {
        if (self::ACCOUNT_LOCKOUT_DURATION === 'PT15M') return '15 minutes';
        if (self::ACCOUNT_LOCKOUT_DURATION === 'PT1H') return '1 hour';
        return '15 minutes';
    }

    public function reinitialiserTentativesConnexion(string $numeroUtilisateur): void
    {
        $user = $this->recupererUtilisateurBaseParNumero($numeroUtilisateur);
        if (!$user) {
            throw new UtilisateurNonTrouveException("Utilisateur non trouvé: " . $numeroUtilisateur);
        }
        $stmt = $this->db->prepare("UPDATE utilisateur SET tentatives_connexion_echouees = 0, compte_bloque_jusqua = NULL WHERE numero_utilisateur = :num");
        $stmt->bindParam(':num', $numeroUtilisateur);
        if (!$stmt->execute()) {
            throw new OperationImpossibleException("Échec de la réinitialisation des tentatives pour " . $numeroUtilisateur);
        }
    }

    public function estCompteActuellementBloque(string $numeroUtilisateur): bool
    {
        $stmt = $this->db->prepare("SELECT compte_bloque_jusqua, statut_compte FROM utilisateur WHERE numero_utilisateur = :num");
        $stmt->bindParam(':num', $numeroUtilisateur);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new UtilisateurNonTrouveException("Utilisateur non trouvé: " . $numeroUtilisateur);
        }

        if ($result['statut_compte'] === 'bloque' && $result['compte_bloque_jusqua']) {
            $dateBlocageFin = new DateTime($result['compte_bloque_jusqua']);
            if (new DateTime() < $dateBlocageFin) {
                return true;
            } else {
                $this->reinitialiserTentativesConnexion($numeroUtilisateur);
                $this->changerStatutDuCompte($numeroUtilisateur, 'actif');
                return false;
            }
        }
        return false;
    }

    public function genererEtStockerSecret2FA(string $numeroUtilisateur): string
    {
        $user = $this->recupererUtilisateurBaseParNumero($numeroUtilisateur);
        if (!$user) {
            throw new UtilisateurNonTrouveException("Utilisateur non trouvé: " . $numeroUtilisateur);
        }
        $secretBytes = random_bytes(20);
        $secretBase32 = $this->base32Encode($secretBytes);

        $stmt = $this->db->prepare("UPDATE utilisateur SET secret_2fa = :secret WHERE numero_utilisateur = :num");
        $stmt->bindParam(':secret', $secretBase32);
        $stmt->bindParam(':num', $numeroUtilisateur);
        if (!$stmt->execute()) {
            throw new OperationImpossibleException("Impossible de stocker le secret 2FA pour " . $numeroUtilisateur);
        }
        $this->journaliserActionAuthentification($numeroUtilisateur, 'GENERATION_SECRET_2FA', 'SUCCES');
        $email = $user['email_principal'] ?? $user['login_utilisateur'];
        return "otpauth://totp/GestionMySoutenance:" . rawurlencode($email) . "?secret=" . $secretBase32 . "&issuer=GestionMySoutenance";
    }

    public function activerAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTPVerifie): bool
    {
        $user = $this->recupererUtilisateurBaseParNumero($numeroUtilisateur);
        if (!$user) {
            throw new UtilisateurNonTrouveException("Utilisateur non trouvé: " . $numeroUtilisateur);
        }
        if (!$user['secret_2fa']) {
            throw new OperationImpossibleException("Secret 2FA non configuré pour l'utilisateur.");
        }

        if ($this->verifierCodeTOTPInterne($user['secret_2fa'], $codeTOTPVerifie)) {
            $stmt = $this->db->prepare("UPDATE utilisateur SET preferences_2fa_active = TRUE WHERE numero_utilisateur = :num");
            $stmt->bindParam(':num', $numeroUtilisateur);
            $success = $stmt->execute();
            if ($success) {
                $this->journaliserActionAuthentification($numeroUtilisateur, 'ACTIVATION_2FA', 'SUCCES');
            }
            return $success;
        }
        $this->journaliserActionAuthentification($numeroUtilisateur, 'ACTIVATION_2FA_CODE_INVALIDE', 'ECHEC');
        throw new MotDePasseInvalideException("Code TOTP invalide.");
    }

    public function verifierCodeAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTP): bool
    {
        $user = $this->recupererUtilisateurBaseParNumero($numeroUtilisateur);
        if (!$user) {
            throw new UtilisateurNonTrouveException("Utilisateur non trouvé: " . $numeroUtilisateur);
        }
        if (!$user['preferences_2fa_active'] || !$user['secret_2fa']) {
            $this->journaliserActionAuthentification($numeroUtilisateur, 'VERIFICATION_2FA_NON_ACTIVE_OU_SECRET_MANQUANT', 'ECHEC');
            throw new OperationImpossibleException("2FA non active ou secret non configuré.");
        }
        $isValid = $this->verifierCodeTOTPInterne($user['secret_2fa'], $codeTOTP);
        if ($isValid) {
            unset($_SESSION['2fa_authentication_pending']);
            unset($_SESSION['2fa_user_num_pending_verification']);
            $this->reinitialiserTentativesConnexion($numeroUtilisateur);
            $this->mettreAJourDerniereConnexion($numeroUtilisateur);
            $this->journaliserActionAuthentification($numeroUtilisateur, 'VERIFICATION_2FA_REUSSIE', 'SUCCES');
        } else {
            $this->journaliserActionAuthentification($numeroUtilisateur, 'VERIFICATION_2FA_ECHOUEE', 'ECHEC');
        }
        return $isValid;
    }

    public function desactiverAuthentificationDeuxFacteurs(string $numeroUtilisateur): bool
    {
        $user = $this->recupererUtilisateurBaseParNumero($numeroUtilisateur);
        if (!$user) {
            throw new UtilisateurNonTrouveException("Utilisateur non trouvé: " . $numeroUtilisateur);
        }
        $stmt = $this->db->prepare("UPDATE utilisateur SET preferences_2fa_active = FALSE, secret_2fa = NULL WHERE numero_utilisateur = :num");
        $stmt->bindParam(':num', $numeroUtilisateur);
        $success = $stmt->execute();
        if ($success) {
            $this->journaliserActionAuthentification($numeroUtilisateur, 'DESACTIVATION_2FA', 'SUCCES');
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
        if (!isset($_SESSION['numero_utilisateur'])) {
            return false;
        }
        $sessionTimeout = 3600;
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $sessionTimeout)) {
            $this->terminerSessionUtilisateur();
            return false;
        }
        $_SESSION['last_activity'] = time();
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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $numUser = $_SESSION['numero_utilisateur'] ?? 'N/A_DECONNEXION';
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        $this->journaliserActionAuthentification($numUser, 'DECONNEXION_SESSION', 'SUCCES');
    }

    public function creerCompteUtilisateurComplet(array $donneesUtilisateur, array $donneesProfil, string $typeProfil, bool $envoyerEmailValidation = true): ?string
    {
        $this->validerDonneesCreationCompte($donneesUtilisateur, $donneesProfil, $typeProfil);

        $numeroUtilisateur = $this->genererNumeroUtilisateurUniqueNonSequentiel();
        $motDePasseHache = password_hash($donneesUtilisateur['mot_de_passe'], PASSWORD_ARGON2ID ?: PASSWORD_DEFAULT);

        $emailProfil = $this->extraireEmailDuProfil($donneesProfil, $typeProfil);
        if (!$emailProfil || !filter_var($emailProfil, FILTER_VALIDATE_EMAIL)) {
            throw new EmailNonValideException("L'email fourni pour le profil est invalide.");
        }
        if ($this->emailPrincipalExisteDeja($emailProfil)) {
            throw new EmailNonValideException("L'email '$emailProfil' est déjà utilisé par un autre compte.");
        }


        $idTypeUtilisateur = $this->getIdTypeUtilisateurParNom($typeProfil);
        if ($idTypeUtilisateur === null) {
            throw new OperationImpossibleException("Type de profil inconnu: " . $typeProfil);
        }

        if ($typeProfil === 'etudiant') {
            if (!isset($donneesProfil['numero_carte_etudiant']) || !isset($donneesProfil['annee_academique_inscription'])) {
                throw new ValidationException("Numéro de carte étudiant et année d'inscription sont requis pour un étudiant.");
            }
            $statutScolarite = $this->serviceGestionAcademique->verifierStatutScolariteEtudiant($donneesProfil['numero_carte_etudiant'], (int)$donneesProfil['annee_academique_inscription']);
            if (!$statutScolarite || !$statutScolarite['eligible_creation_compte']) {
                throw new OperationImpossibleException("L'étudiant n'est pas éligible à la création de compte selon son statut de scolarité.");
            }
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO utilisateur (numero_utilisateur, login_utilisateur, mot_de_passe, id_type_utilisateur, id_groupe_utilisateur, email_principal, statut_compte, date_creation, photo_profil) 
                 VALUES (:num, :login, :mdp, :id_type, :id_groupe, :email_p, :statut, NOW(), :photo)"
            );
            $idGroupe = $donneesUtilisateur['id_groupe_utilisateur'] ?? $this->getDefaultGroupIdForType($typeProfil);

            $stmt->bindParam(':num', $numeroUtilisateur);
            $stmt->bindParam(':login', $donneesUtilisateur['login_utilisateur']);
            $stmt->bindParam(':mdp', $motDePasseHache);
            $stmt->bindParam(':id_type', $idTypeUtilisateur, PDO::PARAM_INT);
            $stmt->bindParam(':id_groupe', $idGroupe, PDO::PARAM_INT);
            $stmt->bindParam(':email_p', $emailProfil);
            $stmt->bindValue(':statut', 'en_attente_validation');
            $stmt->bindValue(':photo', $donneesUtilisateur['photo_profil'] ?? null);
            $stmt->execute();

            $this->creerProfilSpecifique($numeroUtilisateur, $donneesProfil, $typeProfil);

            $this->ajouterMotDePasseHistorique($numeroUtilisateur, $motDePasseHache);

            $tokenValidation = null;
            if ($envoyerEmailValidation) {
                $tokenValidationData = $this->genererEtStockerTokenValidationEmail($numeroUtilisateur);
                $tokenValidation = $tokenValidationData['token_clair'];
            }

            $this->db->commit();

            if ($envoyerEmailValidation && $tokenValidation) {
                $this->envoyerEmailValidationCompte($numeroUtilisateur, $emailProfil, $tokenValidation);
            }
            $this->journaliserActionAuthentification($numeroUtilisateur, 'CREATION_COMPTE_' . strtoupper($typeProfil), 'SUCCES');
            return $numeroUtilisateur;

        } catch (PDOException $e) {
            $this->db->rollBack();
            $this->journaliserActionAuthentification($numeroUtilisateur ?: 'N/A_CREATION_ECHEC', 'CREATION_COMPTE_ECHEC_DB', 'ECHEC', ['erreur' => $e->getMessage()]);
            if ((int)$e->getCode() === 23000) { // Integrity constraint violation
                if (strpos($e->getMessage(), 'utilisateur.login_utilisateur') !== false) {
                    throw new ValidationException("Ce login utilisateur est déjà utilisé.", [], 0, $e);
                }
                if (strpos($e->getMessage(), 'utilisateur.email_principal') !== false) {
                    throw new EmailNonValideException("Cet email principal est déjà utilisé.", 0, $e);
                }
            }
            throw new OperationImpossibleException("Erreur lors de la création du compte: " . $e->getMessage(), 0, $e);
        }
    }

    public function genererNumeroUtilisateurUniqueNonSequentiel(): string
    {
        do {
            $suffix = bin2hex(random_bytes(6));
            $prefix = 'U' . date('y');
            $numero = $prefix . strtoupper($suffix);
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM utilisateur WHERE numero_utilisateur = :num");
            $stmt->bindParam(':num', $numero);
            $stmt->execute();
        } while ($stmt->fetchColumn() > 0);
        return $numero;
    }

    public function envoyerEmailValidationCompte(string $numeroUtilisateur, string $emailPrincipal, string $tokenValidation): void
    {
        $sujet = "Validation de votre compte GestionMySoutenance";
        $urlValidation = ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/validate-email?token=' . $tokenValidation;
        $corps = "Bonjour,\n\nVeuillez cliquer sur le lien suivant pour valider votre compte : " . $urlValidation . "\n\nCe lien expirera dans " . self::EMAIL_VALIDATION_TOKEN_EXPIRY_DAYS . " jours.\n\nCordialement,\nL'équipe GestionMySoutenance";

        try {
            $this->serviceEmail->envoyerEmail($emailPrincipal, $sujet, $corps);
            $this->journaliserActionAuthentification($numeroUtilisateur, 'ENVOI_EMAIL_VALIDATION_COMPTE', 'SUCCES');
        } catch (\Exception $e) {
            $this->journaliserActionAuthentification($numeroUtilisateur, 'ENVOI_EMAIL_VALIDATION_COMPTE_ECHEC', 'ECHEC', ['erreur' => $e->getMessage()]);
            throw new OperationImpossibleException("Erreur lors de l'envoi de l'email de validation: " . $e->getMessage(), 0, $e);
        }
    }

    public function validerCompteEmailViaToken(string $tokenValidation): bool
    {
        $tokenHache = hash('sha256', $tokenValidation);
        $stmt = $this->db->prepare("SELECT numero_utilisateur, date_expiration_token_reset FROM utilisateur WHERE token_validation_email = :token_hache AND email_valide = FALSE");
        $stmt->bindParam(':token_hache', $tokenHache);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            throw new TokenInvalideException("Token de validation invalide ou compte déjà validé.");
        }

        if ($user['date_expiration_token_reset']) {
            $dateExpiration = new DateTime($user['date_expiration_token_reset']);
            if (new DateTime() > $dateExpiration) {
                $this->db->prepare("UPDATE utilisateur SET token_validation_email = NULL, date_expiration_token_reset = NULL WHERE numero_utilisateur = :num")->execute([':num' => $user['numero_utilisateur']]);
                throw new TokenExpireException("Le token de validation a expiré.");
            }
        }

        $stmtUpdate = $this->db->prepare("UPDATE utilisateur SET email_valide = TRUE, statut_compte = 'actif', token_validation_email = NULL, date_expiration_token_reset = NULL WHERE numero_utilisateur = :num");
        $stmtUpdate->bindParam(':num', $user['numero_utilisateur']);
        $success = $stmtUpdate->execute();
        if ($success) {
            $this->journaliserActionAuthentification($user['numero_utilisateur'], 'VALIDATION_EMAIL_TOKEN_REUSSIE', 'SUCCES');
        }
        return $success;
    }

    public function recupererUtilisateurCompletParNumero(string $numeroUtilisateur): ?object
    {
        $userBase = $this->recupererUtilisateurBaseParNumero($numeroUtilisateur);
        if (!$userBase) return null;
        return $this->construireObjetUtilisateurComplet($userBase);
    }

    public function recupererUtilisateurCompletParEmailPrincipal(string $emailPrincipal): ?object
    {
        $stmt = $this->db->prepare("SELECT * FROM utilisateur WHERE email_principal = :email");
        $stmt->bindParam(':email', $emailPrincipal);
        $stmt->execute();
        $userBase = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$userBase) return null;
        return $this->construireObjetUtilisateurComplet($userBase);
    }

    public function recupererUtilisateurCompletParLogin(string $login): ?object
    {
        $stmt = $this->db->prepare("SELECT * FROM utilisateur WHERE login_utilisateur = :login");
        $stmt->bindParam(':login', $login);
        $stmt->execute();
        $userBase = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$userBase) return null;
        return $this->construireObjetUtilisateurComplet($userBase);
    }

    public function listerUtilisateursAvecProfils(array $criteres = [], int $page = 1, int $elementsParPage = 25): array
    {
        $offset = ($page - 1) * $elementsParPage;
        $sqlBase = "FROM utilisateur u";
        $whereClauses = [];
        $params = [];

        if (!empty($criteres['statut_compte'])) {
            $whereClauses[] = "u.statut_compte = :statut_compte";
            $params[':statut_compte'] = $criteres['statut_compte'];
        }
        if (!empty($criteres['id_type_utilisateur'])) {
            $whereClauses[] = "u.id_type_utilisateur = :id_type_utilisateur";
            $params[':id_type_utilisateur'] = (int)$criteres['id_type_utilisateur'];
        }
        if (!empty($criteres['id_groupe_utilisateur'])) {
            $whereClauses[] = "u.id_groupe_utilisateur = :id_groupe_utilisateur";
            $params[':id_groupe_utilisateur'] = (int)$criteres['id_groupe_utilisateur'];
        }

        if (!empty($criteres['recherche_generale'])) {
            $searchTerm = '%' . $criteres['recherche_generale'] . '%';
            $whereClauses[] = "(u.login_utilisateur LIKE :recherche OR u.email_principal LIKE :recherche OR u.numero_utilisateur LIKE :recherche)";
            $params[':recherche'] = $searchTerm;
        }

        $sqlWhere = "";
        if (!empty($whereClauses)) {
            $sqlWhere = " WHERE " . implode(" AND ", $whereClauses);
        }

        $sqlCount = "SELECT COUNT(DISTINCT u.numero_utilisateur) " . $sqlBase . $sqlWhere;
        $stmtCount = $this->db->prepare($sqlCount);
        $stmtCount->execute($params);
        $totalElements = $stmtCount->fetchColumn();

        $sqlSelect = "SELECT u.* " . $sqlBase . $sqlWhere . " ORDER BY u.date_creation DESC LIMIT :limit OFFSET :offset";
        $stmtSelect = $this->db->prepare($sqlSelect);
        foreach ($params as $key => $value) {
            $stmtSelect->bindValue($key, $value);
        }
        $stmtSelect->bindParam(':limit', $elementsParPage, PDO::PARAM_INT);
        $stmtSelect->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmtSelect->execute();

        $utilisateursBruts = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);
        $utilisateursComplets = [];
        foreach ($utilisateursBruts as $userBrut) {
            $utilisateursComplets[] = $this->construireObjetUtilisateurComplet($userBrut);
        }

        return ['utilisateurs' => $utilisateursComplets, 'total_elements' => (int)$totalElements];
    }

    public function modifierMotDePasse(string $numeroUtilisateur, string $nouveauMotDePasse, ?string $ancienMotDePasse = null, bool $parAdmin = false): bool
    {
        $user = $this->recupererUtilisateurBaseParNumero($numeroUtilisateur);
        if (!$user) {
            throw new UtilisateurNonTrouveException("Utilisateur non trouvé.");
        }

        if (!$parAdmin) {
            if ($ancienMotDePasse === null || !password_verify($ancienMotDePasse, $user['mot_de_passe'])) {
                $this->journaliserActionAuthentification($numeroUtilisateur, 'MODIF_MDP_ANCIEN_MDP_INCORRECT', 'ECHEC');
                throw new MotDePasseInvalideException("L'ancien mot de passe est incorrect.");
            }
        }

        $robustesse = $this->verifierRobustesseMotDePasse($nouveauMotDePasse);
        if (!$robustesse['valide']) {
            $this->journaliserActionAuthentification($numeroUtilisateur, 'MODIF_MDP_NON_ROBUSTE', 'ECHEC', ['erreurs' => $robustesse['erreurs']]);
            throw new ValidationException("Le nouveau mot de passe n'est pas assez robuste: " . implode(', ', $robustesse['erreurs']));
        }

        $nouveauMotDePasseHache = password_hash($nouveauMotDePasse, PASSWORD_ARGON2ID ?: PASSWORD_DEFAULT);

        if ($this->estMotDePasseDansHistorique($numeroUtilisateur, $nouveauMotDePasseHache, self::PASSWORD_HISTORY_LIMIT)) {
            $this->journaliserActionAuthentification($numeroUtilisateur, 'MODIF_MDP_DANS_HISTORIQUE', 'ECHEC');
            throw new MotDePasseInvalideException("Le nouveau mot de passe a déjà été utilisé récemment.");
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("UPDATE utilisateur SET mot_de_passe = :mdp WHERE numero_utilisateur = :num");
            $stmt->bindParam(':mdp', $nouveauMotDePasseHache);
            $stmt->bindParam(':num', $numeroUtilisateur);
            $stmt->execute();

            $this->ajouterMotDePasseHistorique($numeroUtilisateur, $nouveauMotDePasseHache);
            $this->nettoyerHistoriqueMotDePasse($numeroUtilisateur);

            $this->db->commit();
            $this->journaliserActionAuthentification($numeroUtilisateur, 'MODIF_MDP_REUSSIE', 'SUCCES', ['par_admin' => $parAdmin]);
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            $this->journaliserActionAuthentification($numeroUtilisateur, 'MODIF_MDP_ECHEC_DB', 'ECHEC', ['erreur' => $e->getMessage()]);
            throw new OperationImpossibleException("Erreur lors de la modification du mot de passe: " . $e->getMessage(), 0, $e);
        }
    }

    public function mettreAJourProfilUtilisateur(string $numeroUtilisateur, string $typeProfil, array $donneesProfil): bool
    {
        $userBase = $this->recupererUtilisateurBaseParNumero($numeroUtilisateur);
        if (!$userBase) {
            throw new UtilisateurNonTrouveException("Utilisateur non trouvé.");
        }

        $idTypeProfilAttendu = $this->getIdTypeUtilisateurParNom($typeProfil);
        if ($userBase['id_type_utilisateur'] != $idTypeProfilAttendu) {
            throw new OperationImpossibleException("Le type de profil fourni ne correspond pas à l'utilisateur.");
        }

        $tableProfil = $this->getTableProfilParIdType($userBase['id_type_utilisateur']);
        if (!$tableProfil) {
            throw new OperationImpossibleException("Type de profil inconnu pour la mise à jour.");
        }

        $setClauses = [];
        $params = [':num_user_key' => $numeroUtilisateur];
        $nouvelEmailProfil = null;
        $emailProfilChampNom = $this->getChampEmailProfilParIdType($userBase['id_type_utilisateur']);


        foreach ($donneesProfil as $champ => $valeur) {
            if ($this->estChampValidePourProfil($tableProfil, $champ)) {
                $setClauses[] = "$champ = :$champ";
                $params[":$champ"] = $valeur;
                if ($champ === $emailProfilChampNom) {
                    $nouvelEmailProfil = $valeur;
                }
            }
        }

        if (empty($setClauses)) {
            return true;
        }

        $this->db->beginTransaction();
        try {
            $sql = "UPDATE " . $tableProfil . " SET " . implode(', ', $setClauses) . " WHERE numero_utilisateur = :num_user_key";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            if ($nouvelEmailProfil !== null && $nouvelEmailProfil !== $userBase['email_principal']) {
                if (!filter_var($nouvelEmailProfil, FILTER_VALIDATE_EMAIL)) {
                    throw new EmailNonValideException("Le nouvel email de profil '$nouvelEmailProfil' est invalide.");
                }
                if ($this->emailPrincipalExisteDeja($nouvelEmailProfil, $numeroUtilisateur)) {
                    throw new EmailNonValideException("Ce nouvel email '$nouvelEmailProfil' est déjà utilisé par un autre compte.");
                }
                $stmtUser = $this->db->prepare("UPDATE utilisateur SET email_principal = :email_p, email_valide = FALSE, token_validation_email = NULL, date_expiration_token_reset = NULL WHERE numero_utilisateur = :num_user_key_update");
                $stmtUser->bindParam(':email_p', $nouvelEmailProfil);
                $stmtUser->bindParam(':num_user_key_update', $numeroUtilisateur);
                $stmtUser->execute();

                $tokenData = $this->genererEtStockerTokenValidationEmail($numeroUtilisateur);
                $this->envoyerEmailValidationCompte($numeroUtilisateur, $nouvelEmailProfil, $tokenData['token_clair']);
                $this->journaliserActionAuthentification($numeroUtilisateur, 'MAJ_PROFIL_NOUVEL_EMAIL_VALIDATION_REQUISE', 'INFO', ['nouvel_email' => $nouvelEmailProfil]);
            }
            $this->db->commit();
            $this->journaliserActionAuthentification($numeroUtilisateur, 'MAJ_PROFIL_UTILISATEUR', 'SUCCES', ['type_profil' => $typeProfil]);
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            $this->journaliserActionAuthentification($numeroUtilisateur, 'MAJ_PROFIL_UTILISATEUR_ECHEC_DB', 'ECHEC', ['erreur' => $e->getMessage()]);
            throw new OperationImpossibleException("Erreur lors de la mise à jour du profil: " . $e->getMessage(), 0, $e);
        }
    }

    public function mettreAJourCompteUtilisateurParAdmin(string $numeroUtilisateur, array $donneesCompte): bool
    {
        $userBase = $this->recupererUtilisateurBaseParNumero($numeroUtilisateur);
        if (!$userBase) {
            throw new UtilisateurNonTrouveException("Utilisateur non trouvé.");
        }

        $setClauses = [];
        $params = [':num_user_key' => $numeroUtilisateur];
        $champsModifiables = ['login_utilisateur', 'id_groupe_utilisateur', 'photo_profil', 'statut_compte'];

        foreach ($donneesCompte as $champ => $valeur) {
            if (in_array($champ, $champsModifiables)) {
                $setClauses[] = "$champ = :$champ";
                $params[":$champ"] = $valeur;
            }
            if ($champ === 'id_type_utilisateur' && $valeur != $userBase['id_type_utilisateur']) {
                throw new OperationImpossibleException("Le changement de type d'utilisateur n'est pas supporté par cette méthode. Utiliser une procédure dédiée.");
            }
        }

        if (empty($setClauses)) {
            return true;
        }

        $sql = "UPDATE utilisateur SET " . implode(', ', $setClauses) . " WHERE numero_utilisateur = :num_user_key";
        $stmt = $this->db->prepare($sql);

        try {
            $success = $stmt->execute($params);
            if ($success) {
                $this->journaliserActionAuthentification($numeroUtilisateur, 'MAJ_COMPTE_UTILISATEUR_PAR_ADMIN', 'SUCCES', ['donnees_modifiees' => array_keys(array_intersect_key($donneesCompte, array_flip($champsModifiables)))]);
            }
            return $success;
        } catch (PDOException $e) {
            if ((int)$e->getCode() === 23000 && strpos($e->getMessage(), 'login_utilisateur') !== false) {
                throw new ValidationException("Ce login utilisateur est déjà utilisé.", [], 0, $e);
            }
            $this->journaliserActionAuthentification($numeroUtilisateur, 'MAJ_COMPTE_UTILISATEUR_PAR_ADMIN_ECHEC_DB', 'ECHEC', ['erreur' => $e->getMessage()]);
            throw new OperationImpossibleException("Erreur lors de la mise à jour du compte: " . $e->getMessage(), 0, $e);
        }
    }

    public function changerStatutDuCompte(string $numeroUtilisateur, string $nouveauStatut, ?string $raison = null): bool
    {
        $statutsValides = ['actif', 'inactif', 'bloque', 'en_attente_validation', 'archive'];
        if (!in_array($nouveauStatut, $statutsValides)) {
            throw new ValidationException("Statut de compte invalide: " . $nouveauStatut);
        }
        $user = $this->recupererUtilisateurBaseParNumero($numeroUtilisateur);
        if (!$user) {
            throw new UtilisateurNonTrouveException("Utilisateur non trouvé: " . $numeroUtilisateur);
        }

        $stmt = $this->db->prepare("UPDATE utilisateur SET statut_compte = :statut WHERE numero_utilisateur = :num");
        $stmt->bindParam(':statut', $nouveauStatut);
        $stmt->bindParam(':num', $numeroUtilisateur);
        $success = $stmt->execute();
        if ($success) {
            $this->journaliserActionAuthentification($numeroUtilisateur, 'CHANGEMENT_STATUT_COMPTE', 'SUCCES', ['nouveau_statut' => $nouveauStatut, 'ancien_statut' => $user['statut_compte'], 'raison' => $raison]);
        }
        return $success;
    }

    public function verifierRobustesseMotDePasse(string $motDePasse): array
    {
        $erreurs = [];
        if (strlen($motDePasse) < self::PASSWORD_MIN_LENGTH) {
            $erreurs[] = 'longueur_minimale (' . self::PASSWORD_MIN_LENGTH . ')';
        }
        if (self::PASSWORD_REQ_UPPERCASE && !preg_match('/[A-Z]/', $motDePasse)) {
            $erreurs[] = 'manque_majuscule';
        }
        if (self::PASSWORD_REQ_LOWERCASE && !preg_match('/[a-z]/', $motDePasse)) {
            $erreurs[] = 'manque_minuscule';
        }
        if (self::PASSWORD_REQ_NUMBER && !preg_match('/[0-9]/', $motDePasse)) {
            $erreurs[] = 'manque_chiffre';
        }
        if (self::PASSWORD_REQ_SPECIAL && !preg_match('/[\W_]/', $motDePasse)) {
            $erreurs[] = 'manque_special';
        }
        return ['valide' => empty($erreurs), 'erreurs' => $erreurs];
    }

    public function demanderReinitialisationMotDePasse(string $emailPrincipal): bool
    {
        $user = $this->recupererUtilisateurBaseParEmailPrincipal($emailPrincipal);
        if (!$user) {
            throw new UtilisateurNonTrouveException("Aucun compte n'est associé à cet email principal.");
        }
        if ($user['statut_compte'] !== 'actif' || !$user['email_valide']) {
            $this->journaliserActionAuthentification($user['numero_utilisateur'], 'DEMANDE_RESET_MDP_COMPTE_INVALIDE', 'ECHEC', ['statut' => $user['statut_compte'], 'email_valide' => $user['email_valide']]);
            throw new CompteNonValideException("Le compte associé à cet email n'est pas actif ou l'email n'est pas validé.");
        }

        $tokenClair = bin2hex(random_bytes(self::TOKEN_LENGHT_BYTES));
        $tokenHache = hash('sha256', $tokenClair);
        $dateExpiration = (new DateTime())->add(new DateInterval('PT' . self::PASSWORD_RESET_TOKEN_EXPIRY_HOURS . 'H'));

        $stmt = $this->db->prepare("UPDATE utilisateur SET token_reset_mdp = :token_hache, date_expiration_token_reset = :date_exp WHERE numero_utilisateur = :num");
        $stmt->bindParam(':token_hache', $tokenHache);
        $stmt->bindValue(':date_exp', $dateExpiration->format('Y-m-d H:i:s'));
        $stmt->bindParam(':num', $user['numero_utilisateur']);

        if (!$stmt->execute()) {
            $this->journaliserActionAuthentification($user['numero_utilisateur'], 'DEMANDE_RESET_MDP_STOCKAGE_TOKEN_ECHEC', 'ECHEC');
            throw new OperationImpossibleException("Erreur lors de la génération du token de réinitialisation.");
        }

        $sujet = "Réinitialisation de votre mot de passe GestionMySoutenance";
        $urlReset = ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/reset-password?token=' . $tokenClair;
        $corps = "Bonjour,\n\nPour réinitialiser votre mot de passe, veuillez cliquer sur le lien suivant : " . $urlReset . "\n\nCe lien expirera dans " . self::PASSWORD_RESET_TOKEN_EXPIRY_HOURS . " heure(s).\nSi vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet email.\n\nCordialement,\nL'équipe GestionMySoutenance";

        try {
            $this->serviceEmail->envoyerEmail($emailPrincipal, $sujet, $corps);
            $this->journaliserActionAuthentification($user['numero_utilisateur'], 'DEMANDE_RESET_MDP_EMAIL_ENVOYE', 'SUCCES');
            return true;
        } catch (\Exception $e) {
            $this->journaliserActionAuthentification($user['numero_utilisateur'], 'DEMANDE_RESET_MDP_EMAIL_ENVOI_ECHEC', 'ECHEC', ['erreur' => $e->getMessage()]);
            throw new OperationImpossibleException("Erreur lors de l'envoi de l'email de réinitialisation: " . $e->getMessage(), 0, $e);
        }
    }

    public function validerTokenReinitialisationMotDePasse(string $token): ?string
    {
        $tokenHache = hash('sha256', $token);
        $stmt = $this->db->prepare("SELECT numero_utilisateur, date_expiration_token_reset FROM utilisateur WHERE token_reset_mdp = :token_hache");
        $stmt->bindParam(':token_hache', $tokenHache);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            throw new TokenInvalideException("Token de réinitialisation invalide.");
        }

        if ($user['date_expiration_token_reset']) {
            $dateExpiration = new DateTime($user['date_expiration_token_reset']);
            if (new DateTime() > $dateExpiration) {
                $this->db->prepare("UPDATE utilisateur SET token_reset_mdp = NULL, date_expiration_token_reset = NULL WHERE numero_utilisateur = :num")->execute([':num' => $user['numero_utilisateur']]);
                throw new TokenExpireException("Le token de réinitialisation a expiré.");
            }
        }
        return $user['numero_utilisateur'];
    }

    public function reinitialiserMotDePasseApresValidationToken(string $token, string $nouveauMotDePasse): bool
    {
        $numeroUtilisateur = $this->validerTokenReinitialisationMotDePasse($token);
        if (!$numeroUtilisateur) {
            throw new TokenInvalideException("Token invalide pour la réinitialisation.");
        }
        $user = $this->recupererUtilisateurBaseParNumero($numeroUtilisateur);
        if (!$user) {
            throw new UtilisateurNonTrouveException("Utilisateur associé au token non trouvé.");
        }


        $success = $this->modifierMotDePasse($numeroUtilisateur, $nouveauMotDePasse, null, true);

        if ($success) {
            $stmt = $this->db->prepare("UPDATE utilisateur SET token_reset_mdp = NULL, date_expiration_token_reset = NULL WHERE numero_utilisateur = :num");
            $stmt->bindParam(':num', $numeroUtilisateur);
            $stmt->execute();
            $this->journaliserActionAuthentification($numeroUtilisateur, 'RESET_MDP_VIA_TOKEN_REUSSI', 'SUCCES');
        } else {
            $this->journaliserActionAuthentification($numeroUtilisateur, 'RESET_MDP_VIA_TOKEN_ECHEC_MODIF', 'ECHEC');
        }
        return $success;
    }

    public function recupererEmailPrincipalPourUtilisateur(string $numeroUtilisateur): ?string
    {
        $userBase = $this->recupererUtilisateurBaseParNumero($numeroUtilisateur);
        if (!$userBase) {
            throw new UtilisateurNonTrouveException("Utilisateur non trouvé: " . $numeroUtilisateur);
        }

        $tableProfil = $this->getTableProfilParIdType($userBase['id_type_utilisateur']);
        $champEmailProfil = $this->getChampEmailProfilParIdType($userBase['id_type_utilisateur']);

        if ($tableProfil && $champEmailProfil) {
            $stmt = $this->db->prepare("SELECT " . $champEmailProfil . " FROM " . $tableProfil . " WHERE numero_utilisateur = :num");
            $stmt->bindParam(':num', $numeroUtilisateur);
            $stmt->execute();
            return $stmt->fetchColumn() ?: null;
        }
        return null;
    }

    public function estMotDePasseDansHistorique(string $numeroUtilisateur, string $nouveauMotDePasseHache, int $limiteHistorique = 5): bool
    {
        if ($limiteHistorique <= 0) return false;
        $user = $this->recupererUtilisateurBaseParNumero($numeroUtilisateur);
        if (!$user) {
            throw new UtilisateurNonTrouveException("Utilisateur non trouvé pour vérification historique mdp.");
        }

        $stmt = $this->db->prepare(
            "SELECT mot_de_passe_hache FROM historique_mot_de_passe 
             WHERE numero_utilisateur = :num 
             ORDER BY date_changement DESC 
             LIMIT :limit"
        );
        $stmt->bindParam(':num', $numeroUtilisateur);
        $stmt->bindParam(':limit', $limiteHistorique, PDO::PARAM_INT);
        $stmt->execute();

        $historiqueHaches = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($historiqueHaches as $mdpHacheHistorique) {
            if ($nouveauMotDePasseHache === $mdpHacheHistorique) {
                return true;
            }
        }
        return false;
    }

    public function journaliserActionAuthentification(string $numeroUtilisateurConcerne, string $libelleAction, string $resultat, ?array $details = null): void
    {
        $acteur = null;
        if (isset($_SESSION['numero_utilisateur'])) {
            $acteur = $_SESSION['numero_utilisateur'];
        } elseif (strpos($libelleAction, 'CONNEXION') === false && strpos($libelleAction, 'RESET_MDP') === false && strpos($libelleAction, 'VALIDATION_EMAIL') === false) {
            $acteur = 'SYSTEME_OU_ANONYME';
        }


        $this->serviceSupervision->enregistrerAction(
            $acteur,
            $libelleAction,
            new DateTime(),
            $_SERVER['REMOTE_ADDR'] ?? 'N/A',
            $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
            'Utilisateur',
            $numeroUtilisateurConcerne,
            array_merge($details ?? [], ['resultat_action' => $resultat])
        );
    }

    private function mettreAJourDerniereConnexion(string $numeroUtilisateur): void
    {
        $stmt = $this->db->prepare("UPDATE utilisateur SET derniere_connexion = NOW() WHERE numero_utilisateur = :num");
        $stmt->bindParam(':num', $numeroUtilisateur);
        $stmt->execute();
    }

    private function getIdTypeUtilisateurParNom(string $typeProfil): ?int
    {
        $map = ['etudiant' => 1, 'enseignant' => 2, 'personnel_administratif' => 3];
        return $map[strtolower($typeProfil)] ?? null;
    }

    private function getDefaultGroupIdForType(string $typeProfil): ?int
    {
        $map = ['etudiant' => 1, 'enseignant' => 2, 'personnel_administratif' => 3];
        return $map[strtolower($typeProfil)] ?? 4;
    }

    private function getTableProfilParIdType(?int $idTypeUtilisateur): ?string
    {
        $map = [1 => 'etudiant', 2 => 'enseignant', 3 => 'personnel_administratif'];
        return $map[$idTypeUtilisateur] ?? null;
    }

    private function getChampEmailProfilParIdType(?int $idTypeUtilisateur): ?string
    {
        $map = [1 => 'email', 2 => 'email_professionnel', 3 => 'email_professionnel'];
        return $map[$idTypeUtilisateur] ?? null;
    }

    private function base32Encode(string $bytes): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $paddingChar = '=';
        $binaryString = '';
        foreach (str_split($bytes) as $char) {
            $binaryString .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $fiveBitGroups = str_split($binaryString, 5);
        $base32 = '';
        foreach ($fiveBitGroups as $group) {
            $group = str_pad($group, 5, '0', STR_PAD_RIGHT);
            $base32 .= $alphabet[bindec($group)];
        }

        $paddingCount = (8 - (strlen($binaryString) % 40 / 5)) % 8;
        if ($paddingCount > 0 && $paddingCount < 8) {
            $base32 .= str_repeat($paddingChar, $paddingCount);
        }
        return $base32;
    }


    private function verifierCodeTOTPInterne(string $secretBase32, string $codeUtilisateur): bool
    {
        return false;
    }

    private function recupererUtilisateurBaseParIdentifiant(string $identifiant): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM utilisateur WHERE login_utilisateur = :identifiant OR email_principal = :identifiant");
        $stmt->bindParam(':identifiant', $identifiant);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    private function recupererUtilisateurBaseParNumero(string $numeroUtilisateur): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM utilisateur WHERE numero_utilisateur = :num");
        $stmt->bindParam(':num', $numeroUtilisateur);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    private function construireObjetUtilisateurComplet(array $utilisateurBase): ?object
    {
        $profil = null;
        $tableProfil = $this->getTableProfilParIdType($utilisateurBase['id_type_utilisateur']);

        if ($tableProfil) {
            $stmtProfil = $this->db->prepare("SELECT * FROM " . $tableProfil . " WHERE numero_utilisateur = :num_user");
            $stmtProfil->bindParam(':num_user', $utilisateurBase['numero_utilisateur']);
            $stmtProfil->execute();
            $profilData = $stmtProfil->fetch(PDO::FETCH_ASSOC);
            if ($profilData) {
                $profil = $profilData;
            }
        }
        $merged = array_merge($utilisateurBase, $profil ?: []);
        return (object) $merged;
    }

    private function validerDonneesCreationCompte(array $donneesUtilisateur, array $donneesProfil, string $typeProfil): void
    {
        if (empty($donneesUtilisateur['login_utilisateur']) || empty($donneesUtilisateur['mot_de_passe'])) {
            throw new ValidationException("Login et mot de passe sont requis.");
        }
        $robustesse = $this->verifierRobustesseMotDePasse($donneesUtilisateur['mot_de_passe']);
        if (!$robustesse['valide']) {
            throw new ValidationException("Mot de passe non robuste: " . implode(', ', $robustesse['erreurs']));
        }
    }

    private function extraireEmailDuProfil(array $donneesProfil, string $typeProfil): ?string
    {
        $idType = $this->getIdTypeUtilisateurParNom($typeProfil);
        $champEmail = $this->getChampEmailProfilParIdType($idType);
        return $donneesProfil[$champEmail] ?? null;
    }

    private function emailPrincipalExisteDeja(string $email, ?string $excludeNumeroUtilisateur = null): bool
    {
        $sql = "SELECT COUNT(*) FROM utilisateur WHERE email_principal = :email";
        $params = [':email' => $email];
        if ($excludeNumeroUtilisateur) {
            $sql .= " AND numero_utilisateur != :num_exclude";
            $params[':num_exclude'] = $excludeNumeroUtilisateur;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    private function creerProfilSpecifique(string $numeroUtilisateur, array $donneesProfil, string $typeProfil): void
    {
        $tableProfil = $this->getTableProfilParIdType($this->getIdTypeUtilisateurParNom($typeProfil));
        if (!$tableProfil) throw new OperationImpossibleException("Type de profil invalide pour création spécifique.");

        $champsProfil = [];
        $placeholdersProfil = [];
        $valeursProfil = [':numero_utilisateur_key' => $numeroUtilisateur];

        $colonnesAttendues = $this->getColonnesAttenduesPourProfil($tableProfil);

        foreach ($colonnesAttendues as $colonne) {
            if ($colonne === 'numero_utilisateur') continue;
            if (!array_key_exists($colonne, $donneesProfil)) {
                if ($this->estColonneNullable($tableProfil, $colonne)) {
                    $donneesProfil[$colonne] = null; // Assigner null si nullable et non fourni
                } else {
                    throw new ValidationException("Champ requis '$colonne' manquant pour le profil $typeProfil.");
                }
            }
            $champsProfil[] = $colonne;
            $placeholdersProfil[] = ":$colonne";
            $valeursProfil[":$colonne"] = $donneesProfil[$colonne];
        }
        $champsProfil[] = 'numero_utilisateur';
        $placeholdersProfil[] = ':numero_utilisateur_key';


        $sql = "INSERT INTO " . $tableProfil . " (" . implode(', ', $champsProfil) . ") VALUES (" . implode(', ', $placeholdersProfil) . ")";
        $stmt = $this->db->prepare($sql);
        if (!$stmt->execute($valeursProfil)) {
            throw new PDOException("Erreur lors de la création du profil " . $typeProfil . " pour " . $numeroUtilisateur . ": " . implode(", ", $stmt->errorInfo()));
        }
    }

    private function getColonnesAttenduesPourProfil(string $tableProfil): array {
        if ($tableProfil === 'etudiant') {
            return ['numero_carte_etudiant', 'nom_etudiant', 'prenom_etudiant', 'date_naissance_etudiant', 'lieu_naissance_etudiant', 'sexe_etudiant', 'nationalite_etudiant', 'adresse_etudiant', 'telephone_etudiant', 'email', 'id_niveau_etude', 'id_specialite', 'annee_academique_inscription', 'numero_utilisateur'];
        } elseif ($tableProfil === 'enseignant') {
            return ['numero_matricule_enseignant', 'nom_enseignant', 'prenom_enseignant', 'grade_enseignant', 'specialite_enseignant', 'telephone_professionnel', 'email_professionnel', 'numero_utilisateur'];
        } elseif ($tableProfil === 'personnel_administratif') {
            return ['numero_matricule_personnel', 'nom_personnel', 'prenom_personnel', 'poste_occupe', 'telephone_professionnel_personnel', 'email_professionnel', 'numero_utilisateur'];
        }
        return [];
    }

    private function estColonneNullable(string $table, string $colonne): bool {
        // Cette méthode devrait idéalement inspecter le schéma de la BDD.
        // Pour simplifier, on hardcode des exemples ou on assume non-nullable par défaut.
        $colonnesNullables = [
            'etudiant' => ['telephone_etudiant', 'adresse_etudiant'],
            'enseignant' => ['telephone_professionnel'],
            'personnel_administratif' => ['telephone_professionnel_personnel']
        ];
        return isset($colonnesNullables[$table]) && in_array($colonne, $colonnesNullables[$table]);
    }


    private function ajouterMotDePasseHistorique(string $numeroUtilisateur, string $motDePasseHache): void
    {
        $stmt = $this->db->prepare("INSERT INTO historique_mot_de_passe (numero_utilisateur, mot_de_passe_hache, date_changement) VALUES (:num, :mdp, NOW())");
        $stmt->bindParam(':num', $numeroUtilisateur);
        $stmt->bindParam(':mdp', $motDePasseHache);
        $stmt->execute();
    }

    private function nettoyerHistoriqueMotDePasse(string $numeroUtilisateur): void
    {
        $stmt = $this->db->prepare("SELECT id_historique_mdp FROM historique_mot_de_passe WHERE numero_utilisateur = :num ORDER BY date_changement DESC");
        $stmt->bindParam(':num', $numeroUtilisateur);
        $stmt->execute();
        $idsHistorique = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (count($idsHistorique) > self::PASSWORD_HISTORY_LIMIT) {
            $idsASupprimer = array_slice($idsHistorique, self::PASSWORD_HISTORY_LIMIT);
            if (!empty($idsASupprimer)) {
                $placeholders = rtrim(str_repeat('?,', count($idsASupprimer)), ',');
                $stmtDelete = $this->db->prepare("DELETE FROM historique_mot_de_passe WHERE id_historique_mdp IN ($placeholders)");
                $stmtDelete->execute($idsASupprimer);
            }
        }
    }

    private function genererEtStockerTokenValidationEmail(string $numeroUtilisateur): array
    {
        $tokenClair = bin2hex(random_bytes(self::TOKEN_LENGHT_BYTES));
        $tokenHache = hash('sha256', $tokenClair);
        $dateExpiration = (new DateTime())->add(new DateInterval('P' . self::EMAIL_VALIDATION_TOKEN_EXPIRY_DAYS . 'D'));

        $stmt = $this->db->prepare("UPDATE utilisateur SET token_validation_email = :token_hache, date_expiration_token_reset = :date_exp WHERE numero_utilisateur = :num");
        $stmt->bindParam(':token_hache', $tokenHache);
        $stmt->bindValue(':date_exp', $dateExpiration->format('Y-m-d H:i:s'));
        $stmt->bindParam(':num', $numeroUtilisateur);
        if (!$stmt->execute()) {
            throw new OperationImpossibleException("Impossible de stocker le token de validation d'email pour " . $numeroUtilisateur);
        }
        return ['token_clair' => $tokenClair, 'token_hache' => $tokenHache];
    }

    private function estChampValidePourProfil(string $tableProfil, string $champ): bool {
        $colonnes = $this->getColonnesAttenduesPourProfil($tableProfil);
        return in_array($champ, $colonnes) && $champ !== 'numero_utilisateur';
    }

}