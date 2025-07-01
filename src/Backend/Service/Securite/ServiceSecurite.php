<?php
// src/Backend/Service/Securite/ServiceSecurite.php

namespace App\Backend\Service\Securite;

use App\Backend\Model\Delegation;
use PDO;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\HistoriqueMotDePasse;
use App\Backend\Model\Sessions;
use App\Backend\Model\GenericModel;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Service\Communication\ServiceCommunicationInterface;
use App\Backend\Exception\{
    IdentifiantsInvalidesException,
    CompteBloqueException,
    CompteNonValideException,
    MotDePasseInvalideException,
    ElementNonTrouveException,
    TokenInvalideException,
    TokenExpireException,
    EmailException
};
use RobThree\Auth\TwoFactorAuth;

class ServiceSecurite implements ServiceSecuriteInterface
{
    private PDO $db;
    private Utilisateur $utilisateurModel;
    private HistoriqueMotDePasse $historiqueMdpModel;
    private Sessions $sessionsModel;
    private GenericModel $rattacherModel;
    private ServiceSupervisionInterface $supervisionService;

    // Constantes de sécurité (peuvent être externalisées via un service de configuration)
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_TIME_MINUTES = 30;
    private const PASSWORD_HISTORY_LIMIT = 3;
    private const PASSWORD_MIN_LENGTH = 8;
    private Delegation $delegationModel;

    public function __construct(
        PDO $db,
        Utilisateur $utilisateurModel,
        HistoriqueMotDePasse $historiqueMdpModel,
        Sessions $sessionsModel,
        GenericModel $rattacherModel,
        ServiceSupervisionInterface $supervisionService,
        Delegation $delegationModel // <-- 1. AJOUTER le paramètre ici
)
    {
        $this->db = $db;
        $this->utilisateurModel = $utilisateurModel;
        $this->historiqueMdpModel = $historiqueMdpModel;
        $this->sessionsModel = $sessionsModel;
        $this->rattacherModel = $rattacherModel;
        $this->supervisionService = $supervisionService;
        $this->delegationModel = $delegationModel;
    }

    //================================================================
    // SECTION 1 : AUTHENTIFICATION & GESTION DE SESSION (API PUBLIQUE)
    //================================================================

    public function tenterConnexion(string $identifiant, string $motDePasseClair): array
    {
        $utilisateur = $this->utilisateurModel->trouverParLoginOuEmailPrincipal($identifiant);

        if (!$utilisateur || !password_verify($motDePasseClair, $utilisateur['mot_de_passe'])) {
            if ($utilisateur) {
                $this->traiterTentativeEchouee($utilisateur['numero_utilisateur']);
            }
            $this->supervisionService->enregistrerAction($identifiant, 'ECHEC_LOGIN', null, null, ['reason' => 'Identifiants invalides']);
            throw new IdentifiantsInvalidesException("Le login ou le mot de passe est incorrect.");
        }

        $numeroUtilisateur = $utilisateur['numero_utilisateur'];

        if ($this->estCompteBloque($utilisateur)) {
            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'ECHEC_LOGIN', null, null, ['reason' => 'Compte bloqué']);
            throw new CompteBloqueException("Votre compte est temporairement bloqué. Veuillez réessayer plus tard.");
        }

        if ($utilisateur['statut_compte'] !== 'actif' || !$utilisateur['email_valide']) {
            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'ECHEC_LOGIN', null, null, ['reason' => 'Compte non actif ou email non validé']);
            throw new CompteNonValideException("Votre compte n'est pas actif ou votre email n'a pas été validé.");
        }

        $this->reinitialiserTentativesConnexion($numeroUtilisateur);
        $this->supervisionService->enregistrerAction($numeroUtilisateur, 'SUCCES_LOGIN');

        if ($utilisateur['preferences_2fa_active']) {
            $_SESSION['2fa_user_id'] = $numeroUtilisateur;
            $_SESSION['2fa_pending'] = true;
            return ['status' => '2fa_required'];
        }

        $this->demarrerSessionUtilisateur($numeroUtilisateur);
        return ['status' => 'success'];
    }

    public function demarrerSessionUtilisateur(string $numeroUtilisateur): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $numeroUtilisateur;
        $_SESSION['last_activity'] = time();

        $user = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur, ['id_groupe_utilisateur']);
        $rattachements = $this->rattacherModel->trouverParCritere(['id_groupe_utilisateur' => $user['id_groupe_utilisateur']]);
        $_SESSION['user_group_permissions'] = array_column($rattachements, 'id_traitement');

        $_SESSION['user_delegations'] = $this->recupererDelegationsActivesPourUtilisateur($numeroUtilisateur);

        $userData = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur);
        unset($userData['mot_de_passe'], $userData['token_reset_mdp'], $userData['token_validation_email'], $userData['secret_2fa']);
        $_SESSION['user_data'] = $userData;

        unset($_SESSION['2fa_pending'], $_SESSION['2fa_user_id']);
        $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['derniere_connexion' => date('Y-m-d H:i:s')]);
    }

    public function logout(): void
    {
        $numeroUtilisateur = $_SESSION['user_id'] ?? 'ANONYMOUS';
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
        $this->supervisionService->enregistrerAction($numeroUtilisateur, 'LOGOUT');
    }

    public function estUtilisateurConnecte(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public function getUtilisateurConnecte(): ?array
    {
        return $this->estUtilisateurConnecte() ? $_SESSION['user_data'] : null;
    }

    //================================================================
    // SECTION 2 : GESTION DES MOTS DE PASSE (API PUBLIQUE)
    //================================================================

    public function demanderReinitialisationMotDePasse(string $emailPrincipal, ServiceCommunicationInterface $communicationService): void
    {
        $utilisateur = $this->utilisateurModel->trouverUnParCritere(['email_principal' => $emailPrincipal]);
        if (!$utilisateur) {
            return; // Ne pas révéler l'existence de l'email
        }

        $tokenClair = bin2hex(random_bytes(32));
        $this->utilisateurModel->mettreAJourParIdentifiant($utilisateur['numero_utilisateur'], [
            'token_reset_mdp' => hash('sha256', $tokenClair),
            'date_expiration_token_reset' => date('Y-m-d H:i:s', time() + 3600) // Expire dans 1 heure
        ]);

        $communicationService->envoyerEmail([
            'destinataire_email' => $emailPrincipal,
            'sujet' => 'Réinitialisation de votre mot de passe',
            'corps_html' => "Cliquez sur ce lien pour réinitialiser votre mot de passe : <a href='/reset-password/{$tokenClair}'>Réinitialiser</a>"
        ]);
    }

    public function reinitialiserMotDePasseViaToken(string $tokenClair, string $nouveauMotDePasseClair): bool
    {
        $tokenHache = hash('sha256', $tokenClair);
        $utilisateur = $this->utilisateurModel->trouverParTokenResetMdp($tokenHache);

        if (!$utilisateur) {
            throw new TokenInvalideException("Token invalide ou déjà utilisé.");
        }
        if (new \DateTime() > new \DateTime($utilisateur['date_expiration_token_reset'])) {
            throw new TokenExpireException("Le token a expiré.");
        }

        return $this->definirNouveauMotDePasse($utilisateur['numero_utilisateur'], $nouveauMotDePasseClair);
    }

    public function modifierMotDePasse(string $numeroUtilisateur, string $nouveauMotDePasseClair, string $ancienMotDePasseClair): bool
    {
        $utilisateur = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur);
        if (!$utilisateur || !password_verify($ancienMotDePasseClair, $utilisateur['mot_de_passe'])) {
            throw new MotDePasseInvalideException("L'ancien mot de passe est incorrect.");
        }
        return $this->definirNouveauMotDePasse($numeroUtilisateur, $nouveauMotDePasseClair);
    }

    //================================================================
    // SECTION 3 : AUTHENTIFICATION À DEUX FACTEURS (2FA) (API PUBLIQUE)
    //================================================================

    public function genererEtStockerSecret2FA(string $numeroUtilisateur): array
    {
        $tfa = new TwoFactorAuth('GestionMySoutenance');
        $secret = $tfa->createSecret();

        $utilisateur = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur, ['email_principal']);
        if (!$utilisateur) {
            throw new ElementNonTrouveException("Utilisateur non trouvé.");
        }

        $qrCodeUrl = $tfa->getQRCodeImageAsDataUri($utilisateur['email_principal'], $secret);

        $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['secret_2fa' => $secret]);
        $this->supervisionService->enregistrerAction($numeroUtilisateur, 'GENERATION_2FA_SECRET');

        return ['secret' => $secret, 'qr_code_url' => $qrCodeUrl];
    }

    public function activerAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTP): bool
    {
        $utilisateur = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur, ['secret_2fa']);
        if (!$utilisateur || empty($utilisateur['secret_2fa'])) {
            throw new OperationImpossibleException("Impossible d'activer la 2FA : aucun secret n'est généré.");
        }

        if (!$this->verifierCodeAuthentificationDeuxFacteurs($numeroUtilisateur, $codeTOTP, $utilisateur['secret_2fa'])) {
            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'ECHEC_ACTIVATION_2FA', null, null, ['reason' => 'Code invalide']);
            throw new IdentifiantsInvalidesException("Le code de vérification est incorrect.");
        }

        $success = $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['preferences_2fa_active' => 1]);
        if ($success) {
            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'ACTIVATION_2FA');
        }
        return $success;
    }

    public function desactiverAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $motDePasseClair): bool
    {
        $utilisateur = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur, ['mot_de_passe']);
        if (!$utilisateur || !password_verify($motDePasseClair, $utilisateur['mot_de_passe'])) {
            throw new MotDePasseInvalideException("Le mot de passe est incorrect.");
        }

        $success = $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['preferences_2fa_active' => 0, 'secret_2fa' => null]);
        if ($success) {
            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'DESACTIVATION_2FA');
        }
        return $success;
    }

    public function verifierCodeAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTP, ?string $secret = null): bool
    {
        if ($secret === null) {
            $user = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur, ['secret_2fa']);
            if (!$user || empty($user['secret_2fa'])) return false;
            $secret = $user['secret_2fa'];
        }

        $tfa = new TwoFactorAuth('GestionMySoutenance');
        return $tfa->verifyCode($secret, $codeTOTP);
    }

    //================================================================
    // SECTION 4 : AUTORISATION & PERMISSIONS (API PUBLIQUE)
    //================================================================

    public function utilisateurPossedePermission(string $permissionCode, ?string $contexteId = null, ?string $contexteType = null): bool
    {
        if (!$this->estUtilisateurConnecte()) {
            return false;
        }

        if (in_array($permissionCode, $_SESSION['user_group_permissions'] ?? [])) {
            return true;
        }

        $delegations = $_SESSION['user_delegations'] ?? [];
        foreach ($delegations as $delegation) {
            if ($delegation['id_traitement'] === $permissionCode) {
                if ($delegation['contexte_id'] === null) {
                    return true;
                }
                if ($delegation['contexte_id'] === $contexteId && $delegation['contexte_type'] === $contexteType) {
                    return true;
                }
            }
        }

        return false;
    }

    public function synchroniserPermissionsSessionsUtilisateur(string $numeroUtilisateur): void
    {
        $sessions = $this->sessionsModel->trouverSessionsParUtilisateur($numeroUtilisateur);
        if (!$sessions) return;

        $user = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur, ['id_groupe_utilisateur']);
        if (!$user) return;

        $newGroupPermissions = array_column($this->rattacherModel->trouverParCritere(['id_groupe_utilisateur' => $user['id_groupe_utilisateur']]), 'id_traitement');
        $newDelegations = $this->recupererDelegationsActivesPourUtilisateur($numeroUtilisateur);

        foreach ($sessions as $session) {
            $sessionData = unserialize($session['session_data']);
            $sessionData['user_group_permissions'] = $newGroupPermissions;
            $sessionData['user_delegations'] = $newDelegations;
            $this->sessionsModel->mettreAJourParIdentifiant($session['session_id'], ['session_data' => serialize($sessionData)]);
        }

        $this->supervisionService->enregistrerAction('SYSTEM', 'SYNCHRONISATION_RBAC', $numeroUtilisateur, 'Utilisateur');
    }

    //================================================================
    // SECTION 5 : LOGIQUE INTERNE & MÉTHODES PRIVÉES
    //================================================================

    private function traiterTentativeEchouee(string $numeroUtilisateur): void
    {
        $this->db->exec("UPDATE utilisateur SET tentatives_connexion_echouees = tentatives_connexion_echouees + 1 WHERE numero_utilisateur = '{$numeroUtilisateur}'");
        $user = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur, ['tentatives_connexion_echouees']);

        if ($user && $user['tentatives_connexion_echouees'] >= self::MAX_LOGIN_ATTEMPTS) {
            $lockoutTime = date('Y-m-d H:i:s', time() + (self::LOCKOUT_TIME_MINUTES * 60));
            $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['statut_compte' => 'bloque', 'compte_bloque_jusqua' => $lockoutTime]);
        }
    }

    private function reinitialiserTentativesConnexion(string $numeroUtilisateur): void
    {
        $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['tentatives_connexion_echouees' => 0, 'compte_bloque_jusqua' => null]);
    }

    private function estCompteBloque(array $utilisateur): bool
    {
        if ($utilisateur['statut_compte'] === 'bloque') {
            if ($utilisateur['compte_bloque_jusqua'] && new \DateTime() < new \DateTime($utilisateur['compte_bloque_jusqua'])) {
                return true;
            }
            // Le temps de blocage est écoulé, on réactive le compte au passage
            $this->utilisateurModel->mettreAJourParIdentifiant($utilisateur['numero_utilisateur'], ['statut_compte' => 'actif', 'compte_bloque_jusqua' => null]);
        }
        return false;
    }

    private function definirNouveauMotDePasse(string $numeroUtilisateur, string $nouveauMotDePasseClair): bool
    {
        $this->verifierRobustesseMotDePasse($nouveauMotDePasseClair);
        if ($this->estNouveauMotDePasseDansHistorique($numeroUtilisateur, $nouveauMotDePasseClair)) {
            throw new MotDePasseInvalideException("Ce mot de passe a été utilisé récemment. Veuillez en choisir un autre.");
        }

        $ancienMotDePasse = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur, ['mot_de_passe'])['mot_de_passe'];
        $nouveauMotDePasseHache = password_hash($nouveauMotDePasseClair, PASSWORD_BCRYPT);

        $this->utilisateurModel->commencerTransaction();
        try {
            $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, [
                'mot_de_passe' => $nouveauMotDePasseHache,
                'token_reset_mdp' => null,
                'date_expiration_token_reset' => null
            ]);
            $this->historiqueMdpModel->creer([
                'id_historique_mdp' => uniqid('HMP_'),
                'numero_utilisateur' => $numeroUtilisateur,
                'mot_de_passe_hache' => $ancienMotDePasse,
                'date_changement' => date('Y-m-d H:i:s')
            ]);
            $this->utilisateurModel->validerTransaction();
            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'CHANGEMENT_MDP');
            return true;
        } catch (\Exception $e) {
            $this->utilisateurModel->annulerTransaction();
            throw $e;
        }
    }

    private function verifierRobustesseMotDePasse(string $motDePasse): void
    {
        if (strlen($motDePasse) < self::PASSWORD_MIN_LENGTH) {
            throw new MotDePasseInvalideException("Le mot de passe doit contenir au moins " . self::PASSWORD_MIN_LENGTH . " caractères.");
        }
        if (!preg_match('/[A-Z]/', $motDePasse)) {
            throw new MotDePasseInvalideException("Le mot de passe doit contenir au moins une majuscule.");
        }
        if (!preg_match('/[a-z]/', $motDePasse)) {
            throw new MotDePasseInvalideException("Le mot de passe doit contenir au moins une minuscule.");
        }
        if (!preg_match('/[0-9]/', $motDePasse)) {
            throw new MotDePasseInvalideException("Le mot de passe doit contenir au moins un chiffre.");
        }
    }

    private function estNouveauMotDePasseDansHistorique(string $numeroUtilisateur, string $nouveauMotDePasseClair): bool
    {
        $historique = $this->historiqueMdpModel->recupererHistoriquePourUtilisateur($numeroUtilisateur, self::PASSWORD_HISTORY_LIMIT);
        foreach ($historique as $entry) {
            if (password_verify($nouveauMotDePasseClair, $entry['mot_de_passe_hache'])) {
                return true;
            }
        }
        return false;
    }

    private function recupererDelegationsActivesPourUtilisateur(string $numeroUtilisateur): array
    {
        // Utilisation de la méthode spécifique du modèle Delegation
        return $this->delegationModel->trouverDelegationActivePourUtilisateur($numeroUtilisateur);
    }
}