<?php
namespace App\Backend\Service\Authentication;

use PDO;
use RobThree\Auth\TwoFactorAuth;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\HistoriqueMotDePasse;
use App\Backend\Model\Enseignant;
use App\Backend\Model\Etudiant;
use App\Backend\Model\PersonnelAdministratif;
use App\Backend\Service\Email\ServiceEmailInterface;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGeneratorInterface;
use App\Backend\Service\Permissions\ServicePermissionsInterface;
use App\Backend\Exception\IdentifiantsInvalidesException;
use App\Backend\Exception\CompteBloqueException;
use App\Backend\Exception\CompteNonValideException;
use App\Backend\Exception\EmailException;
use App\Backend\Exception\TokenExpireException;
use App\Backend\Exception\TokenInvalideException;
use App\Backend\Exception\MotDePasseInvalideException;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceAuthentification implements ServiceAuthenticationInterface
{
    private Utilisateur $utilisateurModel;
    private HistoriqueMotDePasse $historiqueMdpModel;
    private Enseignant $enseignantModel;
    private Etudiant $etudiantModel;
    private PersonnelAdministratif $personnelAdminModel;
    private ServiceEmailInterface $emailService;
    private ServiceSupervisionAdminInterface $supervisionService;
    private IdentifiantGeneratorInterface $idGenerator;
    private ServicePermissionsInterface $permissionService;
    private TwoFactorAuth $tfa;

    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_TIME_MINUTES = 15;

    public function __construct(PDO $db, ServiceEmailInterface $emailService, ServiceSupervisionAdminInterface $supervisionService, IdentifiantGeneratorInterface $idGenerator, ServicePermissionsInterface $permissionService)
    {
        $this->utilisateurModel = new Utilisateur($db);
        $this->historiqueMdpModel = new HistoriqueMotDePasse($db);
        $this->enseignantModel = new Enseignant($db);
        $this->etudiantModel = new Etudiant($db);
        $this->personnelAdminModel = new PersonnelAdministratif($db);
        $this->emailService = $emailService;
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
        $this->permissionService = $permissionService;
        $this->tfa = new TwoFactorAuth('GestionMySoutenance');
    }

    public function tenterConnexion(string $identifiant, string $motDePasseClair): array
    {
        $utilisateur = $this->utilisateurModel->trouverUnParCritere(['login_utilisateur' => $identifiant]);
        if (!$utilisateur) {
            $this->supervisionService->enregistrerAction($identifiant, 'ECHEC_LOGIN', "Utilisateur non trouvé");
            throw new IdentifiantsInvalidesException("Identifiants de connexion invalides.");
        }

        if ($this->estCompteActuellementBloque($utilisateur['numero_utilisateur'])) {
            throw new CompteBloqueException("Votre compte est temporairement bloqué. Veuillez réessayer plus tard.");
        }

        if (!$utilisateur['email_valide']) {
            throw new CompteNonValideException("Votre compte n'a pas été validé. Veuillez vérifier votre e-mail.");
        }

        if (!password_verify($motDePasseClair, $utilisateur['mot_de_passe'])) {
            $this->traiterTentativeConnexionEchoueePourUtilisateur($utilisateur['numero_utilisateur']);
            throw new IdentifiantsInvalidesException("Identifiants de connexion invalides.");
        }

        $this->reinitialiserTentativesConnexion($utilisateur['numero_utilisateur']);

        if ($utilisateur['preferences_2fa_active']) {
            $_SESSION['2fa_user_id'] = $utilisateur['numero_utilisateur'];
            $_SESSION['2fa_pending'] = true;
            return ['status' => '2fa_required'];
        }

        $this->demarrerSessionUtilisateur($utilisateur['numero_utilisateur']);
        return ['status' => 'success', 'user' => $this->getUtilisateurConnecteComplet()];
    }

    public function demarrerSessionUtilisateur(string $numeroUtilisateur): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $numeroUtilisateur;
        $_SESSION['last_activity'] = time();
        $_SESSION['user_data'] = $this->recupererUtilisateurCompletParNumero($numeroUtilisateur);
        $_SESSION['user_permissions'] = $this->permissionService->recupererPermissionsPourGroupe($_SESSION['user_data']['id_groupe_utilisateur']);

        unset($_SESSION['2fa_pending'], $_SESSION['2fa_user_id']);
        $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['derniere_connexion' => date('Y-m-d H:i:s')]);
        $this->supervisionService->enregistrerAction($numeroUtilisateur, 'SUCCES_LOGIN', "Connexion réussie");
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $numeroUtilisateur = $_SESSION['user_id'] ?? 'N/A';
            $_SESSION = [];
            session_destroy();
            setcookie(session_name(), '', time() - 3600, '/');
            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'LOGOUT', "Déconnexion réussie");
        }
    }

    public function getUtilisateurConnecteComplet(): ?array
    {
        if (isset($_SESSION['user_id']) && $this->estUtilisateurConnecteEtSessionValide($_SESSION['user_id'])) {
            return $_SESSION['user_data'] ?? null;
        }
        return null;
    }

    public function estUtilisateurConnecteEtSessionValide(?string $numeroUtilisateur = null): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION['user_id'])) return false;
        if ($numeroUtilisateur && $_SESSION['user_id'] !== $numeroUtilisateur) return false;
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > ini_get('session.gc_maxlifetime'))) {
            $this->logout();
            return false;
        }
        $_SESSION['last_activity'] = time();
        $utilisateur = $this->utilisateurModel->trouverParIdentifiant($_SESSION['user_id'], ['statut_compte']);
        if (!$utilisateur || $utilisateur['statut_compte'] !== 'actif') {
            $this->logout();
            return false;
        }
        return true;
    }

    public function creerCompteUtilisateurComplet(array $donneesUtilisateur, array $donneesProfil, string $typeProfilCode, bool $envoyerEmailValidation = true): string
    {
        $this->utilisateurModel->commencerTransaction();
        try {
            if ($this->utilisateurModel->trouverUnParCritere(['login_utilisateur' => $donneesUtilisateur['login_utilisateur']])) {
                throw new DoublonException("Ce login est déjà utilisé.");
            }
            if ($this->utilisateurModel->trouverUnParCritere(['email_principal' => $donneesUtilisateur['email_principal']])) {
                throw new DoublonException("Cet email est déjà utilisé.");
            }

            $this->verifierRobustesseMotDePasse($donneesUtilisateur['mot_de_passe']);

            $numeroUtilisateur = $donneesProfil['numero_carte_etudiant'] ?? $donneesProfil['numero_enseignant'] ?? $donneesProfil['numero_personnel_administratif'] ?? $this->idGenerator->generate('utilisateur');

            $utilisateurData = [
                'numero_utilisateur' => $numeroUtilisateur,
                'login_utilisateur' => $donneesUtilisateur['login_utilisateur'],
                'email_principal' => $donneesUtilisateur['email_principal'],
                'mot_de_passe' => password_hash($donneesUtilisateur['mot_de_passe'], PASSWORD_BCRYPT),
                'id_type_utilisateur' => $typeProfilCode,
                'id_groupe_utilisateur' => $donneesUtilisateur['id_groupe_utilisateur'],
                'id_niveau_acces_donne' => $donneesUtilisateur['id_niveau_acces_donne'],
                'statut_compte' => 'en_attente_validation',
            ];
            $this->utilisateurModel->creer($utilisateurData);

            $profilData['numero_utilisateur'] = $numeroUtilisateur;
            switch ($typeProfilCode) {
                case 'TYPE_ETUD': $this->etudiantModel->creer($profilData); break;
                case 'TYPE_ENS': $this->enseignantModel->creer($profilData); break;
                case 'TYPE_PERS_ADMIN': $this->personnelAdminModel->creer($profilData); break;
            }

            $this->utilisateurModel->validerTransaction();
            return $numeroUtilisateur;
        } catch (\Exception $e) {
            $this->utilisateurModel->annulerTransaction();
            throw $e;
        }
    }

    public function listerUtilisateursAvecProfils(array $criteres = [], int $page = 1, int $elementsParPage = 20): array
    {
        $offset = ($page - 1) * $elementsParPage;
        $utilisateurs = $this->utilisateurModel->trouverParCritere($criteres, ['*'], 'AND', null, $elementsParPage, $offset);
        foreach ($utilisateurs as &$user) {
            $user['profil'] = $this->recupererProfil($user['numero_utilisateur'], $user['id_type_utilisateur']);
        }
        return $utilisateurs;
    }

    private function recupererProfil(string $numeroUtilisateur, string $typeProfilCode): ?array
    {
        switch ($typeProfilCode) {
            case 'TYPE_ETUD': return $this->etudiantModel->trouverParIdentifiant($numeroUtilisateur);
            case 'TYPE_ENS': return $this->enseignantModel->trouverParIdentifiant($numeroUtilisateur);
            case 'TYPE_PERS_ADMIN': return $this->personnelAdminModel->trouverParIdentifiant($numeroUtilisateur);
            default: return null;
        }
    }

    public function mettreAJourProfilUtilisateur(string $numeroUtilisateur, string $typeProfilCode, array $donneesProfil): bool
    {
        switch ($typeProfilCode) {
            case 'TYPE_ETUD': return $this->etudiantModel->mettreAJourParIdentifiant($numeroUtilisateur, $donneesProfil);
            case 'TYPE_ENS': return $this->enseignantModel->mettreAJourParIdentifiant($numeroUtilisateur, $donneesProfil);
            case 'TYPE_PERS_ADMIN': return $this->personnelAdminModel->mettreAJourParIdentifiant($numeroUtilisateur, $donneesProfil);
            default: return false;
        }
    }

    public function mettreAJourCompteUtilisateurParAdmin(string $numeroUtilisateur, array $donneesCompte): bool
    {
        return $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, $donneesCompte);
    }

    public function supprimerUtilisateur(string $numeroUtilisateur): bool
    {
        $this->utilisateurModel->commencerTransaction();
        try {
            $user = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur);
            if (!$user) throw new ElementNonTrouveException("Utilisateur non trouvé.");

            $this->recupererProfil($numeroUtilisateur, $user['id_type_utilisateur']);
            switch ($user['id_type_utilisateur']) {
                case 'TYPE_ETUD': $this->etudiantModel->supprimerParIdentifiant($numeroUtilisateur); break;
                case 'TYPE_ENS': $this->enseignantModel->supprimerParIdentifiant($numeroUtilisateur); break;
                case 'TYPE_PERS_ADMIN': $this->personnelAdminModel->supprimerParIdentifiant($numeroUtilisateur); break;
            }
            $this->utilisateurModel->supprimerParIdentifiant($numeroUtilisateur);
            $this->utilisateurModel->validerTransaction();
            return true;
        } catch (\Exception $e) {
            $this->utilisateurModel->annulerTransaction();
            throw $e;
        }
    }

    public function changerStatutDuCompte(string $numeroUtilisateur, string $nouveauStatut, ?string $raison = null): bool
    {
        return $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['statut_compte' => $nouveauStatut]);
    }

    public function modifierMotDePasse(string $numeroUtilisateur, string $nouveauMotDePasseClair, ?string $ancienMotDePasseClair = null, bool $isAdminReset = false): bool
    {
        $user = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur);
        if (!$user) throw new ElementNonTrouveException("Utilisateur non trouvé.");
        if (!$isAdminReset && !password_verify($ancienMotDePasseClair, $user['mot_de_passe'])) {
            throw new MotDePasseInvalideException("L'ancien mot de passe est incorrect.");
        }
        $this->verifierRobustesseMotDePasse($nouveauMotDePasseClair);
        $nouveauMotDePasseHache = password_hash($nouveauMotDePasseClair, PASSWORD_BCRYPT);
        return $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['mot_de_passe' => $nouveauMotDePasseHache]);
    }

    public function demanderReinitialisationMotDePasse(string $emailPrincipal): void
    {
        $user = $this->utilisateurModel->trouverUnParCritere(['email_principal' => $emailPrincipal]);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $this->utilisateurModel->mettreAJourParIdentifiant($user['numero_utilisateur'], [
                'token_reset_mdp' => hash('sha256', $token),
                'date_expiration_token_reset' => date('Y-m-d H:i:s', time() + 3600)
            ]);
            $resetLink = ($_ENV['APP_URL'] ?? 'http://localhost:8080') . '/reset-password?token=' . $token;
            $this->emailService->send($emailPrincipal, 'Réinitialisation de mot de passe', "Cliquez ici pour réinitialiser: <a href='{$resetLink}'>{$resetLink}</a>");
        }
    }

    public function reinitialiserMotDePasseApresValidationToken(string $tokenClair, string $nouveauMotDePasseClair): bool
    {
        $tokenHache = hash('sha256', $tokenClair);
        $user = $this->utilisateurModel->trouverUnParCritere(['token_reset_mdp' => $tokenHache]);
        if (!$user) throw new TokenInvalideException("Token invalide.");
        if (new \DateTime() > new \DateTime($user['date_expiration_token_reset'])) throw new TokenExpireException("Token expiré.");

        return $this->modifierMotDePasse($user['numero_utilisateur'], $nouveauMotDePasseClair, null, true);
    }

    public function genererEtStockerSecret2FA(string $numeroUtilisateur): array
    {
        $secret = $this->tfa->createSecret();
        $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['secret_2fa' => $secret]);
        $user = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur);
        $qrCodeUrl = $this->tfa->getQRCodeImageAsDataUri($user['email_principal'], $secret);
        return ['secret' => $secret, 'qr_code_url' => $qrCodeUrl];
    }

    public function activerAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTP): bool
    {
        $user = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur);
        if (!$user || !$user['secret_2fa']) throw new OperationImpossibleException("Secret 2FA non généré.");
        if (!$this->tfa->verifyCode($user['secret_2fa'], $codeTOTP)) throw new IdentifiantsInvalidesException("Code 2FA incorrect.");

        return $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['preferences_2fa_active' => 1]);
    }

    public function desactiverAuthentificationDeuxFacteurs(string $numeroUtilisateur): bool
    {
        return $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['preferences_2fa_active' => 0, 'secret_2fa' => null]);
    }

    public function verifierCodeAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTP): bool
    {
        $user = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur);
        if (!$user || !$user['secret_2fa']) return false;
        return $this->tfa->verifyCode($user['secret_2fa'], $codeTOTP);
    }

    public function estCompteActuellementBloque(string $numeroUtilisateur): bool
    {
        $user = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur);
        if ($user && $user['compte_bloque_jusqua']) {
            if (new \DateTime() < new \DateTime($user['compte_bloque_jusqua'])) {
                return true;
            } else {
                $this->reinitialiserTentativesConnexion($numeroUtilisateur);
            }
        }
        return false;
    }

    public function traiterTentativeConnexionEchoueePourUtilisateur(string $numeroUtilisateur): void
    {
        $this->utilisateurModel->getDb()->exec("UPDATE utilisateur SET tentatives_connexion_echouees = tentatives_connexion_echouees + 1 WHERE numero_utilisateur = '{$numeroUtilisateur}'");
        $user = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur);
        if ($user['tentatives_connexion_echouees'] >= self::MAX_LOGIN_ATTEMPTS) {
            $lockoutUntil = date('Y-m-d H:i:s', time() + self::LOCKOUT_TIME_MINUTES * 60);
            $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['compte_bloque_jusqua' => $lockoutUntil]);
        }
    }

    public function reinitialiserTentativesConnexion(string $numeroUtilisateur): void
    {
        $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['tentatives_connexion_echouees' => 0, 'compte_bloque_jusqua' => null]);
    }

    public function verifierRobustesseMotDePasse(string $motDePasse): void
    {
        if (strlen($motDePasse) < 8) throw new MotDePasseInvalideException("Le mot de passe doit contenir au moins 8 caractères.");
    }

    public function validerCompteEmailViaToken(string $tokenClair): bool
    {
        $tokenHache = hash('sha256', $tokenClair);
        $user = $this->utilisateurModel->trouverUnParCritere(['token_validation_email' => $tokenHache]);
        if (!$user) throw new TokenInvalideException("Token de validation invalide.");
        return $this->utilisateurModel->mettreAJourParIdentifiant($user['numero_utilisateur'], ['email_valide' => 1, 'statut_compte' => 'actif', 'token_validation_email' => null]);
    }

    public function getUtilisateurModel(): Utilisateur { return $this->utilisateurModel; }
    public function getEnseignantModel(): Enseignant { return $this->enseignantModel; }
}