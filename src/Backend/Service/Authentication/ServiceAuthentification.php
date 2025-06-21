<?php
namespace App\Backend\Service\Authentication;

use PDO;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\HistoriqueMotDePasse;
use App\Backend\Model\TypeUtilisateur;
use App\Backend\Model\GroupeUtilisateur;
use App\Backend\Model\Enseignant; // Nouveau: pour gestion du profil Enseignant
use App\Backend\Model\Etudiant; // Nouveau: pour gestion du profil Etudiant
use App\Backend\Model\PersonnelAdministratif; // Nouveau: pour gestion du profil Personnel Administratif
use App\Backend\Model\Sessions; // Nouveau: pour la table sessions en DB
use App\Backend\Service\Email\ServiceEmail;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGenerator; // Nouveau service
use App\Backend\Exception\IdentifiantsInvalidesException;
use App\Backend\Exception\CompteBloqueException;
use App\Backend\Exception\CompteNonValideException;
use App\Backend\Exception\EmailException;
use App\Backend\Exception\EmailNonValideException;
use App\Backend\Exception\TokenExpireException;
use App\Backend\Exception\TokenInvalideException;
use App\Backend\Exception\MotDePasseInvalideException;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ElementNonTrouveException;

class ServiceAuthentification implements ServiceAuthenticationInterface
{
    private PDO $db;
    private Utilisateur $utilisateurModel;
    private HistoriqueMotDePasse $historiqueMdpModel;
    private ServiceEmail $emailService;
    private ServiceSupervisionAdmin $supervisionService;
    private IdentifiantGenerator $idGenerator; // Injection du nouveau service
    private TypeUtilisateur $typeUtilisateurModel; // Pour l'accès aux types d'utilisateurs
    private GroupeUtilisateur $groupeUtilisateurModel; // Pour l'accès aux groupes d'utilisateurs
    private Enseignant $enseignantModel; // Modèle Enseignant
    private Etudiant $etudiantModel; // Modèle Etudiant
    private PersonnelAdministratif $personnelAdminModel; // Modèle Personnel Administratif
    private Sessions $sessionsModel; // Modèle Sessions pour la gestion temps réel

    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_TIME_MINUTES = 30; // 30 minutes de blocage

    public function __construct(
        PDO $db,
        ServiceEmail $emailService,
        ServiceSupervisionAdmin $supervisionService,
        IdentifiantGenerator $idGenerator // Injecter le générateur
    ) {
        $this->db = $db;
        $this->utilisateurModel = new Utilisateur($db);
        $this->historiqueMdpModel = new HistoriqueMotDePasse($db);
        $this->typeUtilisateurModel = new TypeUtilisateur($db); // Initialiser
        $this->groupeUtilisateurModel = new GroupeUtilisateur($db); // Initialiser
        $this->enseignantModel = new Enseignant($db); // Initialiser
        $this->etudiantModel = new Etudiant($db); // Initialiser
        $this->personnelAdminModel = new PersonnelAdministratif($db); // Initialiser
        $this->sessionsModel = new Sessions($db); // Initialiser

        $this->emailService = $emailService;
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
    }

    // --- AUTHENTIFICATION ET GESTION DE SESSION ---

    public function tenterConnexion(string $identifiant, string $motDePasseClair): array
    {
        $utilisateur = $this->utilisateurModel->trouverParLoginOuEmailPrincipal($identifiant);

        if (!$utilisateur) {
            $this->journaliserActionAuthentification($identifiant, 'ECHEC_LOGIN', 'Utilisateur non trouvé');
            throw new IdentifiantsInvalidesException("Identifiants de connexion invalides.");
        }

        $numeroUtilisateur = $utilisateur['numero_utilisateur'];

        if ($this->estCompteActuellementBloque($numeroUtilisateur)) {
            $this->journaliserActionAuthentification($numeroUtilisateur, 'ECHEC_LOGIN', 'Compte bloqué');
            throw new CompteBloqueException("Votre compte est temporairement bloqué. Veuillez réessayer plus tard.");
        }

        if (!$utilisateur['email_valide']) { // Vérification de la validation de l'email
            $this->journaliserActionAuthentification($numeroUtilisateur, 'ECHEC_LOGIN', 'Email non validé');
            throw new CompteNonValideException("Votre compte n'a pas été validé. Veuillez vérifier votre e-mail.");
        }

        if (!password_verify($motDePasseClair, $utilisateur['mot_de_passe'])) {
            $this->traiterTentativeConnexionEchoueePourUtilisateur($numeroUtilisateur);
            $this->journaliserActionAuthentification($numeroUtilisateur, 'ECHEC_LOGIN', 'Mot de passe incorrect');
            throw new IdentifiantsInvalidesException("Identifiants de connexion invalides.");
        }

        // Connexion réussie
        $this->reinitialiserTentativesConnexion($numeroUtilisateur);
        $this->journaliserActionAuthentification($numeroUtilisateur, 'SUCCES_LOGIN', 'Connexion réussie');

        // Vérifier si 2FA est activé
        if ($utilisateur['preferences_2fa_active']) {
            // Ne démarre pas la session complète ici, mais stocke l'ID utilisateur pour la vérification 2FA
            $_SESSION['2fa_user_id'] = $numeroUtilisateur;
            $_SESSION['2fa_pending'] = true; // Indique qu'une vérification 2FA est en attente
            return ['status' => '2fa_required'];
        }

        $this->demarrerSessionUtilisateur($numeroUtilisateur);
        return ['status' => 'success', 'user' => $this->getUtilisateurConnecteComplet()];
    }

    public function demarrerSessionUtilisateur(string $numeroUtilisateur): void
    {
        session_regenerate_id(true); // Régénérer l'ID de session pour prévenir les attaques de fixation de session
        $_SESSION['user_id'] = $numeroUtilisateur;
        $_SESSION['last_activity'] = time();
        $_SESSION['user_data'] = $this->recupererUtilisateurCompletParNumero($numeroUtilisateur); // Charger les données complètes
        $_SESSION['user_permissions'] = $this->getPermissionsForUser($numeroUtilisateur); // Charger les permissions

        // Nettoyage des flags 2FA si la connexion complète est atteinte
        if (isset($_SESSION['2fa_pending'])) {
            unset($_SESSION['2fa_user_id']);
            unset($_SESSION['2fa_pending']);
        }

        // Mettre à jour la dernière connexion dans la base de données
        $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['derniere_connexion' => date('Y-m-d H:i:s')]);
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $numeroUtilisateur = $_SESSION['user_id'] ?? 'N/A';
            $_SESSION = []; // Vider toutes les variables de session
            session_destroy(); // Détruire la session côté serveur
            setcookie(session_name(), '', time() - 3600, '/'); // Supprimer le cookie de session

            $this->journaliserActionAuthentification($numeroUtilisateur, 'LOGOUT', 'Déconnexion réussie');
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

        // Vérifier si c'est la bonne session si un user_id est fourni
        if ($numeroUtilisateur && $_SESSION['user_id'] !== $numeroUtilisateur) {
            return false;
        }

        // Vérifier l'inactivité de session (timeout)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > ini_get('session.gc_maxlifetime'))) {
            $this->logout(); // Déconnexion automatique
            return false;
        }

        // Mettre à jour l'activité
        $_SESSION['last_activity'] = time();

        // Vérifier si l'utilisateur existe toujours et est actif en DB
        $utilisateur = $this->utilisateurModel->trouverParIdentifiant($_SESSION['user_id'], ['statut_compte']);
        if (!$utilisateur || $utilisateur['statut_compte'] !== 'actif') {
            $this->logout(); // Déconnexion si le compte est inactif/supprimé
            return false;
        }

        return true;
    }

    // --- GESTION DES COMPTES UTILISATEURS (CRUD PAR ADMIN) ---

    /**
     * Crée un compte utilisateur complet avec son profil spécifique (étudiant, enseignant, etc.).
     * Gère la génération de l'ID utilisateur unique.
     * @param array $donneesUtilisateur Données de base de l'utilisateur (login, email_principal, mot_de_passe).
     * @param array $donneesProfil Données spécifiques au profil (nom, prenom, etc.).
     * @param string $typeProfilCode Code du type de profil (ex: 'TYPE_ETUD', 'TYPE_ENS', 'TYPE_PERS_ADMIN').
     * @param bool $envoyerEmailValidation Indique si un email de validation doit être envoyé.
     * @return string Le numéro d'utilisateur créé.
     * @throws DoublonException Si le login ou l'email existe déjà.
     * @throws EmailException Si l'envoi de l'email échoue.
     * @throws \Exception Pour toute autre erreur inattendue.
     */
    public function creerCompteUtilisateurComplet(array $donneesUtilisateur, array $donneesProfil, string $typeProfilCode, bool $envoyerEmailValidation = true): string
    {
        $this->utilisateurModel->commencerTransaction();
        try {
            // 1. Vérifier unicité du login/email
            if ($this->utilisateurModel->loginExiste($donneesUtilisateur['login_utilisateur'])) {
                throw new DoublonException("Ce login est déjà utilisé.");
            }
            if ($this->utilisateurModel->emailPrincipalExiste($donneesUtilisateur['email_principal'])) {
                throw new DoublonException("Cet email est déjà utilisé.");
            }

            // 2. Récupérer type d'utilisateur et groupe par défaut
            $typeUtilisateur = $this->typeUtilisateurModel->trouverUnParCritere(['id_type_utilisateur' => $typeProfilCode]);
            if (!$typeUtilisateur) {
                throw new \Exception("Type d'utilisateur '{$typeProfilCode}' non trouvé.");
            }

            // Déterminer le groupe par défaut basé sur le type
            $idGroupeUtilisateur = '';
            switch ($typeProfilCode) {
                case 'TYPE_ADMIN':
                    $idGroupeUtilisateur = 'GRP_ADMIN_SYS';
                    break;
                case 'TYPE_ETUD':
                    $idGroupeUtilisateur = 'GRP_ETUDIANT';
                    break;
                case 'TYPE_ENS':
                    $idGroupeUtilisateur = 'GRP_ENSEIGNANT';
                    break;
                case 'TYPE_PERS_ADMIN':
                    $idGroupeUtilisateur = 'GRP_PERS_ADMIN';
                    break;
                default:
                    throw new \Exception("Groupe utilisateur par défaut non défini pour le type '{$typeProfilCode}'.");
            }
            $groupeUtilisateur = $this->groupeUtilisateurModel->trouverUnParCritere(['id_groupe_utilisateur' => $idGroupeUtilisateur]);
            if (!$groupeUtilisateur) {
                throw new \Exception("Groupe utilisateur par défaut '{$idGroupeUtilisateur}' non trouvé.");
            }

            // 3. Générer l'ID unique (numéro_utilisateur)
            // Le préfixe dépend du type d'utilisateur
            $prefixeId = match($typeProfilCode) {
                'TYPE_ETUD' => 'ETU',
                'TYPE_ENS' => 'ENS',
                'TYPE_PERS_ADMIN' => 'ADM', // Ex: ADM-2025-0001 pour Personnel Administratif
                'TYPE_ADMIN' => 'SYS', // Ex: SYS-2025-0001 pour Admin Système
                default => throw new \Exception("Préfixe d'ID non défini pour le type '{$typeProfilCode}'.")
            };

            $numeroUtilisateur = $this->idGenerator->genererIdentifiantUnique($prefixeId);

            // 4. Hacher le mot de passe
            $motDePasseHache = password_hash($donneesUtilisateur['mot_de_passe'], PASSWORD_BCRYPT);

            // 5. Préparer les données de l'utilisateur de base
            $utilisateurData = [
                'numero_utilisateur' => $numeroUtilisateur,
                'login_utilisateur' => $donneesUtilisateur['login_utilisateur'],
                'email_principal' => $donneesUtilisateur['email_principal'],
                'mot_de_passe' => $motDePasseHache,
                'id_type_utilisateur' => $typeUtilisateur['id_type_utilisateur'],
                'id_groupe_utilisateur' => $groupeUtilisateur['id_groupe_utilisateur'],
                'id_niveau_acces_donne' => $donneesUtilisateur['id_niveau_acces_donne'] ?? 'ACCES_RESTREINT', // Valeur par défaut
                'statut_compte' => 'en_attente_validation', // Par défaut pour les nouveaux comptes
                'date_creation' => date('Y-m-d H:i:s')
            ];

            // Générer et stocker le token de validation email
            $tokenValidationEmailClair = bin2hex(random_bytes(32));
            $utilisateurData['token_validation_email'] = hash('sha256', $tokenValidationEmailClair);

            // 6. Créer l'utilisateur de base
            $this->utilisateurModel->creer($utilisateurData);

            // 7. Créer le profil spécifique et le lier
            $profilData = array_merge($donneesProfil, [
                // Le numéro d'utilisateur généré est aussi l'ID primaire du profil
                // pour Etudiant, Enseignant, PersonnelAdministratif, cela sera leur numéro unique
                match($typeProfilCode) {
                    'TYPE_ETUD' => 'numero_carte_etudiant',
                    'TYPE_ENS' => 'numero_enseignant',
                    'TYPE_PERS_ADMIN' => 'numero_personnel_administratif',
                    default => '' // Pour les types sans profil dédié (ex: Admin système géré directement par Utilisateur)
                } => $numeroUtilisateur,
                'numero_utilisateur' => $numeroUtilisateur // Liaison FK
            ]);

            switch ($typeProfilCode) {
                case 'TYPE_ETUD':
                    $this->etudiantModel->creer($profilData);
                    break;
                case 'TYPE_ENS':
                    $this->enseignantModel->creer($profilData);
                    break;
                case 'TYPE_PERS_ADMIN':
                    $this->personnelAdminModel->creer($profilData);
                    break;
                // Aucun profil spécifique n'est créé pour TYPE_ADMIN (géré directement par Utilisateur)
            }

            // 8. Journaliser l'action
            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'CREATION_COMPTE', "Compte utilisateur de type {$typeProfilCode} créé", $numeroUtilisateur, 'Utilisateur');

            $this->utilisateurModel->validerTransaction();

            // 9. Envoyer l'email de validation si requis
            if ($envoyerEmailValidation) {
                try {
                    $this->envoyerEmailValidationCompte($numeroUtilisateur, $tokenValidationEmailClair);
                } catch (EmailException $e) {
                    // Log the email sending failure, but do not block user creation
                    error_log("Échec de l'envoi de l'email de validation pour {$donneesUtilisateur['email_principal']}: " . $e->getMessage());
                }
            }
            return $numeroUtilisateur;

        } catch (DoublonException $e) {
            $this->utilisateurModel->annulerTransaction();
            throw $e;
        } catch (\Exception $e) {
            $this->utilisateurModel->annulerTransaction();
            $this->supervisionService->enregistrerAction($donneesUtilisateur['login_utilisateur'] ?? 'N/A', 'ECHEC_CREATION_COMPTE', "Erreur lors de la création du compte: " . $e->getMessage());
            throw $e; // Remplacer par une exception plus spécifique si possible
        }
    }

    /**
     * Récupère les données complètes d'un utilisateur incluant son profil spécifique.
     * @param string $numeroUtilisateur Le numéro unique de l'utilisateur.
     * @return array|null Les données complètes de l'utilisateur ou null.
     */
    public function recupererUtilisateurCompletParNumero(string $numeroUtilisateur): ?array
    {
        $utilisateur = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur);
        if (!$utilisateur) {
            return null;
        }

        $profilData = [];
        switch ($utilisateur['id_type_utilisateur']) {
            case 'TYPE_ETUD':
                $profilData = $this->etudiantModel->trouverParNumeroCarteEtudiant($numeroUtilisateur);
                break;
            case 'TYPE_ENS':
                $profilData = $this->enseignantModel->trouverParNumeroEnseignant($numeroUtilisateur);
                break;
            case 'TYPE_PERS_ADMIN':
                $profilData = $this->personnelAdminModel->trouverParNumeroPersonnelAdministratif($numeroUtilisateur);
                break;
        }
        return array_merge($utilisateur, ['profil' => $profilData]);
    }

    public function recupererUtilisateurCompletParEmailPrincipal(string $emailPrincipal): ?array
    {
        $utilisateur = $this->utilisateurModel->trouverParEmailPrincipal($emailPrincipal);
        if (!$utilisateur) {
            return null;
        }
        return $this->recupererUtilisateurCompletParNumero($utilisateur['numero_utilisateur']);
    }

    public function recupererUtilisateurCompletParLogin(string $login): ?array
    {
        $utilisateur = $this->utilisateurModel->trouverParLoginUtilisateur($login);
        if (!$utilisateur) {
            return null;
        }
        return $this->recupererUtilisateurCompletParNumero($utilisateur['numero_utilisateur']);
    }

    /**
     * Liste tous les utilisateurs avec leurs profils associés, avec pagination et filtres.
     * @param array $criteres Critères de recherche (ex: ['statut_compte' => 'actif']).
     * @param int $page Numéro de page pour la pagination.
     * @param int $elementsParPage Nombre d'éléments par page.
     * @return array Tableau d'utilisateurs.
     */
    public function listerUtilisateursAvecProfils(array $criteres = [], int $page = 1, int $elementsParPage = 20): array
    {
        $offset = ($page - 1) * $elementsParPage;
        $utilisateurs = $this->utilisateurModel->trouverParCritere($criteres, ['*'], 'AND', null, $elementsParPage, $offset);

        foreach ($utilisateurs as &$user) {
            $user['profil'] = [];
            switch ($user['id_type_utilisateur']) {
                case 'TYPE_ETUD':
                    $user['profil'] = $this->etudiantModel->trouverParNumeroCarteEtudiant($user['numero_utilisateur']) ?? [];
                    break;
                case 'TYPE_ENS':
                    $user['profil'] = $this->enseignantModel->trouverParNumeroEnseignant($user['numero_utilisateur']) ?? [];
                    break;
                case 'TYPE_PERS_ADMIN':
                    $user['profil'] = $this->personnelAdminModel->trouverParNumeroPersonnelAdministratif($user['numero_utilisateur']) ?? [];
                    break;
            }
        }
        return $utilisateurs;
    }

    /**
     * Met à jour les informations d'un profil utilisateur spécifique.
     * Ne gère PAS les changements de mot de passe, login ou email principal (ceux-ci ont des méthodes dédiées).
     * @param string $numeroUtilisateur Le numéro unique de l'utilisateur.
     * @param string $typeProfilCode Le code du type de profil ('TYPE_ETUD', 'TYPE_ENS', etc.).
     * @param array $donneesProfil Les données spécifiques au profil à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     */
    public function mettreAJourProfilUtilisateur(string $numeroUtilisateur, string $typeProfilCode, array $donneesProfil): bool
    {
        try {
            $this->utilisateurModel->commencerTransaction();
            $success = false;

            switch ($typeProfilCode) {
                case 'TYPE_ETUD':
                    $success = $this->etudiantModel->mettreAJourParIdentifiant($numeroUtilisateur, $donneesProfil);
                    break;
                case 'TYPE_ENS':
                    $success = $this->enseignantModel->mettreAJourParIdentifiant($numeroUtilisateur, $donneesProfil);
                    break;
                case 'TYPE_PERS_ADMIN':
                    $success = $this->personnelAdminModel->mettreAJourParIdentifiant($numeroUtilisateur, $donneesProfil);
                    break;
                case 'TYPE_ADMIN': // Les admins n'ont pas de profil dédié, leurs infos sont dans Utilisateur
                    // Gérer les champs spécifiques à Utilisateur directement ici si besoin
                    // Ex: $success = $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['photo_profil' => $donneesProfil['photo_profil']]);
                    $success = true; // Ou false si aucune mise à jour n'est appliquée
                    break;
                default:
                    throw new \InvalidArgumentException("Type de profil non reconnu pour la mise à jour : {$typeProfilCode}");
            }

            if ($success) {
                $this->utilisateurModel->validerTransaction();
                $this->supervisionService->enregistrerAction($numeroUtilisateur, 'MISE_AJOUR_PROFIL', "Profil de type {$typeProfilCode} mis à jour");
                return true;
            }
            $this->utilisateurModel->annulerTransaction();
            return false;

        } catch (\Exception $e) {
            $this->utilisateurModel->annulerTransaction();
            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'ECHEC_MISE_AJOUR_PROFIL', "Erreur lors de la mise à jour du profil: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Met à jour les informations de base d'un utilisateur par un administrateur.
     * Peut inclure le login ou l'email principal, avec vérification d'unicité.
     * @param string $numeroUtilisateur Le numéro unique de l'utilisateur à mettre à jour.
     * @param array $donneesCompte Les données du compte utilisateur à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     * @throws DoublonException Si le login ou l'email est déjà utilisé par un autre utilisateur.
     */
    public function mettreAJourCompteUtilisateurParAdmin(string $numeroUtilisateur, array $donneesCompte): bool
    {
        $this->utilisateurModel->commencerTransaction();
        try {
            $utilisateurActuel = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['login_utilisateur', 'email_principal']);
            if (!$utilisateurActuel) {
                throw new ElementNonTrouveException("Utilisateur non trouvé.");
            }

            // Vérifier l'unicité si login ou email est modifié
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

            // Mettre à jour les champs
            $success = $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, $donneesCompte);

            if ($success) {
                $this->utilisateurModel->validerTransaction();
                $this->supervisionService->enregistrerAction($numeroUtilisateur, 'MISE_AJOUR_COMPTE_ADMIN', "Compte utilisateur mis à jour par l'administrateur");
                // Mettre à jour les permissions en temps réel si le groupe utilisateur a changé
                if (isset($donneesCompte['id_groupe_utilisateur'])) {
                    $this->synchroniserPermissionsSessionsUtilisateur($numeroUtilisateur);
                }
                return true;
            }
            $this->utilisateurModel->annulerTransaction();
            return false;

        } catch (DoublonException $e) {
            $this->utilisateurModel->annulerTransaction();
            throw $e;
        } catch (\Exception $e) {
            $this->utilisateurModel->annulerTransaction();
            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'ECHEC_MISE_AJOUR_COMPTE_ADMIN', "Erreur mise à jour compte par admin: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Supprime un utilisateur et son profil associé.
     * @param string $numeroUtilisateur Le numéro unique de l'utilisateur à supprimer.
     * @return bool Vrai si la suppression a réussi, faux sinon.
     * @throws \Exception En cas d'erreur de suppression ou de profil non trouvé.
     */
    public function supprimerUtilisateur(string $numeroUtilisateur): bool
    {
        $this->utilisateurModel->commencerTransaction();
        try {
            $utilisateur = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['id_type_utilisateur']);
            if (!$utilisateur) {
                throw new ElementNonTrouveException("Utilisateur à supprimer non trouvé.");
            }

            // Supprimer le profil spécifique d'abord (si cascade n'est pas déjà gérée par DB)
            switch ($utilisateur['id_type_utilisateur']) {
                case 'TYPE_ETUD':
                    $this->etudiantModel->supprimerParIdentifiant($numeroUtilisateur);
                    break;
                case 'TYPE_ENS':
                    $this->enseignantModel->supprimerParIdentifiant($numeroUtilisateur);
                    break;
                case 'TYPE_PERS_ADMIN':
                    $this->personnelAdminModel->supprimerParIdentifiant($numeroUtilisateur);
                    break;
            }

            // Supprimer l'historique des mots de passe
            $this->historiqueMdpModel->supprimerParCles(['numero_utilisateur' => $numeroUtilisateur]);

            // Supprimer l'utilisateur de base
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
            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'ECHEC_SUPPRESSION_COMPTE', "Erreur lors de la suppression du compte: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Change le statut du compte d'un utilisateur.
     * @param string $numeroUtilisateur Le numéro unique de l'utilisateur.
     * @param string $nouveauStatut Le nouveau statut ('actif', 'inactif', 'bloque', 'archive').
     * @param string|null $raison Optionnel: la raison du changement de statut.
     * @return bool Vrai si le statut a été modifié, faux sinon.
     */
    public function changerStatutDuCompte(string $numeroUtilisateur, string $nouveauStatut, ?string $raison = null): bool
    {
        $this->utilisateurModel->commencerTransaction();
        try {
            $success = $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['statut_compte' => $nouveauStatut]);

            if ($success) {
                // Si le compte est bloqué, définir la date de déblocage
                if ($nouveauStatut === 'bloque') {
                    $blocageJusqua = date('Y-m-d H:i:s', time() + self::LOCKOUT_TIME_MINUTES * 60);
                    $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['compte_bloque_jusqua' => $blocageJusqua]);
                } elseif ($nouveauStatut === 'actif' && $utilisateur = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['compte_bloque_jusqua'])) {
                    // Si le compte est réactivé, annuler le blocage
                    if ($utilisateur['compte_bloque_jusqua'] !== null) {
                        $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['compte_bloque_jusqua' => null]);
                    }
                }

                $this->utilisateurModel->validerTransaction();
                $this->supervisionService->enregistrerAction($numeroUtilisateur, 'CHANGEMENT_STATUT_COMPTE', "Statut du compte changé à '{$nouveauStatut}'" . ($raison ? " ({$raison})" : ""));
                // Mettre à jour les permissions en temps réel si le statut impacte les droits
                $this->synchroniserPermissionsSessionsUtilisateur($numeroUtilisateur);
                return true;
            }
            $this->utilisateurModel->annulerTransaction();
            return false;
        } catch (\Exception $e) {
            $this->utilisateurModel->annulerTransaction();
            $this->supervisionService->enregistrerAction($numeroUtilisateur, 'ECHEC_CHANGEMENT_STATUT_COMPTE', "Erreur changement statut compte: " . $e->getMessage());
            throw $e;
        }
    }

    // --- GESTION DES MOTS DE PASSE ---

    public function modifierMotDePasse(string $numeroUtilisateur, string $nouveauMotDePasseClair, ?string $ancienMotDePasseClair = null, bool $isAdminReset = false): bool
    {
        $this->utilisateurModel->commencerTransaction();
        try {
            $utilisateur = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['mot_de_passe']);
            if (!$utilisateur) {
                throw new ElementNonTrouveException("Utilisateur non trouvé.");
            }

            if (!$isAdminReset) { // Si ce n'est pas un reset par admin, vérifier l'ancien mot de passe
                if (!password_verify($ancienMotDePasseClair, $utilisateur['mot_de_passe'])) {
                    throw new MotDePasseInvalideException("L'ancien mot de passe est incorrect.");
                }
            }

            $this->verifierRobustesseMotDePasse($nouveauMotDePasseClair);

            // Vérifier l'historique des mots de passe
            if ($this->estNouveauMotDePasseDansHistorique($numeroUtilisateur, $nouveauMotDePasseClair)) {
                throw new MotDePasseInvalideException("Le nouveau mot de passe a déjà été utilisé récemment. Veuillez en choisir un autre.");
            }

            $nouveauMotDePasseHache = password_hash($nouveauMotDePasseClair, PASSWORD_BCRYPT);

            // Mettre à jour le mot de passe de l'utilisateur
            $success = $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['mot_de_passe' => $nouveauMotDePasseHache]);

            if ($success) {
                // Enregistrer l'ancien mot de passe dans l'historique
                $this->historiqueMdpModel->creer([
                    'id_historique_mdp' => $this->idGenerator->genererIdentifiantUnique('HMP'), // Générer un ID unique
                    'numero_utilisateur' => $numeroUtilisateur,
                    'mot_de_passe_hache' => $utilisateur['mot_de_passe']
                ]);

                // Nettoyer les tokens de réinitialisation si existants
                $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['token_reset_mdp' => null, 'date_expiration_token_reset' => null]);

                $this->utilisateurModel->validerTransaction();
                $this->journaliserActionAuthentification($numeroUtilisateur, 'CHANGEMENT_MDP', $isAdminReset ? 'Mot de passe réinitialisé par admin' : 'Mot de passe modifié par utilisateur');
                return true;
            }
            $this->utilisateurModel->annulerTransaction();
            return false;

        } catch (\Exception $e) { // Capturer toutes les exceptions pour rollback
            $this->utilisateurModel->annulerTransaction();
            $this->journaliserActionAuthentification($numeroUtilisateur, 'ECHEC_CHANGEMENT_MDP', "Erreur lors du changement de mot de passe: " . $e->getMessage());
            throw $e;
        }
    }

    public function demanderReinitialisationMotDePasse(string $emailPrincipal): void
    {
        $utilisateur = $this->utilisateurModel->trouverParEmailPrincipal($emailPrincipal);

        if (!$utilisateur || !$utilisateur['email_valide']) {
            // Pour des raisons de sécurité, ne pas indiquer si l'email existe ou non.
            // Simplement logguer et retourner un message générique.
            $this->journaliserActionAuthentification($emailPrincipal, 'DEMANDE_RESET_MDP', 'Tentative de réinitialisation pour email non valide/inexistant');
            return; // Ou lancer une exception générique non informative
        }

        $numeroUtilisateur = $utilisateur['numero_utilisateur'];
        $this->utilisateurModel->commencerTransaction();
        try {
            $tokenClair = bin2hex(random_bytes(32)); // Générer un token sécurisé
            $tokenHache = hash('sha256', $tokenClair); // Hacher le token pour stockage
            $dateExpiration = date('Y-m-d H:i:s', time() + (2 * 3600)); // Token valide 2 heures

            $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, [
                'token_reset_mdp' => $tokenHache, // Stocker le hachage
                'date_expiration_token_reset' => $dateExpiration
            ]);
            $this->utilisateurModel->validerTransaction();

            // Préparer les données pour l'email
            $resetLink = 'http://localhost/reset-password?token=' . $tokenClair; // Assurez-vous que c'est l'URL réelle
            $emailData = [
                'destinataire_email' => $emailPrincipal,
                'sujet' => 'Réinitialisation de votre mot de passe',
                'corps_html' => "<p>Bonjour,</p><p>Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur ce lien pour continuer : <a href=\"{$resetLink}\">{$resetLink}</a></p><p>Ce lien expirera dans 2 heures.</p>",
                'corps_texte' => "Bonjour,\nVous avez demandé à réinitialiser votre mot de passe. Cliquez sur ce lien pour continuer : {$resetLink}\nCe lien expirera dans 2 heures.",
                'modele_email' => 'reset_password', // Nom d'un modèle d'email si vous en avez un
                'variables_modele' => ['reset_link' => $resetLink]
            ];

            $this->emailService->envoyerEmail($emailData);
            $this->journaliserActionAuthentification($numeroUtilisateur, 'DEMANDE_RESET_MDP', 'Email de réinitialisation envoyé');

        } catch (\Exception $e) {
            $this->utilisateurModel->annulerTransaction();
            $this->journaliserActionAuthentification($numeroUtilisateur, 'ECHEC_DEMANDE_RESET_MDP', "Erreur envoi email reset mdp: " . $e->getMessage());
            throw new EmailException("Erreur lors de l'envoi de l'e-mail de réinitialisation. Veuillez réessayer plus tard.");
        }
    }

    public function reinitialiserMotDePasseApresValidationToken(string $tokenClair, string $nouveauMotDePasseClair): bool
    {
        $utilisateur = $this->utilisateurModel->trouverParTokenResetMdp(hash('sha256', $tokenClair)); // Rechercher par hachage du token
        if (!$utilisateur) {
            throw new TokenInvalideException("Le lien de réinitialisation est invalide ou a déjà été utilisé.");
        }

        if (new \DateTime() > new \DateTime($utilisateur['date_expiration_token_reset'])) {
            throw new TokenExpireException("Le lien de réinitialisation a expiré.");
        }

        // Appeler la méthode générique de modification de mot de passe, en indiquant que c'est un reset par admin
        return $this->modifierMotDePasse($utilisateur['numero_utilisateur'], $nouveauMotDePasseClair, null, true);
    }

    // --- AUTHENTIFICATION A DEUX FACTEURS (2FA) ---

    /**
     * Génère et stocke le secret 2FA pour un utilisateur.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return array Tableau contenant le secret et l'URL du QR code.
     */
    public function genererEtStockerSecret2FA(string $numeroUtilisateur): array
    {
        // Utiliser une bibliothèque 2FA (ex: https://github.com/RobThree/TwoFactorAuth)
        // Pour l'exemple, nous simulerons le secret et l'URL
        $secret = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456'; // Générer un secret réel de 160 bits (20 caractères)
        $issuer = 'GestionMySoutenance';
        $userEmail = $this->recupererEmailSourceDuProfil($numeroUtilisateur); // Obtenir l'email de l'utilisateur

        // Simuler l'URL du QR code (nécessite une lib 2FA réelle)
        $qrCodeUrl = "otpauth://totp/{$issuer}:{$userEmail}?secret={$secret}&issuer={$issuer}";

        $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['secret_2fa' => $secret]);
        $this->journaliserActionAuthentification($numeroUtilisateur, 'GENERATION_2FA_SECRET', 'Secret 2FA généré');

        return ['secret' => $secret, 'qr_code_url' => $qrCodeUrl];
    }

    /**
     * Active l'authentification 2FA pour un utilisateur après vérification du code.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $codeTOTP Le code TOTP soumis par l'utilisateur.
     * @return bool Vrai si l'activation réussit, faux sinon.
     * @throws IdentifiantsInvalidesException Si le code TOTP est incorrect.
     */
    public function activerAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTP): bool
    {
        $utilisateur = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['secret_2fa']);
        if (!$utilisateur || empty($utilisateur['secret_2fa'])) {
            throw new \Exception("Impossible d'activer 2FA: secret non trouvé.");
        }

        // Vérifier le code TOTP (utiliser une lib 2FA réelle ici)
        // Simuler la vérification pour l'exemple
        $isCodeValid = ($codeTOTP === '123456'); // Remplacer par $tfa->verifyCode($utilisateur['secret_2fa'], $codeTOTP);

        if (!$isCodeValid) {
            $this->journaliserActionAuthentification($numeroUtilisateur, 'ECHEC_ACTIVATION_2FA', 'Code 2FA incorrect lors de l\'activation');
            throw new IdentifiantsInvalidesException("Code de vérification 2FA incorrect.");
        }

        $success = $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['preferences_2fa_active' => true]);
        if ($success) {
            $this->journaliserActionAuthentification($numeroUtilisateur, 'ACTIVATION_2FA', '2FA activée');
        }
        return $success;
    }

    /**
     * Désactive l'authentification 2FA pour un utilisateur.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return bool Vrai si la désactivation réussit, faux sinon.
     */
    public function desactiverAuthentificationDeuxFacteurs(string $numeroUtilisateur): bool
    {
        $success = $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['preferences_2fa_active' => false, 'secret_2fa' => null]);
        if ($success) {
            $this->journaliserActionAuthentification($numeroUtilisateur, 'DESACTIVATION_2FA', '2FA désactivée');
        }
        return $success;
    }

    /**
     * Vérifie un code TOTP lors d'une connexion avec 2FA.
     * @param string $numeroUtilisateur L'ID de l'utilisateur en attente de vérification 2FA.
     * @param string $codeTOTP Le code TOTP soumis par l'utilisateur.
     * @return bool Vrai si le code est valide, faux sinon.
     */
    public function verifierCodeAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTP): bool
    {
        $utilisateur = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['secret_2fa']);
        if (!$utilisateur || empty($utilisateur['secret_2fa'])) {
            return false; // Pas de secret 2FA configuré
        }

        // Utiliser une bibliothèque 2FA réelle ici
        // Ex: $tfa = new TwoFactorAuth(); return $tfa->verifyCode($utilisateur['secret_2fa'], $codeTOTP);
        $isCodeValid = ($codeTOTP === '123456'); // Simuler pour l'exemple

        if ($isCodeValid) {
            $this->journaliserActionAuthentification($numeroUtilisateur, 'VERIF_2FA_SUCCES', 'Code 2FA validé');
        } else {
            $this->journaliserActionAuthentification($numeroUtilisateur, 'VERIF_2FA_ECHEC', 'Code 2FA incorrect');
        }
        return $isCodeValid;
    }

    // --- METHODES UTILITAIRES ET INTERNES ---

    /**
     * Gère le nombre de tentatives de connexion échouées et bloque le compte si nécessaire.
     * @param string $numeroUtilisateur Le numéro unique de l'utilisateur.
     */
    public function traiterTentativeConnexionEchoueePourUtilisateur(string $numeroUtilisateur): void
    {
        $utilisateur = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['tentatives_connexion_echouees']);
        if (!$utilisateur) {
            return;
        }

        $nouvellesTentatives = $utilisateur['tentatives_connexion_echouees'] + 1;
        $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['tentatives_connexion_echouees' => $nouvellesTentatives]);

        if ($nouvellesTentatives >= self::MAX_LOGIN_ATTEMPTS) {
            $this->changerStatutDuCompte($numeroUtilisateur, 'bloque', 'Trop de tentatives de connexion échouées');
            $this->journaliserActionAuthentification($numeroUtilisateur, 'COMPTE_BLOQUE', 'Compte bloqué après tentatives échouées');
        }
    }

    /**
     * Réinitialise le compteur de tentatives de connexion échouées.
     * @param string $numeroUtilisateur Le numéro unique de l'utilisateur.
     */
    public function reinitialiserTentativesConnexion(string $numeroUtilisateur): void
    {
        $this->utilisateurModel->mettreAJourChamps($numeroUtilisateur, ['tentatives_connexion_echouees' => 0, 'compte_bloque_jusqua' => null]);
    }

    /**
     * Vérifie si un compte est actuellement bloqué.
     * @param string $numeroUtilisateur Le numéro unique de l'utilisateur.
     * @return bool Vrai si le compte est bloqué, faux sinon.
     */
    public function estCompteActuellementBloque(string $numeroUtilisateur): bool
    {
        $utilisateur = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['compte_bloque_jusqua', 'statut_compte']);
        if (!$utilisateur) {
            return false;
        }

        // Si le statut est 'bloque' et qu'il y a une date de déblocage future
        if ($utilisateur['statut_compte'] === 'bloque' && $utilisateur['compte_bloque_jusqua'] !== null) {
            if (new \DateTime() < new \DateTime($utilisateur['compte_bloque_jusqua'])) {
                return true;
            } else {
                // Le temps de blocage est écoulé, réactiver le compte
                $this->changerStatutDuCompte($numeroUtilisateur, 'actif', 'Déblocage automatique après expiration');
                return false;
            }
        }
        // Si le statut est 'bloque' sans date de déblocage (blocage permanent ou manuel), il reste bloqué
        if ($utilisateur['statut_compte'] === 'bloque') {
            return true;
        }

        return false;
    }

    /**
     * Vérifie la robustesse d'un mot de passe clair.
     * @param string $motDePasse Le mot de passe en clair.
     * @throws MotDePasseInvalideException Si le mot de passe ne respecte pas les règles.
     */
    public function verifierRobustesseMotDePasse(string $motDePasse): void
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

    /**
     * Vérifie si un nouveau mot de passe a déjà été utilisé récemment par l'utilisateur.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $nouveauMotDePasseClair Le nouveau mot de passe en clair.
     * @return bool Vrai si le mot de passe est dans l'historique récent, faux sinon.
     */
    public function estNouveauMotDePasseDansHistorique(string $numeroUtilisateur, string $nouveauMotDePasseClair): bool
    {
        $historique = $this->historiqueMdpModel->recupererHistoriquePourUtilisateur($numeroUtilisateur, 3); // Vérifier les 3 derniers mots de passe
        foreach ($historique as $entry) {
            if (password_verify($nouveauMotDePasseClair, $entry['mot_de_passe_hache'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Envoie un email de validation de compte à l'utilisateur.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $tokenClair Le token de validation en clair.
     * @throws EmailException Si l'envoi de l'email échoue.
     * @throws ElementNonTrouveException Si l'utilisateur n'est pas trouvé.
     */
    public function envoyerEmailValidationCompte(string $numeroUtilisateur, string $tokenClair): void
    {
        $utilisateur = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['email_principal']);
        if (!$utilisateur || empty($utilisateur['email_principal'])) {
            throw new ElementNonTrouveException("Email de l'utilisateur non trouvé pour l'envoi de validation.");
        }

        $validationLink = 'http://localhost/validate-email?token=' . $tokenClair; // Assurez-vous que c'est l'URL réelle
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

    /**
     * Valide le compte utilisateur via le token d'email.
     * @param string $tokenClair Le token de validation en clair.
     * @return bool Vrai si le compte est validé, faux sinon.
     * @throws TokenInvalideException Si le token est invalide.
     */
    public function validerCompteEmailViaToken(string $tokenClair): bool
    {
        $tokenHache = hash('sha256', $tokenClair);
        $utilisateur = $this->utilisateurModel->trouverParTokenValidationEmailHache($tokenHache);

        if (!$utilisateur) {
            throw new TokenInvalideException("Le lien de validation est invalide ou a déjà été utilisé.");
        }

        $success = $this->utilisateurModel->mettreAJourChamps($utilisateur['numero_utilisateur'], [
            'email_valide' => true,
            'statut_compte' => 'actif', // Passer le compte en actif après validation de l'email
            'token_validation_email' => null // Invalider le token
        ]);

        if ($success) {
            $this->journaliserActionAuthentification($utilisateur['numero_utilisateur'], 'VALIDATION_EMAIL', 'Email de compte validé');
        }
        return $success;
    }

    /**
     * Récupère l'email principal de l'utilisateur à partir de son profil spécifique.
     * @param string $numeroUtilisateur
     * @return string L'email principal.
     * @throws ElementNonTrouveException Si l'utilisateur ou son profil n'est pas trouvé.
     */
    private function recupererEmailSourceDuProfil(string $numeroUtilisateur): string
    {
        $utilisateurComplet = $this->recupererUtilisateurCompletParNumero($numeroUtilisateur);
        if (!$utilisateurComplet) {
            throw new ElementNonTrouveException("Utilisateur non trouvé pour récupérer l'email.");
        }
        return $utilisateurComplet['email_principal'];
    }

    /**
     * Récupère la liste des permissions (traitements) pour un utilisateur donné.
     * @param string $numeroUtilisateur Le numéro unique de l'utilisateur.
     * @return array La liste des codes de permission.
     */
    public function getPermissionsForUser(string $numeroUtilisateur): array
    {
        // Récupérer les informations de l'utilisateur pour connaître son groupe
        $utilisateur = $this->utilisateurModel->trouverParNumeroUtilisateur($numeroUtilisateur, ['id_groupe_utilisateur']);
        if (!$utilisateur || empty($utilisateur['id_groupe_utilisateur'])) {
            return [];
        }
        // Utiliser le ServicePermissions pour récupérer les permissions du groupe
        $servicePermissions = new \App\Backend\Service\Permissions\ServicePermissions(
            $this->db,
            $this->supervisionService,
            $this->utilisateurModel, // Passez Utilisateur si besoin
            $this->groupeUtilisateurModel, // Passé GroupeUtilisateur
            $this->typeUtilisateurModel // Passé TypeUtilisateur
        );
        return $servicePermissions->recupererPermissionsPourGroupe($utilisateur['id_groupe_utilisateur']);
    }

    /**
     * Journalise une action liée à l'authentification.
     * @param string $idUtilisateur L'ID de l'utilisateur concerné ou un identifiant (email/login).
     * @param string $libelleAction Le libellé de l'action (ex: 'SUCCES_LOGIN', 'ECHEC_LOGIN').
     * @param string $details Détails supplémentaires sur l'action.
     */
    public function journaliserActionAuthentification(string $idUtilisateur, string $libelleAction, string $details): void
    {
        $this->supervisionService->enregistrerAction($idUtilisateur, $libelleAction, $details, $idUtilisateur, 'Utilisateur');
    }

    /**
     * Synchronise les permissions d'un utilisateur dans sa session DB active.
     * Appelé après un changement de rôle ou de statut qui impacte les permissions.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur dont les permissions doivent être rafraîchies.
     */
    public function synchroniserPermissionsSessionsUtilisateur(string $numeroUtilisateur): void
    {
        $sessions = $this->sessionsModel->trouverSessionsParUtilisateur($numeroUtilisateur);
        $newPermissions = $this->getPermissionsForUser($numeroUtilisateur);

        foreach ($sessions as $session) {
            // Déserialiser les données de session
            $sessionData = unserialize($session['session_data']);

            // Mettre à jour le tableau des permissions
            $sessionData['user_permissions'] = $newPermissions;
            if (isset($sessionData['user_data'])) {
                $sessionData['user_data']['user_permissions'] = $newPermissions; // Mettre à jour aussi dans user_data si stocké
            }

            // Resérialiser et mettre à jour en DB
            $this->sessionsModel->mettreAJourParIdentifiant($session['session_id'], [
                'session_data' => serialize($sessionData),
                'session_last_activity' => time() // Mettre à jour l'activité pour éviter un GC précoce
            ]);
        }
        $this->journaliserActionAuthentification($numeroUtilisateur, 'SYNCHRONISATION_RBAC', 'Permissions de session synchronisées.');
    }
}