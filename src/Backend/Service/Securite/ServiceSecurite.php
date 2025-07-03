<?php
// src/Backend/Service/Securite/ServiceSecurite.php

namespace App\Backend\Service\Securite;

use PDO;
use RobThree\Auth\TwoFactorAuth;
use App\Backend\Model\Delegation;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\HistoriqueMotDePasse;
use App\Backend\Model\Sessions;
use App\Backend\Model\GenericModel;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Service\Communication\ServiceCommunicationInterface;
use App\Backend\Exception\{IdentifiantsInvalidesException,
    CompteBloqueException,
    CompteNonValideException,
    MotDePasseInvalideException,
    ElementNonTrouveException,
    PermissionException,
    TokenInvalideException,
    TokenExpireException,
    OperationImpossibleException};

class ServiceSecurite implements ServiceSecuriteInterface
{
    private PDO $db;
    private Utilisateur $utilisateurModel;
    private HistoriqueMotDePasse $historiqueMdpModel;
    private Sessions $sessionsModel;
    private GenericModel $rattacherModel;
    private GenericModel $traitementModel; // Assurez-vous que ce modèle est bien injecté et représente la table 'traitement'
    private Delegation $delegationModel;
    private ServiceSupervisionInterface $supervisionService;

    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_TIME_MINUTES = 30;
    private const PASSWORD_HISTORY_LIMIT = 3;
    private const PASSWORD_MIN_LENGTH = 8;

    public function __construct(
        PDO $db,
        Utilisateur $utilisateurModel,
        HistoriqueMotDePasse $historiqueMdpModel,
        Sessions $sessionsModel,
        GenericModel $rattacherModel,
        GenericModel $traitementModel, // Assurez-vous que ce modèle est bien injecté
        Delegation $delegationModel,
        ServiceSupervisionInterface $supervisionService
    ) {
        $this->db = $db;
        $this->utilisateurModel = $utilisateurModel;
        $this->historiqueMdpModel = $historiqueMdpModel;
        $this->sessionsModel = $sessionsModel;
        $this->rattacherModel = $rattacherModel;
        $this->traitementModel = $traitementModel; // Initialisation
        $this->delegationModel = $delegationModel;
        $this->supervisionService = $supervisionService;
    }

    //================================================================
    // SECTION 1 : AUTHENTIFICATION & GESTION DE SESSION
    //================================================================

    public function tenterConnexion(string $identifiant, string $motDePasseClair): array
    {
        error_log("DEBUG ServiceSecurite: Tentative de connexion pour: " . $identifiant);

        $utilisateur = $this->utilisateurModel->trouverParLoginOuEmailPrincipal($identifiant);

        if (!$utilisateur || !password_verify($motDePasseClair, $utilisateur['mot_de_passe'])) {
            error_log("DEBUG ServiceSecurite: Échec de connexion - identifiants invalides");
            if ($utilisateur) {
                $this->traiterTentativeEchouee($utilisateur['numero_utilisateur']);
            }
//            $this->supervisionService->enregistrerAction($identifiant, 'ECHEC_LOGIN', null, null, ['reason' => 'Identifiants invalides']);
            throw new IdentifiantsInvalidesException("Le login ou le mot de passe est incorrect.");
        }

        $numeroUtilisateur = $utilisateur['numero_utilisateur'];

        if ($this->estCompteBloque($utilisateur)) {
//            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'ECHEC_LOGIN', null, null, ['reason' => 'Compte bloqué']);
            throw new CompteBloqueException("Votre compte est temporairement bloqué. Veuillez réessayer plus tard.");
        }

        if ($utilisateur['statut_compte'] !== 'actif' || !$utilisateur['email_valide']) {
//            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'ECHEC_LOGIN', null, null, ['reason' => 'Compte non actif ou email non validé']);
            throw new CompteNonValideException("Votre compte n'est pas actif ou votre email n'a pas été validé.");
        }

        $this->reinitialiserTentativesConnexion($numeroUtilisateur);
//        $this->supervisionService->enregistrerAction($numeroUtilisateur, 'SUCCES_LOGIN');

        if ($utilisateur['preferences_2fa_active']) {
            $_SESSION['2fa_user_id'] = $numeroUtilisateur;
            $_SESSION['2fa_pending'] = true;
            return ['status' => '2fa_required'];
        }

        error_log("DEBUG ServiceSecurite: Toutes les vérifications passées, appel à demarrerSessionUtilisateur");

        $this->demarrerSessionUtilisateur($numeroUtilisateur);

        error_log("DEBUG ServiceSecurite: Session créée, retour de tenterConnexion avec status=success");
        return ['status' => 'success'];
    }

    public function demarrerSessionUtilisateur(string $numeroUtilisateur): void
    {
        error_log("DEBUG SecuriteService: Début de la session pour l'utilisateur: " . $numeroUtilisateur);
        session_regenerate_id(true);
        error_log("DEBUG SecuriteService: Nouvel ID de session après régénération: " . session_id());
        $_SESSION['user_id'] = $numeroUtilisateur;
        $_SESSION['last_activity'] = time();
        error_log("DEBUG SecuriteService: _SESSION['user_id'] défini à: " . $_SESSION['user_id']);

        $user = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur);
        if (!$user) {
            error_log("ERROR SecuriteService: Données utilisateur non trouvées pour le démarrage de session ID: " . $numeroUtilisateur);
            throw new ElementNonTrouveException("Impossible de démarrer la session pour un utilisateur inexistant.");
        }

        $rattachements = $this->rattacherModel->trouverParCritere(['id_groupe_utilisateur' => $user['id_groupe_utilisateur']]);
        $_SESSION['user_group_permissions'] = array_column($rattachements, 'id_traitement');
        $_SESSION['user_delegations'] = $this->recupererDelegationsActivesPourUtilisateur($numeroUtilisateur);

        unset($user['mot_de_passe'], $user['token_reset_mdp'], $user['token_validation_email'], $user['secret_2fa']);
        $_SESSION['user_data'] = $user;

        unset($_SESSION['2fa_pending'], $_SESSION['2fa_user_id']);
        $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['derniere_connexion' => date('Y-m-d H:i:s')]);
        error_log("DEBUG SecuriteService: Session entièrement peuplée pour l'utilisateur: " . ($_SESSION['user_id'] ?? 'N/A'));
        error_log("DEBUG SecuriteService: Données de session complètes: " . json_encode($_SESSION));
    }

    public function logout(): void
    {
        $numeroUtilisateur = $_SESSION['user_id'] ?? 'ANONYMOUS';
//        $this->supervisionService->enregistrerAction($numeroUtilisateur, 'LOGOUT');

        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
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
    // SECTION 2 : GESTION DES MOTS DE PASSE
    //================================================================

    public function demanderReinitialisationMotDePasse(string $emailPrincipal, ServiceCommunicationInterface $communicationService): void
    {
        $utilisateur = $this->utilisateurModel->trouverUnParCritere(['email_principal' => $emailPrincipal]);
        if (!$utilisateur) {
            return; // Ne pas révéler si l'email existe ou non
        }

        $tokenClair = bin2hex(random_bytes(32));
        $this->utilisateurModel->mettreAJourParIdentifiant($utilisateur['numero_utilisateur'], [
            'token_reset_mdp' => hash('sha256', $tokenClair),
            'date_expiration_token_reset' => date('Y-m-d H:i:s', time() + 3600)
        ]);

        $communicationService->envoyerEmail(
            $emailPrincipal,
            'RESET_PASSWORD', // ID du template de notification
            ['reset_link' => $_ENV['APP_URL'] . "/reset-password/{$tokenClair}"]
        );
    }

    public function reinitialiserMotDePasseViaToken(string $tokenClair, string $nouveauMotDePasseClair): bool
    {
        $tokenHache = hash('sha256', $tokenClair);
        $utilisateur = $this->utilisateurModel->trouverParTokenResetMdp($tokenHache);

        if (!$utilisateur) throw new TokenInvalideException("Token invalide ou déjà utilisé.");
        if (new \DateTime() > new \DateTime($utilisateur['date_expiration_token_reset'])) throw new TokenExpireException("Le token a expiré.");

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
    // SECTION 3 : AUTHENTIFICATION À DEUX FACTEURS (2FA)
    //================================================================

    public function genererEtStockerSecret2FA(string $numeroUtilisateur): array
    {
        $tfa = new TwoFactorAuth('GestionMySoutenance');
        $secret = $tfa->createSecret();

        $utilisateur = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur, ['email_principal']);
        if (!$utilisateur) throw new ElementNonTrouveException("Utilisateur non trouvé.");

        $qrCodeUrl = $tfa->getQRCodeImageAsDataUri($utilisateur['email_principal'], $secret);
        $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['secret_2fa' => $secret]);
//        $this->supervisionService->enregistrerAction($numeroUtilisateur, 'GENERATION_2FA_SECRET');

        return ['secret' => $secret, 'qr_code_url' => $qrCodeUrl];
    }

    public function activerAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTP): bool
    {
        $utilisateur = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur, ['secret_2fa']);
        if (!$utilisateur || empty($utilisateur['secret_2fa'])) throw new OperationImpossibleException("Impossible d'activer la 2FA : aucun secret n'est généré.");

        if (!$this->verifierCodeAuthentificationDeuxFacteurs($numeroUtilisateur, $codeTOTP, $utilisateur['secret_2fa'])) {
//            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'ECHEC_ACTIVATION_2FA', null, null, ['reason' => 'Code invalide']);
            throw new IdentifiantsInvalidesException("Le code de vérification est incorrect.");
        }

        $success = $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['preferences_2fa_active' => 1]);
//        if ($success) $this->supervisionService->enregistrerAction($numeroUtilisateur, 'ACTIVATION_2FA');
        return $success;
    }

    public function desactiverAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $motDePasseClair): bool
    {
        $utilisateur = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur, ['mot_de_passe']);
        if (!$utilisateur || !password_verify($motDePasseClair, $utilisateur['mot_de_passe'])) throw new MotDePasseInvalideException("Le mot de passe est incorrect.");

        $success = $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['preferences_2fa_active' => 0, 'secret_2fa' => null]);
//        if ($success) $this->supervisionService->enregistrerAction($numeroUtilisateur, 'DESACTIVATION_2FA');
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
    // SECTION 4 : AUTORISATION & PERMISSIONS
    //================================================================

    public function utilisateurPossedePermission(string $permissionCode, ?string $contexteId = null, ?string $contexteType = null): bool
    {
        if (!$this->estUtilisateurConnecte()) return false;

        // L'admin en mode impersonation a les droits de l'utilisateur cible, pas les siens.
        $permissions = $_SESSION['user_group_permissions'] ?? [];
        $delegations = $_SESSION['user_delegations'] ?? [];

        if (in_array($permissionCode, $permissions)) return true;

        foreach ($delegations as $delegation) {
            if ($delegation['id_traitement'] === $permissionCode) {
                if ($delegation['contexte_id'] === null || ($delegation['contexte_id'] === $contexteId && $delegation['contexte_type'] === $contexteType)) {
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
    // SECTION 5 : IMPERSONATION
    //================================================================

    public function demarrerImpersonation(string $adminId, string $targetUserId): bool
    {
        $admin = $this->utilisateurModel->trouverParIdentifiant($adminId);
        $targetUser = $this->utilisateurModel->trouverParIdentifiant($targetUserId);

        if (!$admin || !$targetUser || $admin['id_groupe_utilisateur'] !== 'GRP_ADMIN_SYS') {
            throw new PermissionException("Action d'impersonation non autorisée.");
        }
        if ($adminId === $targetUserId) {
            throw new OperationImpossibleException("Vous ne pouvez pas vous impersonnaliser vous-même.");
        }

        // Stocker les informations de l'admin
        $_SESSION['impersonator_data'] = $_SESSION['user_data'];

        // Démarrer une nouvelle session pour l'utilisateur cible
        $this->demarrerSessionUtilisateur($targetUserId);

        // Enregistrer l'action dans l'audit
        $this->supervisionService->enregistrerAction($adminId, 'IMPERSONATION_START', $targetUserId, 'Utilisateur');

        return true;
    }

    public function arreterImpersonation(): bool
    {
        if (!$this->estEnModeImpersonation()) {
            return false;
        }

        $adminData = $this->getImpersonatorData();
        $targetUserId = $_SESSION['user_id'];

        // Démarrer une nouvelle session pour l'admin
        $this->demarrerSessionUtilisateur($adminData['numero_utilisateur']);

        // Nettoyer la session
        unset($_SESSION['impersonator_data']);

        $this->supervisionService->enregistrerAction($adminData['numero_utilisateur'], 'IMPERSONATION_STOP', $targetUserId, 'Utilisateur');

        return true;
    }

    public function estEnModeImpersonation(): bool
    {
        return isset($_SESSION['impersonator_data']);
    }

    public function getImpersonatorData(): ?array
    {
        return $_SESSION['impersonator_data'] ?? null;
    }

    //================================================================
    // SECTION 6 : GESTION DYNAMIQUE DE L'INTERFACE
    //================================================================

    public function construireMenuPourUtilisateurConnecte(): array
    {
        if (!$this->estUtilisateurConnecte()) {
            return [];
        }

        $permissionsUtilisateur = $_SESSION['user_group_permissions'] ?? [];
        $delegationsUtilisateur = $_SESSION['user_delegations'] ?? [];

        // Ajouter les permissions déléguées à la liste des permissions de l'utilisateur pour cette requête
        foreach ($delegationsUtilisateur as $delegation) {
            $permissionsUtilisateur[] = $delegation['id_traitement'];
        }
        $permissionsUtilisateur = array_unique($permissionsUtilisateur);

        if (empty($permissionsUtilisateur)) {
            return [];
        }

        // 1. Récupérer tous les éléments de menu auxquels l'utilisateur a droit
        $placeholders = implode(',', array_fill(0, count($permissionsUtilisateur), '?'));
        $sql = "SELECT id_traitement, libelle_traitement AS libelle_menu, url_associee, icone_class, id_parent_traitement, ordre_affichage 
                FROM `{$this->traitementModel->table}`
                WHERE id_traitement LIKE 'MENU_%' AND id_traitement IN ($placeholders)
                ORDER BY ordre_affichage ASC, libelle_traitement ASC"; // Utiliser ordre_affichage ici

        $stmt = $this->db->prepare($sql);
        $stmt->execute($permissionsUtilisateur);
        $itemsMenu = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Construire l'arborescence
        $menuHierarchique = [];
        $itemsParId = [];

        // Indexer tous les items par leur ID
        foreach ($itemsMenu as $item) {
            $itemsParId[$item['id_traitement']] = $item;
            $itemsParId[$item['id_traitement']]['enfants'] = [];
        }

        // Associer les enfants à leurs parents
        foreach ($itemsParId as $id => &$item) {
            if (!empty($item['id_parent_traitement']) && isset($itemsParId[$item['id_parent_traitement']])) {
                $itemsParId[$item['id_parent_traitement']]['enfants'][] = &$item;
            }
        }
        unset($item); // Rompre la référence

        // Récupérer uniquement les éléments de premier niveau (ceux sans parent ou dont le parent n'est pas dans la liste)
        foreach ($itemsParId as $id => $item) {
            if (empty($item['id_parent_traitement']) || !isset($itemsParId[$item['id_parent_traitement']])) {
                $menuHierarchique[] = $item;
            }
        }

        // Trier les éléments de premier niveau et leurs enfants par ordre_affichage
        usort($menuHierarchique, function($a, $b) {
            return $a['ordre_affichage'] <=> $b['ordre_affichage'];
        });
        foreach ($menuHierarchique as &$item) {
            if (!empty($item['enfants'])) {
                usort($item['enfants'], function($a, $b) {
                    return $a['ordre_affichage'] <=> $b['ordre_affichage'];
                });
            }
        }
        unset($item); // Rompre la référence

        return $menuHierarchique;
    }

    public function updateMenuStructure(array $menuStructure): bool
    {
        $this->db->beginTransaction();
        try {
            foreach ($menuStructure as $item) {
                // Valider les données de l'élément de menu
                if (!isset($item['id_traitement']) || !isset($item['ordre_affichage'])) {
                    throw new OperationImpossibleException("Structure de menu invalide: id_traitement ou ordre_affichage manquant.");
                }

                $dataToUpdate = [
                    'ordre_affichage' => (int) $item['ordre_affichage'],
                    'id_parent_traitement' => $item['id_parent_traitement'] ?? null // Peut être null pour les éléments racines
                ];

                $success = $this->traitementModel->mettreAJourParIdentifiant($item['id_traitement'], $dataToUpdate);

                if (!$success) {
                    throw new OperationImpossibleException("Échec de la mise à jour de l'élément de menu: " . $item['id_traitement']);
                }
            }
            $this->db->commit();
            $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'UPDATE_MENU_STRUCTURE', null, 'Menu', ['structure' => $menuStructure]);
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    //================================================================
    // SECTION 7 : LOGIQUE INTERNE & MÉTHODES PRIVÉES
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
//            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'CHANGEMENT_MDP');
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
    //================================================================
    // SECTION 8 : VALIDATION D'EMAIL
    //================================================================

    /**
     * Valide l'adresse email d'un utilisateur à partir d'un token fourni.
     *
     * @param string $tokenClair Le token reçu dans l'URL de validation.
     * @return array Les données de l'utilisateur dont l'email vient d'être validé.
     * @throws TokenInvalideException Si le token ne correspond à aucun utilisateur.
     * @throws OperationImpossibleException Si l'email de l'utilisateur est déjà validé.
     * @throws TokenExpireException Si le token a dépassé sa date de validité.
     */
    public function validateEmailToken(string $tokenClair): array
    {
        // 1. Hacher le token reçu pour le comparer à celui stocké en base de données.
        $tokenHache = hash('sha256', $tokenClair);

        // 2. Chercher l'utilisateur correspondant à ce token de validation.
        $utilisateur = $this->utilisateurModel->trouverParTokenValidationEmail($tokenHache);

        // 3. Gérer les cas d'erreur.
        if (!$utilisateur) {
            // Le token est incorrect ou a déjà été utilisé et effacé.
            throw new TokenInvalideException("Token de validation d'email invalide ou déjà utilisé.");
        }

        if ($utilisateur['email_valide']) {
            // L'email a déjà été validé, l'action n'a plus lieu d'être.
            throw new OperationImpossibleException("L'email est déjà validé pour cet utilisateur.");
        }

        // La colonne d'expiration du token de mot de passe est réutilisée pour la validation d'email.
        if ($utilisateur['date_expiration_token_reset'] && new \DateTime() > new \DateTime($utilisateur['date_expiration_token_reset'])) {
            throw new TokenExpireException("Le token de validation d'email a expiré. Veuillez demander un nouveau lien.");
        }

        // 4. Mettre à jour le statut de l'utilisateur.
        $success = $this->utilisateurModel->mettreAJourParIdentifiant($utilisateur['numero_utilisateur'], [
            'email_valide' => 1, // Marquer l'email comme validé.
            'token_validation_email' => null, // Effacer le token pour qu'il ne puisse pas être réutilisé.
            'date_expiration_token_reset' => null // Effacer la date d'expiration associée.
        ]);

        if (!$success) {
            // Si la mise à jour échoue pour une raison inattendue.
            throw new OperationImpossibleException("Échec de la mise à jour du statut de l'email.");
        }

        // 5. Enregistrer l'action dans le journal d'audit.
        $this->supervisionService->enregistrerAction(
            $utilisateur['numero_utilisateur'],
            'VALIDATION_EMAIL_SUCCES',
            $utilisateur['numero_utilisateur'],
            'Utilisateur'
        );

        // 6. Retourner les données de l'utilisateur pour confirmation ou redirection.
        return $utilisateur;
    }
}