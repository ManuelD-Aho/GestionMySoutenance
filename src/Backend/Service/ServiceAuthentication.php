<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use RobThree\Auth\TwoFactorAuth;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\HistoriqueMotDePasse;
use App\Backend\Model\Sessions;
use App\Backend\Service\Interface\AuthenticationServiceInterface;
use App\Backend\Service\Interface\EmailServiceInterface;
use App\Backend\Service\Interface\AuditServiceInterface;
use App\Backend\Exception\AuthenticationException;
use App\Backend\Exception\CompteBloqueException;
use App\Backend\Exception\CompteNonValideException;
use App\Backend\Exception\IdentifiantsInvalidesException;
use App\Backend\Exception\TokenExpireException;
use App\Backend\Exception\TokenInvalideException;

class ServiceAuthentication implements AuthenticationServiceInterface
{
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_DURATION = '15 minutes';

    private PDO $pdo;
    private Utilisateur $utilisateurModel;
    private Sessions $sessionsModel;
    private EmailServiceInterface $emailService;
    private AuditServiceInterface $auditService;

    public function __construct(
        PDO $pdo,
        Utilisateur $utilisateurModel,
        Sessions $sessionsModel,
        EmailServiceInterface $emailService,
        AuditServiceInterface $auditService
    ) {
        $this->pdo = $pdo;
        $this->utilisateurModel = $utilisateurModel;
        $this->sessionsModel = $sessionsModel;
        $this->emailService = $emailService;
        $this->auditService = $auditService;
    }

    public function tenterConnexion(string $identifiant, string $motDePasse): array
    {
        $user = $this->utilisateurModel->trouverParLoginOuEmailPrincipal($identifiant);

        if (!$user) {
            throw new IdentifiantsInvalidesException("Identifiants invalides.");
        }

        $this->verifierStatutCompte($user);

        // Correction DDL: 'mot_de_passe' au lieu de 'mot_de_passe_hache'
        if (!password_verify($motDePasse, $user['mot_de_passe'])) {
            $this->enregistrerTentativeEchouee($user['numero_utilisateur']);
            throw new IdentifiantsInvalidesException("Identifiants invalides.");
        }

        $this->reinitialiserTentatives($user['numero_utilisateur']);
        $this->auditService->enregistrerAction($user['numero_utilisateur'], 'SUCCES_LOGIN', $user['numero_utilisateur'], 'Utilisateur');

        return $user;
    }

    public function demarrerSessionUtilisateur(string $numeroUtilisateur): bool
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        $_SESSION['user_id'] = $numeroUtilisateur;
        $_SESSION['login_time'] = time();

        return true;
    }

    public function logout(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            $this->auditService->enregistrerAction($userId, 'USER_LOGOUT', $userId, 'Utilisateur');
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
    }

    public function getUtilisateurConnecte(): ?array
    {
        if (!$this->estConnecte()) {
            return null;
        }
        return $this->utilisateurModel->trouverParIdentifiant($_SESSION['user_id']);
    }

    public function estConnecte(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public function verifierCode2FA(string $numeroUtilisateur, string $code): bool
    {
        $user = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur);
        if (!$user || empty($user['secret_2fa'])) {
            throw new AuthenticationException("La 2FA n'est pas activée pour cet utilisateur.");
        }

        $tfa = new TwoFactorAuth('GestionMySoutenance');
        if ($tfa->verifyCode($user['secret_2fa'], $code)) {
            $this->auditService->enregistrerAction($numeroUtilisateur, 'USER_2FA_SUCCESS', $numeroUtilisateur, 'Utilisateur');
            return true;
        }

        $this->auditService->enregistrerAction($numeroUtilisateur, 'USER_2FA_FAILURE', $numeroUtilisateur, 'Utilisateur');
        throw new AuthenticationException("Code 2FA invalide.");
    }

    public function demanderReinitialisationMotDePasse(string $email): bool
    {
        $user = $this->utilisateurModel->trouverUnParCritere(['email_principal' => $email]);
        if (!$user) {
            return true; // Ne pas révéler si un email existe
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiration = (new \DateTime())->add(new \DateInterval('PT1H'))->format('Y-m-d H:i:s');

        // Correction DDL: 'date_expiration_token_reset'
        $this->utilisateurModel->mettreAJourParIdentifiant($user['numero_utilisateur'], [
            'token_reset_mdp' => $tokenHash,
            'date_expiration_token_reset' => $expiration
        ]);

        $this->emailService->envoyerDepuisTemplate(
            $user['email_principal'],
            'PASSWORD_RESET_REQUEST',
            ['user_name' => $user['login_utilisateur'], 'reset_token' => $token]
        );

        $this->auditService->enregistrerAction($user['numero_utilisateur'], 'PASSWORD_RESET_REQUESTED', $user['numero_utilisateur'], 'Utilisateur');

        return true;
    }

    public function validerTokenReinitialisation(string $token): ?string
    {
        $tokenHash = hash('sha256', $token);
        $user = $this->utilisateurModel->trouverUnParCritere(['token_reset_mdp' => $tokenHash]);

        if (!$user) {
            throw new TokenInvalideException("Token de réinitialisation invalide.");
        }

        // Correction DDL: 'date_expiration_token_reset'
        if (new \DateTime() > new \DateTime($user['date_expiration_token_reset'])) {
            throw new TokenExpireException("Le token de réinitialisation a expiré.");
        }

        return $user['numero_utilisateur'];
    }

    public function reinitialiserMotDePasseAvecToken(string $token, string $nouveauMotDePasse): bool
    {
        $numeroUtilisateur = $this->validerTokenReinitialisation($token);

        $nouveauMdpHache = password_hash($nouveauMotDePasse, PASSWORD_ARGON2ID);

        $this->pdo->beginTransaction();
        try {
            // Correction DDL: 'mot_de_passe', 'date_expiration_token_reset'
            $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, [
                'mot_de_passe' => $nouveauMdpHache,
                'token_reset_mdp' => null,
                'date_expiration_token_reset' => null
            ]);

            $this->auditService->enregistrerAction($numeroUtilisateur, 'PASSWORD_RESET_COMPLETED', $numeroUtilisateur, 'Utilisateur');
            $this->pdo->commit();

            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function verifierStatutCompte(array $user): void
    {
        if ($user['statut_compte'] === 'bloque') {
            // Correction DDL: 'compte_bloque_jusqua'
            if (!empty($user['compte_bloque_jusqua'])) {
                $dateDeblocage = new \DateTime($user['compte_bloque_jusqua']);
                if (new \DateTime() < $dateDeblocage) {
                    throw new CompteBloqueException("Compte bloqué. Veuillez réessayer plus tard.");
                } else {
                    $this->reinitialiserTentatives($user['numero_utilisateur']);
                }
            }
        }

        if ($user['statut_compte'] !== 'actif') {
            throw new CompteNonValideException("Le compte n'est pas actif.");
        }

        if (!$user['email_valide']) {
            throw new CompteNonValideException("L'adresse email du compte n'a pas été validée.");
        }
    }

    private function enregistrerTentativeEchouee(string $numeroUtilisateur): void
    {
        $this->pdo->exec("UPDATE utilisateur SET tentatives_connexion_echouees = tentatives_connexion_echouees + 1 WHERE numero_utilisateur = '{$numeroUtilisateur}'");
        $user = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur);

        $this->auditService->enregistrerAction($numeroUtilisateur, 'ECHEC_LOGIN', $numeroUtilisateur, 'Utilisateur');

        if ($user['tentatives_connexion_echouees'] >= self::MAX_LOGIN_ATTEMPTS) {
            $dateDeblocage = (new \DateTime())->add(\DateInterval::createFromDateString(self::LOCKOUT_DURATION))->format('Y-m-d H:i:s');
            // Correction DDL: 'compte_bloque_jusqua'
            $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, [
                'statut_compte' => 'bloque',
                'compte_bloque_jusqua' => $dateDeblocage
            ]);
            $this->auditService->enregistrerAction($numeroUtilisateur, 'ACCOUNT_LOCKED', $numeroUtilisateur, 'Utilisateur');
            throw new CompteBloqueException("Compte bloqué suite à de trop nombreuses tentatives.");
        }
    }

    private function reinitialiserTentatives(string $numeroUtilisateur): void
    {
        // Correction DDL: 'compte_bloque_jusqua'
        $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, [
            'tentatives_connexion_echouees' => 0,
            'compte_bloque_jusqua' => null,
            'statut_compte' => 'actif' // Réactiver le compte après un succès
        ]);
    }
}