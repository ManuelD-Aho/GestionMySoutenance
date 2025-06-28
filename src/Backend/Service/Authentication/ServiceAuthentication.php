<?php

namespace App\Backend\Service\Authentication;

use PDO;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\HistoriqueMotDePasse;
use App\Backend\Model\TypeUtilisateur;
use App\Backend\Model\GroupeUtilisateur;
use App\Backend\Model\Enseignant;
use App\Backend\Model\Etudiant;
use App\Backend\Model\PersonnelAdministratif;
use App\Backend\Model\Sessions;
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

class ServiceAuthentication implements ServiceAuthenticationInterface
{
    private PDO $db;

    public Utilisateur $utilisateurModel;
    private HistoriqueMotDePasse $historiqueMdpModel;
    private TypeUtilisateur $typeUtilisateurModel;
    private GroupeUtilisateur $groupeUtilisateurModel;
    private Enseignant $enseignantModel;
    private Etudiant $etudiantModel;
    private PersonnelAdministratif $personnelAdminModel;
    private Sessions $sessionsModel;
    private ServiceEmailInterface $emailService;
    private ServiceSupervisionAdminInterface $supervisionService;
    private IdentifiantGeneratorInterface $idGenerator;
    private ServicePermissionsInterface $permissionService;

    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_TIME_MINUTES = 30;

    public function __construct(
        PDO $db,
        Utilisateur $utilisateurModel,
        HistoriqueMotDePasse $historiqueMdpModel,
        TypeUtilisateur $typeUtilisateurModel,
        GroupeUtilisateur $groupeUtilisateurModel,
        Enseignant $enseignantModel,
        Etudiant $etudiantModel,
        PersonnelAdministratif $personnelAdminModel,
        Sessions $sessionsModel,
        ServiceEmailInterface $emailService,
        ServiceSupervisionAdminInterface $supervisionService,
        IdentifiantGeneratorInterface $idGenerator,
        ServicePermissionsInterface $permissionService
    ) {
        $this->db = $db;
        $this->utilisateurModel = $utilisateurModel;
        $this->historiqueMdpModel = $historiqueMdpModel;
        $this->typeUtilisateurModel = $typeUtilisateurModel;
        $this->groupeUtilisateurModel = $groupeUtilisateurModel;
        $this->enseignantModel = $enseignantModel;
        $this->etudiantModel = $etudiantModel;
        $this->personnelAdminModel = $personnelAdminModel;
        $this->sessionsModel = $sessionsModel;
        $this->emailService = $emailService;
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
        $this->permissionService = $permissionService;
    }

    public function tenterConnexion(string $identifiant, string $motDePasseClair): array
    {
        $utilisateur = $this->utilisateurModel->trouverParLoginOuEmailPrincipal($identifiant);

        if (!$utilisateur) {
            $this->supervisionService->enregistrerAction($identifiant, 'ECHEC_LOGIN', 'Utilisateur non trouvé');
            throw new IdentifiantsInvalidesException("Identifiants de connexion invalides.");
        }

        $numeroUtilisateur = $utilisateur['numero_utilisateur'];

        if ($this->estCompteActuellementBloque($numeroUtilisateur)) {
            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'ECHEC_LOGIN', 'Compte bloqué');
            throw new CompteBloqueException("Votre compte est temporairement bloqué. Veuillez réessayer plus tard.");
        }

        if (!$utilisateur['email_valide']) {
            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'ECHEC_LOGIN', 'Email non validé');
            throw new CompteNonValideException("Votre compte n'a pas été validé. Veuillez vérifier votre e-mail.");
        }

        if (!password_verify($motDePasseClair, $utilisateur['mot_de_passe'])) {
            $this->traiterTentativeConnexionEchoueePourUtilisateur($numeroUtilisateur);
            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'ECHEC_LOGIN', 'Mot de passe incorrect');
            throw new IdentifiantsInvalidesException("Identifiants de connexion invalides.");
        }

        $this->reinitialiserTentativesConnexion($numeroUtilisateur);
        $this->supervisionService->enregistrerAction($numeroUtilisateur, 'SUCCES_LOGIN', 'Connexion réussie');

        if ($utilisateur['preferences_2fa_active']) {
            $_SESSION['2fa_user_id'] = $numeroUtilisateur;
            $_SESSION['2fa_pending'] = true;
            return ['status' => '2fa_required'];
        }

        $this->demarrerSessionUtilisateur($numeroUtilisateur);
        return ['status' => 'success', 'user' => $this->getUtilisateurConnecteComplet()];
    }

    public function demarrerSessionUtilisateur(string $numeroUtilisateur): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $numeroUtilisateur;
        $_SESSION['last_activity'] = time();
        $_SESSION['user_data'] = $this->recupererUtilisateurCompletParNumero($numeroUtilisateur);
        $_SESSION['user_permissions'] = $this->permissionService->recupererPermissionsPourGroupe($_SESSION['user_data']['id_groupe_utilisateur']);

        if (isset($_SESSION['2fa_pending'])) {
            unset($_SESSION['2fa_user_id']);
            unset($_SESSION['2fa_pending']);
        }

        $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['derniere_connexion' => date('Y-m-d H:i:s')]);
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $numeroUtilisateur = $_SESSION['user_id'] ?? 'N/A';
            $_SESSION = [];
            session_destroy();
            setcookie(session_name(), '', time() - 3600, '/');
            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'LOGOUT', 'Déconnexion réussie');
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
        if (session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION['user_id'])) {
            return false;
        }

        if ($numeroUtilisateur && $_SESSION['user_id'] !== $numeroUtilisateur) {
            return false;
        }

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
            if ($this->utilisateurModel->loginExiste($donneesUtilisateur['login_utilisateur'])) {
                throw new DoublonException("Ce login est déjà utilisé.");
            }
            if ($this->utilisateurModel->emailPrincipalExiste($donneesUtilisateur['email_principal'])) {
                throw new DoublonException("Cet email est déjà utilisé.");
            }

            $typeUtilisateur = $this->typeUtilisateurModel->trouverUnParCritere(['id_type_utilisateur' => $typeProfilCode]);
            if (!$typeUtilisateur) {
                throw new ElementNonTrouveException("Type d'utilisateur '{$typeProfilCode}' non trouvé.");
            }

            $idGroupeUtilisateur = match($typeProfilCode) {
                'TYPE_ADMIN' => 'GRP_ADMIN_SYS',
                'TYPE_ETUD' => 'GRP_ETUDIANT',
                'TYPE_ENS' => 'GRP_ENSEIGNANT',
                'TYPE_PERS_ADMIN' => 'GRP_PERS_ADMIN',
                default => throw new \Exception("Groupe utilisateur par défaut non défini pour le type '{$typeProfilCode}'.")
            };

            $prefixeId = match($typeProfilCode) {
                'TYPE_ETUD' => 'ETU',
                'TYPE_ENS' => 'ENS',
                'TYPE_PERS_ADMIN' => 'ADM',
                'TYPE_ADMIN' => 'SYS',
                default => throw new \Exception("Préfixe d'ID non défini pour le type '{$typeProfilCode}'.")
            };

            $numeroUtilisateur = $this->idGenerator->genererIdentifiantUnique($prefixeId);
            $motDePasseHache = password_hash($donneesUtilisateur['mot_de_passe'], PASSWORD_BCRYPT);

            $utilisateurData = [
                'numero_utilisateur' => $numeroUtilisateur,
                'login_utilisateur' => $donneesUtilisateur['login_utilisateur'],
                'email_principal' => $donneesUtilisateur['email_principal'],
                'mot_de_passe' => $motDePasseHache,
                'id_type_utilisateur' => $typeProfilCode,
                'id_groupe_utilisateur' => $idGroupeUtilisateur,
                'id_niveau_acces_donne' => $donneesUtilisateur['id_niveau_acces_donne'] ?? 'ACCES_PERSONNEL',
                'statut_compte' => 'en_attente_validation',
                'date_creation' => date('Y-m-d H:i:s')
            ];

            $tokenValidationEmailClair = bin2hex(random_bytes(32));
            $utilisateurData['token_validation_email'] = hash('sha256', $tokenValidationEmailClair);

            $this->utilisateurModel->creer($utilisateurData);

            $profilData['numero_utilisateur'] = $numeroUtilisateur;
            $profilIdKey = match($typeProfilCode) {
                'TYPE_ETUD' => 'numero_carte_etudiant',
                'TYPE_ENS' => 'numero_enseignant',
                'TYPE_PERS_ADMIN' => 'numero_personnel_administratif',
                default => ''
            };
            if ($profilIdKey) {
                $profilData[$profilIdKey] = $numeroUtilisateur;
            }

            match ($typeProfilCode) {
                'TYPE_ETUD' => $this->etudiantModel->creer($profilData),
                'TYPE_ENS' => $this->enseignantModel->creer($profilData),
                'TYPE_PERS_ADMIN' => $this->personnelAdminModel->creer($profilData),
                default => null
            };

            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'CREATION_COMPTE', "Compte utilisateur de type {$typeProfilCode} créé", $numeroUtilisateur, 'Utilisateur');
            $this->utilisateurModel->validerTransaction();

            if ($envoyerEmailValidation) {
                try {
                    $this->envoyerEmailValidationCompte($numeroUtilisateur, $tokenValidationEmailClair);
                } catch (EmailException $e) {
                    error_log("Échec de l'envoi de l'email de validation pour {$donneesUtilisateur['email_principal']}: " . $e->getMessage());
                }
            }
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
            $user['profil'] = [];
            switch ($user['id_type_utilisateur']) {
                case 'TYPE_ETUD':
                    $user['profil'] = $this->etudiantModel->trouverParIdentifiant($user['numero_utilisateur']) ?? [];
                    break;
                case 'TYPE_ENS':
                    $user['profil'] = $this->enseignantModel->trouverParIdentifiant($user['numero_utilisateur']) ?? [];
                    break;
                case 'TYPE_PERS_ADMIN':
                    $user['profil'] = $this->personnelAdminModel->trouverParIdentifiant($user['numero_utilisateur']) ?? [];
                    break;
            }
        }
        return $utilisateurs;
    }

    public function mettreAJourProfilUtilisateur(string $numeroUtilisateur, string $typeProfilCode, array $donneesProfil): bool
    {
        $success = match ($typeProfilCode) {
            'TYPE_ETUD' => $this->etudiantModel->mettreAJourParIdentifiant($numeroUtilisateur, $donneesProfil),
            'TYPE_ENS' => $this->enseignantModel->mettreAJourParIdentifiant($numeroUtilisateur, $donneesProfil),
            'TYPE_PERS_ADMIN' => $this->personnelAdminModel->mettreAJourParIdentifiant($numeroUtilisateur, $donneesProfil),
            default => true
        };
        if ($success) {
            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'MISE_AJOUR_PROFIL', "Profil de type {$typeProfilCode} mis à jour");
        }
        return $success;
    }

    public function mettreAJourCompteUtilisateurParAdmin(string $numeroUtilisateur, array $donneesCompte): bool
    {
        $this->utilisateurModel->commencerTransaction();
        try {
            $utilisateurActuel = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['login_utilisateur', 'email_principal']);
            if (!$utilisateurActuel) {
                throw new ElementNonTrouveException("Utilisateur non trouvé.");
            }

            if (isset($donneesCompte['login_utilisateur']) && $donneesCompte['login_utilisateur'] !== $utilisateurActuel['login_utilisateur']) {
                if ($this->utilisateurModel->loginExiste($donneesCompte['login_utilisateur'], $numeroUtilisateur)) {
                    throw new DoublonException("Ce login est déjà utilisé par un autre compte.");
                }
            }
            if (isset($donneesCompte['email_principal']) && $donneesCompte['email_principal'] !== $utilisateurActuel['email_principal']) {
                if ($this->utilisateurModel->emailPrincipalExiste($donneesCompte['email_principal'], $numeroUtilisateur)) {
                    throw new DoublonException("Cet email est déjà utilisé par un autre compte.");
                }
            }

            $success = $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, $donneesCompte);

            if ($success) {
                $this->utilisateurModel->validerTransaction();
                $this->supervisionService->enregistrerAction($numeroUtilisateur, 'MISE_AJOUR_COMPTE_ADMIN', "Compte utilisateur mis à jour par l'administrateur");
                if (isset($donneesCompte['id_groupe_utilisateur'])) {
                    $this->synchroniserPermissionsSessionsUtilisateur($numeroUtilisateur);
                }
                return true;
            }
            $this->utilisateurModel->annulerTransaction();
            return false;

        } catch (\Exception $e) {
            $this->utilisateurModel->annulerTransaction();
            throw $e;
        }
    }

    public function supprimerUtilisateur(string $numeroUtilisateur): bool
    {
        $this->utilisateurModel->commencerTransaction();
        try {
            $utilisateur = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['id_type_utilisateur']);
            if (!$utilisateur) {
                throw new ElementNonTrouveException("Utilisateur à supprimer non trouvé.");
            }

            match ($utilisateur['id_type_utilisateur']) {
                'TYPE_ETUD' => $this->etudiantModel->supprimerParIdentifiant($numeroUtilisateur),
                'TYPE_ENS' => $this->enseignantModel->supprimerParIdentifiant($numeroUtilisateur),
                'TYPE_PERS_ADMIN' => $this->personnelAdminModel->supprimerParIdentifiant($numeroUtilisateur),
                default => null
            };

            $this->historiqueMdpModel->supprimerParCritere(['numero_utilisateur' => $numeroUtilisateur]);
            $success = $this->utilisateurModel->supprimerParIdentifiant($numeroUtilisateur);

            if ($success) {
                $this->utilisateurModel->validerTransaction();
                $this->supervisionService->enregistrerAction($numeroUtilisateur, 'SUPPRESSION_COMPTE', "Compte utilisateur {$numeroUtilisateur} supprimé");
                return true;
            }
            $this->utilisateurModel->annulerTransaction();
            return false;

        } catch (\Exception $e) {
            $this->utilisateurModel->annulerTransaction();
            throw $e;
        }
    }

    public function changerStatutDuCompte(string $numeroUtilisateur, string $nouveauStatut, ?string $raison = null): bool
    {
        $this->utilisateurModel->commencerTransaction();
        try {
            $success = $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['statut_compte' => $nouveauStatut]);

            if ($success) {
                if ($nouveauStatut === 'bloque') {
                    $blocageJusqua = date('Y-m-d H:i:s', time() + self::LOCKOUT_TIME_MINUTES * 60);
                    $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['compte_bloque_jusqua' => $blocageJusqua]);
                } elseif ($nouveauStatut === 'actif') {
                    $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['compte_bloque_jusqua' => null]);
                }

                $this->utilisateurModel->validerTransaction();
                $this->supervisionService->enregistrerAction($numeroUtilisateur, 'CHANGEMENT_STATUT_COMPTE', "Statut du compte changé à '{$nouveauStatut}'" . ($raison ? " ({$raison})" : ""));
                $this->synchroniserPermissionsSessionsUtilisateur($numeroUtilisateur);
                return true;
            }
            $this->utilisateurModel->annulerTransaction();
            return false;
        } catch (\Exception $e) {
            $this->utilisateurModel->annulerTransaction();
            throw $e;
        }
    }

    public function modifierMotDePasse(string $numeroUtilisateur, string $nouveauMotDePasseClair, ?string $ancienMotDePasseClair = null, bool $isAdminReset = false): bool
    {
        $this->utilisateurModel->commencerTransaction();
        try {
            $utilisateur = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['mot_de_passe']);
            if (!$utilisateur) {
                throw new ElementNonTrouveException("Utilisateur non trouvé.");
            }

            if (!$isAdminReset) {
                if (!password_verify($ancienMotDePasseClair, $utilisateur['mot_de_passe'])) {
                    throw new MotDePasseInvalideException("L'ancien mot de passe est incorrect.");
                }
            }

            $this->verifierRobustesseMotDePasse($nouveauMotDePasseClair);

            if ($this->estNouveauMotDePasseDansHistorique($numeroUtilisateur, $nouveauMotDePasseClair)) {
                throw new MotDePasseInvalideException("Le nouveau mot de passe a déjà été utilisé récemment. Veuillez en choisir un autre.");
            }

            $nouveauMotDePasseHache = password_hash($nouveauMotDePasseClair, PASSWORD_BCRYPT);
            $success = $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['mot_de_passe' => $nouveauMotDePasseHache]);

            if ($success) {
                $this->historiqueMdpModel->creer([
                    'id_historique_mdp' => $this->idGenerator->genererIdentifiantUnique('HMP'),
                    'numero_utilisateur' => $numeroUtilisateur,
                    'mot_de_passe_hache' => $utilisateur['mot_de_passe']
                ]);
                $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['token_reset_mdp' => null, 'date_expiration_token_reset' => null]);
                $this->utilisateurModel->validerTransaction();
                $this->supervisionService->enregistrerAction($numeroUtilisateur, 'CHANGEMENT_MDP', $isAdminReset ? 'Mot de passe réinitialisé par admin' : 'Mot de passe modifié par utilisateur');
                return true;
            }
            $this->utilisateurModel->annulerTransaction();
            return false;

        } catch (\Exception $e) {
            $this->utilisateurModel->annulerTransaction();
            throw $e;
        }
    }

    public function demanderReinitialisationMotDePasse(string $emailPrincipal): void
    {
        $utilisateur = $this->utilisateurModel->trouverParEmailPrincipal($emailPrincipal);

        if (!$utilisateur || !$utilisateur['email_valide']) {
            $this->supervisionService->enregistrerAction($emailPrincipal, 'DEMANDE_RESET_MDP', 'Tentative de réinitialisation pour email non valide/inexistant');
            return;
        }

        $numeroUtilisateur = $utilisateur['numero_utilisateur'];
        $this->utilisateurModel->commencerTransaction();
        try {
            $tokenClair = bin2hex(random_bytes(32));
            $tokenHache = hash('sha256', $tokenClair);
            $dateExpiration = date('Y-m-d H:i:s', time() + (2 * 3600));

            $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, [
                'token_reset_mdp' => $tokenHache,
                'date_expiration_token_reset' => $dateExpiration
            ]);
            $this->utilisateurModel->validerTransaction();

            $resetLink = 'http://localhost/reset-password?token=' . $tokenClair;
            $emailData = [
                'destinataire_email' => $emailPrincipal,
                'sujet' => 'Réinitialisation de votre mot de passe',
                'corps_html' => "<p>Bonjour,</p><p>Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur ce lien pour continuer : <a href=\"{$resetLink}\">{$resetLink}</a></p><p>Ce lien expirera dans 2 heures.</p>",
                'corps_texte' => "Bonjour,\nVous avez demandé à réinitialiser votre mot de passe. Cliquez sur ce lien pour continuer : {$resetLink}\nCe lien expirera dans 2 heures.",
                'modele_email' => 'reset_password',
                'variables_modele' => ['reset_link' => $resetLink]
            ];

            $this->emailService->envoyerEmail($emailData);
            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'DEMANDE_RESET_MDP', 'Email de réinitialisation envoyé');

        } catch (\Exception $e) {
            $this->utilisateurModel->annulerTransaction();
            throw new EmailException("Erreur lors de l'envoi de l'e-mail de réinitialisation. Veuillez réessayer plus tard.");
        }
    }

    public function reinitialiserMotDePasseApresValidationToken(string $tokenClair, string $nouveauMotDePasseClair): bool
    {
        $utilisateur = $this->utilisateurModel->trouverParTokenResetMdp(hash('sha256', $tokenClair));
        if (!$utilisateur) {
            throw new TokenInvalideException("Le lien de réinitialisation est invalide ou a déjà été utilisé.");
        }

        if (new \DateTime() > new \DateTime($utilisateur['date_expiration_token_reset'])) {
            throw new TokenExpireException("Le lien de réinitialisation a expiré.");
        }

        return $this->modifierMotDePasse($utilisateur['numero_utilisateur'], $nouveauMotDePasseClair, null, true);
    }

    public function genererEtStockerSecret2FA(string $numeroUtilisateur): array
    {
        $tfa = new \RobThree\Auth\TwoFactorAuth('GestionMySoutenance');
        $secret = $tfa->createSecret();
        $userEmail = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur, ['email_principal'])['email_principal'];
        $qrCodeUrl = $tfa->getQRCodeImageAsDataUri($userEmail, $secret);

        $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['secret_2fa' => $secret]);
        $this->supervisionService->enregistrerAction($numeroUtilisateur, 'GENERATION_2FA_SECRET', 'Secret 2FA généré');

        return ['secret' => $secret, 'qr_code_url' => $qrCodeUrl];
    }

    public function activerAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTP): bool
    {
        $utilisateur = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['secret_2fa']);
        if (!$utilisateur || empty($utilisateur['secret_2fa'])) {
            throw new \Exception("Impossible d'activer 2FA: secret non trouvé.");
        }

        $tfa = new \RobThree\Auth\TwoFactorAuth('GestionMySoutenance');
        $isCodeValid = $tfa->verifyCode($utilisateur['secret_2fa'], $codeTOTP);

        if (!$isCodeValid) {
            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'ECHEC_ACTIVATION_2FA', 'Code 2FA incorrect lors de l\'activation');
            throw new IdentifiantsInvalidesException("Code de vérification 2FA incorrect.");
        }

        $success = $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['preferences_2fa_active' => true]);
        if ($success) {
            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'ACTIVATION_2FA', '2FA activée');
        }
        return $success;
    }

    public function desactiverAuthentificationDeuxFacteurs(string $numeroUtilisateur): bool
    {
        $success = $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['preferences_2fa_active' => false, 'secret_2fa' => null]);
        if ($success) {
            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'DESACTIVATION_2FA', '2FA désactivée');
        }
        return $success;
    }

    public function verifierCodeAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTP): bool
    {
        $utilisateur = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['secret_2fa']);
        if (!$utilisateur || empty($utilisateur['secret_2fa'])) {
            return false;
        }

        $tfa = new \RobThree\Auth\TwoFactorAuth('GestionMySoutenance');
        $isCodeValid = $tfa->verifyCode($utilisateur['secret_2fa'], $codeTOTP);

        if ($isCodeValid) {
            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'VERIF_2FA_SUCCES', 'Code 2FA validé');
        } else {
            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'VERIF_2FA_ECHEC', 'Code 2FA incorrect');
        }
        return $isCodeValid;
    }

    /**
     * Retourne le modèle Utilisateur
     */
    public function getUtilisateurModel(): Utilisateur
    {
        return $this->utilisateurModel;
    }

    /**
     * Retourne le modèle Enseignant
     */
    public function getEnseignantModel(): Enseignant
    {
        return $this->enseignantModel;
    }

    private function traiterTentativeConnexionEchoueePourUtilisateur(string $numeroUtilisateur): void
    {
        $utilisateur = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['tentatives_connexion_echouees']);
        if (!$utilisateur) {
            return;
        }

        $nouvellesTentatives = $utilisateur['tentatives_connexion_echouees'] + 1;
        $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['tentatives_connexion_echouees' => $nouvellesTentatives]);

        if ($nouvellesTentatives >= self::MAX_LOGIN_ATTEMPTS) {
            $this->changerStatutDuCompte($numeroUtilisateur, 'bloque', 'Trop de tentatives de connexion échouées');
            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'COMPTE_BLOQUE', 'Compte bloqué après tentatives échouées');
        }
    }

    private function reinitialiserTentativesConnexion(string $numeroUtilisateur): void
    {
        $this->utilisateurModel->mettreAJourParIdentifiant($numeroUtilisateur, ['tentatives_connexion_echouees' => 0, 'compte_bloque_jusqua' => null]);
    }

    private function estCompteActuellementBloque(string $numeroUtilisateur): bool
    {
        $utilisateur = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['compte_bloque_jusqua', 'statut_compte']);
        if (!$utilisateur) {
            return false;
        }

        if ($utilisateur['statut_compte'] === 'bloque' && $utilisateur['compte_bloque_jusqua'] !== null) {
            if (new \DateTime() < new \DateTime($utilisateur['compte_bloque_jusqua'])) {
                return true;
            } else {
                $this->changerStatutDuCompte($numeroUtilisateur, 'actif', 'Déblocage automatique après expiration');
                return false;
            }
        }
        if ($utilisateur['statut_compte'] === 'bloque') {
            return true;
        }

        return false;
    }

    private function verifierRobustesseMotDePasse(string $motDePasse): void
    {
        if (strlen($motDePasse) < 8) {
            throw new MotDePasseInvalideException("Le mot de passe doit contenir au moins 8 caractères.");
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
        if (!preg_match('/[^A-Za-z0-9]/', $motDePasse)) {
            throw new MotDePasseInvalideException("Le mot de passe doit contenir au moins un caractère spécial.");
        }
    }

    private function estNouveauMotDePasseDansHistorique(string $numeroUtilisateur, string $nouveauMotDePasseClair): bool
    {
        $historique = $this->historiqueMdpModel->recupererHistoriquePourUtilisateur($numeroUtilisateur, 3);
        foreach ($historique as $entry) {
            if (password_verify($nouveauMotDePasseClair, $entry['mot_de_passe_hache'])) {
                return true;
            }
        }
        return false;
    }

    private function envoyerEmailValidationCompte(string $numeroUtilisateur, string $tokenClair): void
    {
        $utilisateur = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['email_principal']);
        if (!$utilisateur || empty($utilisateur['email_principal'])) {
            throw new ElementNonTrouveException("Email de l'utilisateur non trouvé pour l'envoi de validation.");
        }

        $validationLink = 'http://localhost/validate-email?token=' . $tokenClair;
        $emailData = [
            'destinataire_email' => $utilisateur['email_principal'],
            'sujet' => 'Validez votre compte GestionMySoutenance',
            'corps_html' => "<p>Bonjour,</p><p>Veuillez cliquer sur ce lien pour valider votre compte : <a href=\"{$validationLink}\">{$validationLink}</a></p>",
            'corps_texte' => "Bonjour,\nVeuillez cliquer sur ce lien pour valider votre compte : {$validationLink}",
            'modele_email' => 'email_validation',
            'variables_modele' => ['validation_link' => $validationLink]
        ];

        if (!$this->emailService->envoyerEmail($emailData)) {
            throw new EmailException("Échec de l'envoi de l'e-mail de validation.");
        }
    }

    public function validerCompteEmailViaToken(string $tokenClair): bool
    {
        $tokenHache = hash('sha256', $tokenClair);
        $utilisateur = $this->utilisateurModel->trouverParTokenValidationEmailHache($tokenHache);

        if (!$utilisateur) {
            throw new TokenInvalideException("Le lien de validation est invalide ou a déjà été utilisé.");
        }

        $success = $this->utilisateurModel->mettreAJourParIdentifiant($utilisateur['numero_utilisateur'], [
            'email_valide' => true,
            'statut_compte' => 'actif',
            'token_validation_email' => null
        ]);

        if ($success) {
            $this->supervisionService->enregistrerAction($utilisateur['numero_utilisateur'], 'VALIDATION_EMAIL', 'Email de compte validé');
        }
        return $success;
    }

    public function recupererUtilisateurCompletParNumero(string $numeroUtilisateur): ?array
    {
        $utilisateur = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur);
        if (!$utilisateur) {
            return null;
        }

        $profilData = [];
        switch ($utilisateur['id_type_utilisateur']) {
            case 'TYPE_ETUD':
                $profilData = $this->etudiantModel->trouverParIdentifiant($numeroUtilisateur);
                break;
            case 'TYPE_ENS':
                $profilData = $this->enseignantModel->trouverParIdentifiant($numeroUtilisateur);
                break;
            case 'TYPE_PERS_ADMIN':
                $profilData = $this->personnelAdminModel->trouverParIdentifiant($numeroUtilisateur);
                break;
        }
        return array_merge($utilisateur, ['profil' => $profilData]);
    }

    private function synchroniserPermissionsSessionsUtilisateur(string $numeroUtilisateur): void
    {
        $sessions = $this->sessionsModel->trouverSessionsParUtilisateur($numeroUtilisateur);
        $utilisateur = $this->utilisateurModel->trouverParIdentifiant($numeroUtilisateur, ['id_groupe_utilisateur']);
        if (!$utilisateur) return;

        $newPermissions = $this->permissionService->recupererPermissionsPourGroupe($utilisateur['id_groupe_utilisateur']);

        foreach ($sessions as $session) {
            $sessionData = unserialize($session['session_data']);
            $sessionData['user_permissions'] = $newPermissions;
            if (isset($sessionData['user_data'])) {
                $sessionData['user_data']['user_permissions'] = $newPermissions;
            }

            $this->sessionsModel->mettreAJourParIdentifiant($session['session_id'], [
                'session_data' => serialize($sessionData),
                'session_last_activity' => time()
            ]);
        }
        $this->supervisionService->enregistrerAction($numeroUtilisateur, 'SYNCHRONISATION_RBAC', 'Permissions de session synchronisées.');
    }
}

