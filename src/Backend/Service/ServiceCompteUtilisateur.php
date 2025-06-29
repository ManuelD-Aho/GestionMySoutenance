<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use RobThree\Auth\TwoFactorAuth;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\HistoriqueMotDePasse;
use App\Backend\Service\Interface\CompteUtilisateurServiceInterface;
use App\Backend\Service\Interface\EmailServiceInterface;
use App\Backend\Service\Interface\AuditServiceInterface;
use App\Backend\Service\Interface\IdentifiantGeneratorInterface;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\MotDePasseInvalideException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\TokenInvalideException;

class ServiceCompteUtilisateur implements CompteUtilisateurServiceInterface
{
    private PDO $pdo;
    private Utilisateur $utilisateurModel;
    private HistoriqueMotDePasse $historiqueMdpModel;
    private EmailServiceInterface $emailService;
    private AuditServiceInterface $auditService;
    private IdentifiantGeneratorInterface $identifiantGenerator;

    public function __construct(
        PDO $pdo,
        Utilisateur $utilisateurModel,
        HistoriqueMotDePasse $historiqueMdpModel,
        EmailServiceInterface $emailService,
        AuditServiceInterface $auditService,
        IdentifiantGeneratorInterface $identifiantGenerator
    ) {
        $this->pdo = $pdo;
        $this->utilisateurModel = $utilisateurModel;
        $this->historiqueMdpModel = $historiqueMdpModel;
        $this->emailService = $emailService;
        $this->auditService = $auditService;
        $this->identifiantGenerator = $identifiantGenerator;
    }

    public function creerCompte(array $donneesLogin, string $idGroupe, string $idType, string $idNiveauAcces): string
    {
        if ($this->utilisateurModel->loginExiste($donneesLogin['login_utilisateur'])) {
            throw new DoublonException("Le login '{$donneesLogin['login_utilisateur']}' est déjà utilisé.");
        }
        if ($this->utilisateurModel->trouverUnParCritere(['email_principal' => $donneesLogin['email_principal']])) {
            throw new DoublonException("L'email '{$donneesLogin['email_principal']}' est déjà utilisé.");
        }

        $numeroUtilisateur = $this->identifiantGenerator->generer(substr($idType, 0, 3));

        $this->pdo->beginTransaction();
        try {
            $donneesCompte = [
                'numero_utilisateur' => $numeroUtilisateur,
                'login_utilisateur' => $donneesLogin['login_utilisateur'],
                'email_principal' => $donneesLogin['email_principal'],
                'mot_de_passe' => password_hash($donneesLogin['mot_de_passe'], PASSWORD_ARGON2ID),
                'id_groupe_utilisateur' => $idGroupe,
                'id_type_utilisateur' => $idType,
                'id_niveau_acces_donne' => $idNiveauAcces,
                'statut_compte' => 'inactif',
                'date_creation' => (new \DateTime())->format('Y-m-d H:i:s')
            ];
            $this->utilisateurModel->creer($donneesCompte);
            $this->envoyerEmailDeValidation($numeroUtilisateur);
            $this->auditService->enregistrerAction('SYSTEM', 'USER_ACCOUNT_CREATED', $numeroUtilisateur, 'Utilisateur', $donneesCompte);
            $this->pdo->commit();

            return $numeroUtilisateur;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function mettreAJourInformationsBase(string $numeroUtilisateur, array $donnees): bool
    {
        $user = $this->recupererOuEchouer($numeroUtilisateur);
        return $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, $donnees);
    }

    public function changerStatut(string $numeroUtilisateur, string $nouveauStatut): bool
    {
        $this->recupererOuEchouer($numeroUtilisateur);
        $resultat = $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['statut_compte' => $nouveauStatut]);
        $this->auditService->enregistrerAction('SYSTEM_ADMIN', 'USER_STATUS_CHANGED', $numeroUtilisateur, 'Utilisateur', ['nouveau_statut' => $nouveauStatut]);
        return $resultat;
    }

    public function changerMotDePasse(string $numeroUtilisateur, string $ancienMdp, string $nouveauMdp): bool
    {
        $user = $this->recupererOuEchouer($numeroUtilisateur);
        if (!password_verify($ancienMdp, $user['mot_de_passe_hache'])) {
            throw new MotDePasseInvalideException("L'ancien mot de passe est incorrect.");
        }

        $historique = $this->historiqueMdpModel->recupererHistoriquePourUtilisateur($numeroUtilisateur, 5);
        foreach ($historique as $h) {
            if (password_verify($nouveauMdp, $h['mot_de_passe_hache'])) {
                throw new MotDePasseInvalideException("Le nouveau mot de passe ne peut pas être identique à l'un des 5 précédents.");
            }
        }

        $nouveauMdpHache = password_hash($nouveauMdp, PASSWORD_ARGON2ID);
        $this->pdo->beginTransaction();
        try {
            $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['mot_de_passe_hache' => $nouveauMdpHache]);
            $this->historiqueMdpModel->creer([
                'numero_utilisateur' => $numeroUtilisateur,
                'mot_de_passe' => $user['mot_de_passe_hache'],
                'date_changement' => (new \DateTime())->format('Y-m-d H:i:s')
            ]);
            $this->auditService->enregistrerAction($numeroUtilisateur, 'USER_PASSWORD_CHANGED', $numeroUtilisateur, 'Utilisateur');
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function reinitialiserMotDePasseParAdmin(string $numeroUtilisateur): string
    {
        $this->recupererOuEchouer($numeroUtilisateur);
        $nouveauMdp = bin2hex(random_bytes(8));
        $nouveauMdpHache = password_hash($nouveauMdp, PASSWORD_ARGON2ID);
        $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['mot_de_passe_hache' => $nouveauMdpHache]);
        $this->auditService->enregistrerAction('SYSTEM_ADMIN', 'USER_PASSWORD_RESET_BY_ADMIN', $numeroUtilisateur, 'Utilisateur');
        return $nouveauMdp;
    }

    public function genererSecret2FA(string $numeroUtilisateur): array
    {
        $user = $this->recupererOuEchouer($numeroUtilisateur);
        $tfa = new TwoFactorAuth('GestionMySoutenance');
        $secret = $tfa->createSecret();
        $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['secret_2fa_temporaire' => $secret]);
        return [
            'secret' => $secret,
            'qr_code_url' => $tfa->getQRCodeImageAsDataUri($user['login_utilisateur'], $secret)
        ];
    }

    public function activer2FA(string $numeroUtilisateur, string $code): bool
    {
        $user = $this->recupererOuEchouer($numeroUtilisateur);
        if (empty($user['secret_2fa_temporaire'])) {
            throw new OperationImpossibleException("Aucun processus d'activation 2FA n'a été démarré.");
        }
        $tfa = new TwoFactorAuth('GestionMySoutenance');
        if (!$tfa->verifyCode($user['secret_2fa_temporaire'], $code)) {
            throw new MotDePasseInvalideException("Le code 2FA est invalide.");
        }
        $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, [
            'secret_2fa' => $user['secret_2fa_temporaire'],
            'secret_2fa_temporaire' => null,
            'date_activation_2fa' => (new \DateTime())->format('Y-m-d H:i:s')
        ]);
        $this->auditService->enregistrerAction($numeroUtilisateur, 'USER_2FA_ENABLED', $numeroUtilisateur, 'Utilisateur');
        return true;
    }

    public function desactiver2FA(string $numeroUtilisateur): bool
    {
        $this->recupererOuEchouer($numeroUtilisateur);
        $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, [
            'secret_2fa' => null,
            'secret_2fa_temporaire' => null,
            'date_activation_2fa' => null
        ]);
        $this->auditService->enregistrerAction($numeroUtilisateur, 'USER_2FA_DISABLED', $numeroUtilisateur, 'Utilisateur');
        return true;
    }

    public function lierEntiteMetier(string $numeroUtilisateur, string $idEntite, string $typeEntite): bool
    {
        $this->recupererOuEchouer($numeroUtilisateur);
        return $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['id_entite_metier' => $idEntite]);
    }

    public function validerEmail(string $token): bool
    {
        $tokenHash = hash('sha256', $token);
        $user = $this->utilisateurModel->trouverUnParCritere(['token_validation_email' => $tokenHash]);
        if (!$user) {
            throw new TokenInvalideException("Token de validation d'email invalide.");
        }
        $this->utilisateurModel->mettreAJourParIdentifiant($user['numero_utilisateur'], [
            'email_valide' => true,
            'statut_compte' => 'actif',
            'token_validation_email' => null
        ]);
        $this->auditService->enregistrerAction($user['numero_utilisateur'], 'USER_EMAIL_VALIDATED', $user['numero_utilisateur'], 'Utilisateur');
        return true;
    }

    public function envoyerEmailDeValidation(string $numeroUtilisateur): bool
    {
        $user = $this->recupererOuEchouer($numeroUtilisateur);
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['token_validation_email' => $tokenHash]);
        $this->emailService->envoyerDepuisTemplate(
            $user['email_principal'],
            'ACCOUNT_VALIDATION_TPL',
            ['user_name' => $user['login_utilisateur'], 'validation_token' => $token]
        );
        return true;
    }

    public function supprimerCompte(string $numeroUtilisateur): bool
    {
        $this->recupererOuEchouer($numeroUtilisateur);
        $this->pdo->beginTransaction();
        try {
            $this->utilisateurModel->supprimerParIdentifiant($numeroUtilisateur);
            $this->auditService->enregistrerAction('SYSTEM_ADMIN', 'USER_ACCOUNT_DELETED', $numeroUtilisateur, 'Utilisateur');
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function listerComptes(array $filtres = []): array
    {
        return $this->utilisateurModel->trouverParCritere($filtres);
    }

    private function recupererOuEchouer(string $numeroUtilisateur): array
    {
        $user = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur);
        if (!$user) {
            throw new ElementNonTrouveException("L'utilisateur avec l'ID '{$numeroUtilisateur}' n'a pas été trouvé.");
        }
        return $user;
    }
}