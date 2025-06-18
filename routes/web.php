<?php

// Importer toutes les classes de contrôleurs nécessaires
use App\Backend\Controller\Administration\AdminDashboardController;
use App\Backend\Controller\Administration\ConfigSystemeController;
use App\Backend\Controller\Administration\GestionAcadController;
use App\Backend\Controller\Administration\HabilitationController;
use App\Backend\Controller\Administration\ReferentialController;
use App\Backend\Controller\Administration\ReportingController;
use App\Backend\Controller\Administration\SupervisionController;
use App\Backend\Controller\Administration\UtilisateurController;
use App\Backend\Controller\AssetController;
use App\Backend\Controller\AuthentificationController;
use App\Backend\Controller\Common\NotificationController;
use App\Backend\Controller\Commission\CommissionDashboardController;
use App\Backend\Controller\Commission\CommunicationCommissionController;
use App\Backend\Controller\Commission\CorrectionCommissionController;
use App\Backend\Controller\Commission\PvController;
use App\Backend\Controller\Commission\ValidationRapportController;
use App\Backend\Controller\DashboardController;
use App\Backend\Controller\Etudiant\DocumentEtudiantController;
use App\Backend\Controller\Etudiant\EtudiantDashboardController;
use App\Backend\Controller\Etudiant\ProfilEtudiantController;
use App\Backend\Controller\Etudiant\RapportController as EtudiantRapportController; // Alias pour éviter conflit de nom si un autre RapportController existe
use App\Backend\Controller\Etudiant\ReclamationEtudiantController;
use App\Backend\Controller\HomeController; // Le contrôleur pour la page d'accueil
use App\Backend\Controller\PersonnelAdministratif\CommunicationInterneController;
use App\Backend\Controller\PersonnelAdministratif\ConformiteController;
use App\Backend\Controller\PersonnelAdministratif\PersonnelDashboardController;
use App\Backend\Controller\PersonnelAdministratif\ScolariteController;
use FastRoute\RouteCollector;

return function(RouteCollector $r) {
    // == Routes Publiques ==
    $r->addRoute('GET', '/', [HomeController::class, 'home']); // Page d'accueil, redirige vers login ou dashboard

    // Authentification
    $r->addRoute('GET', '/login', [AuthentificationController::class, 'showLoginForm']);    // Afficher le formulaire de connexion
    $r->addRoute('POST', '/login', [AuthentificationController::class, 'handleLogin']);   // Traiter la soumission du formulaire de connexion
    $r->addRoute('GET', '/logout', [AuthentificationController::class, 'logout']);         // Déconnexion

    // Validation d'email et réinitialisation de mot de passe
    $r->addRoute('GET', '/validate-email', [AuthentificationController::class, 'handleValidateEmailToken']);
    $r->addRoute('GET', '/forgot-password', [AuthentificationController::class, 'showForgotPasswordForm']);
    $r->addRoute('POST', '/forgot-password', [AuthentificationController::class, 'handleForgotPasswordRequest']);
    $r->addRoute('GET', '/reset-password', [AuthentificationController::class, 'showResetPasswordForm']);
    $r->addRoute('POST', '/reset-password', [AuthentificationController::class, 'handleResetPasswordSubmission']);

    // Authentification à deux facteurs (2FA)
    $r->addRoute('GET', '/login-2fa', [AuthentificationController::class, 'show2FAForm']);
    $r->addRoute('POST', '/login-2fa', [AuthentificationController::class, 'handle2FASubmission']);


    // == Routes Protégées (nécessitent une connexion) ==
    $r->addGroup('/dashboard', function (RouteCollector $r) {
        $r->addRoute('GET', '', [DashboardController::class, 'index']); // Tableau de bord principal (redirige selon le rôle)
    });

    // Profil utilisateur (commun à tous les rôles connectés)
    $r->addGroup('/profile', function (RouteCollector $r) {
        // $r->addRoute('GET', '', [ProfilEtudiantController::class, 'showProfile']); // Exemple, à adapter si un contrôleur de profil générique existe
        $r->addRoute('GET', '/change-password', [AuthentificationController::class, 'showChangePasswordForm']);
        $r->addRoute('POST', '/change-password', [AuthentificationController::class, 'handleChangePassword']);
        $r->addRoute('GET', '/setup-2fa', [AuthentificationController::class, 'showSetup2FAForm']);
        $r->addRoute('POST', '/activate-2fa', [AuthentificationController::class, 'handleActivate2FA']);
        $r->addRoute('POST', '/disable-2fa', [AuthentificationController::class, 'handleDisable2FA']);
    });

    $r->addGroup('/admin', function (FastRoute\RouteCollector $r) {
        // ... (vos autres routes admin)

        // NOUVELLE ROUTE POUR LA LISTE DES UTILISATEURS


        // ...
    });


    // --- Routes spécifiques aux Étudiants ---
    $r->addGroup('/etudiant', function (RouteCollector $r) {
        $r->addRoute('GET', '/dashboard', [EtudiantDashboardController::class, 'index']);
        $r->addRoute('GET', '/profil', [ProfilEtudiantController::class, 'show']);
        $r->addRoute('POST', '/profil/update', [ProfilEtudiantController::class, 'update']);

        $r->addRoute('GET', '/rapports/soumettre', [EtudiantRapportController::class, 'showSoumettreRapportForm']);
        $r->addRoute('POST', '/rapports/soumettre', [EtudiantRapportController::class, 'handleSoumettreRapport']);
        $r->addRoute('GET', '/rapports/suivi', [EtudiantRapportController::class, 'showSuiviRapport']);
        $r->addRoute('GET', '/rapports/corrections', [EtudiantRapportController::class, 'showSoumettreCorrectionsForm']); // Si applicable
        $r->addRoute('POST', '/rapports/corrections', [EtudiantRapportController::class, 'handleSoumettreCorrections']); // Si applicable

        $r->addRoute('GET', '/reclamations/soumettre', [ReclamationEtudiantController::class, 'showSoumettreReclamationForm']);
        $r->addRoute('POST', '/reclamations/soumettre', [ReclamationEtudiantController::class, 'handleSoumettreReclamation']);
        $r->addRoute('GET', '/reclamations/suivi', [ReclamationEtudiantController::class, 'showSuiviReclamations']);

        $r->addRoute('GET', '/documents', [DocumentEtudiantController::class, 'listUserDocuments']);
        // $r->addRoute('GET', '/ressources', [RessourceEtudiantController::class, 'index']); // Exemple
    });

    // --- Routes spécifiques au Personnel Administratif ---
    $r->addGroup('/personnel-admin', function (RouteCollector $r) {
        $r->addRoute('GET', '/dashboard', [PersonnelDashboardController::class, 'index']);

        // Scolarité
        $r->addGroup('/scolarite', function (RouteCollector $r) {
            $r->addRoute('GET', '/etudiants', [ScolariteController::class, 'gestionEtudiants']);
            $r->addRoute('GET', '/inscriptions', [ScolariteController::class, 'gestionInscriptions']);
            $r->addRoute('GET', '/notes', [ScolariteController::class, 'gestionNotes']);
            $r->addRoute('GET', '/documents/generation', [ScolariteController::class, 'generationDocuments']);
        });

        // Conformité
        $r->addGroup('/conformite', function (RouteCollector $r) {
            $r->addRoute('GET', '/rapports/verifier', [ConformiteController::class, 'listRapportsAVerifier']);
            $r->addRoute('GET', '/rapports/traites', [ConformiteController::class, 'listRapportsTraites']);
            $r->addRoute('GET', '/rapports/{id_rapport:\d+}/details', [ConformiteController::class, 'detailsRapport']);
            $r->addRoute('POST', '/rapports/{id_rapport:\d+}/statut', [ConformiteController::class, 'updateStatutRapport']);
        });
        // Communication (si applicable pour Personnel Admin)
        // $r->addRoute('GET', '/communication', [CommunicationInterneController::class, 'index']);
    });

    // --- Routes spécifiques à la Commission ---
    $r->addGroup('/commission', function (RouteCollector $r) {
        $r->addRoute('GET', '/dashboard', [CommissionDashboardController::class, 'index']);
        $r->addRoute('GET', '/rapports/traiter', [ValidationRapportController::class, 'listRapportsATraiter']);
        $r->addRoute('GET', '/rapports/{id_rapport:\d+}/details', [ValidationRapportController::class, 'detailsRapport']);
        $r->addRoute('POST', '/rapports/{id_rapport:\d+}/voter', [ValidationRapportController::class, 'handleVoteRapport']);
        $r->addRoute('GET', '/rapports/{id_rapport:\d+}/vote', [ValidationRapportController::class, 'showInterfaceVote']); // Pour afficher le formulaire de vote

        $r->addRoute('GET', '/corrections', [CorrectionCommissionController::class, 'index']); // Lister les rapports nécessitant corrections/avis
        $r->addRoute('POST', '/corrections/{id_rapport_etudiant:\d+}/avis', [CorrectionCommissionController::class, 'enregistrerAvisOuCorrection']);


        $r->addRoute('GET', '/pv/rediger', [PvController::class, 'showRedigerPvForm']);
        $r->addRoute('POST', '/pv/rediger', [PvController::class, 'handleRedigerPv']);
        $r->addRoute('GET', '/pv/valider', [PvController::class, 'listPvAValider']); // Lister les PV à valider
        $r->addRoute('POST', '/pv/{id_pv:\d+}/valider', [PvController::class, 'handleValiderPv']);
        $r->addRoute('GET', '/pv/{id_pv:\d+}/consulter', [PvController::class, 'consulterPv']);
        $r->addRoute('GET', '/historique', [CommissionDashboardController::class, 'historiqueDecisions']); // Ou un contrôleur dédié
        // Communication (si applicable pour Commission)
        // $r->addRoute('GET', '/communication', [CommunicationCommissionController::class, 'index']);
    });


    // --- Routes spécifiques à l'Administration Système ---
    $r->addGroup('/admin', function (RouteCollector $r) {

        $r->addRoute('GET', '', [AdminDashboardController::class, 'index']); // Route pour /admin
        $r->addRoute('GET', '/dashboard', [AdminDashboardController::class, 'index']);

        // Gestion des utilisateurs
        $r->addGroup('/utilisateurs', function (RouteCollector $r) {
            $r->addRoute('GET', '/utilisateurs', [UtilisateurController::class, 'listerUtilisateurs']);
            $r->addRoute('GET', '', [UtilisateurController::class, 'listAll']); // Liste tous les types d'utilisateurs ou une page de gestion
            $r->addRoute('GET', '/etudiants', [UtilisateurController::class, 'listEtudiants']);
            $r->addRoute('GET', '/etudiants/ajouter', [UtilisateurController::class, 'showAddEtudiantForm']);
            $r->addRoute('POST', '/etudiants/ajouter', [UtilisateurController::class, 'handleAddEtudiant']);
            $r->addRoute('GET', '/etudiants/{num_etudiant}/modifier', [UtilisateurController::class, 'showEditEtudiantForm']);
            $r->addRoute('POST', '/etudiants/{num_etudiant}/modifier', [UtilisateurController::class, 'handleEditEtudiant']);

            $r->addRoute('GET', '/enseignants', [UtilisateurController::class, 'listEnseignants']);
            // ... autres CRUD pour enseignants, personnel ...
        });

        // Gestion des habilitations
        $r->addGroup('/habilitations', function (RouteCollector $r) {
            $r->addRoute('GET', '/groupes', [HabilitationController::class, 'listGroupes']);
            $r->addRoute('GET', '/groupes/creer', [HabilitationController::class, 'showFormGroupe']);
            $r->addRoute('POST', '/groupes/creer', [HabilitationController::class, 'handleFormGroupe']);
            $r->addRoute('GET', '/groupes/{id_groupe}/modifier', [HabilitationController::class, 'showFormGroupe']);
            $r->addRoute('POST', '/groupes/{id_groupe}/modifier', [HabilitationController::class, 'handleFormGroupe']);
            // ... types utilisateur, traitements, niveaux d'accès, rattachements ...
        });

        // Référentiels
        $r->addGroup('/referentiels', function (RouteCollector $r) {
            $r->addRoute('GET', '', [ReferentialController::class, 'listReferentiels']); // Page listant les types de référentiels
            $r->addRoute('GET', '/{type_referentiel}', [ReferentialController::class, 'listItemsReferentiel']);
            $r->addRoute('GET', '/{type_referentiel}/ajouter', [ReferentialController::class, 'showFormItemReferentiel']);
            $r->addRoute('POST', '/{type_referentiel}/ajouter', [ReferentialController::class, 'handleFormItemReferentiel']);
            // ... modifier, supprimer item référentiel ...
        });

        // Configuration Système
        $r->addGroup('/config', function (RouteCollector $r) {
            $r->addRoute('GET', '/general', [ConfigSystemeController::class, 'showParametresGeneraux']);
            $r->addRoute('POST', '/general', [ConfigSystemeController::class, 'handleParametresGeneraux']);
            $r->addRoute('GET', '/annee-academique', [ConfigSystemeController::class, 'gestionAnneeAcademique']);
            // ... modèles documents ...
        });

        // Gestion Académique (vue admin)
        $r->addGroup('/gestion-acad', function (RouteCollector $r) {
            $r->addRoute('GET', '/inscriptions', [GestionAcadController::class, 'listInscriptions']);
            // ... autres vues admin pour gestion académique ...
        });

        // Supervision
        $r->addGroup('/supervision', function (RouteCollector $r) {
            $r->addRoute('GET', '/audit-logs', [SupervisionController::class, 'showAuditLogs']);
            // ... maintenance, workflows ...
        });

        // Reporting
        $r->addRoute('GET', '/reporting', [ReportingController::class, 'showReportingDashboard']);

    });


    // Routes pour les assets (CSS, JS, Images) si gérées par PHP (non recommandé pour la performance)
    // Préférez la configuration du serveur web pour servir les fichiers statiques directement.
    // Exemple si vous devez le faire via PHP :
    // $r->addRoute('GET', '/assets/{filepath:.+}', [AssetController::class, 'serve']);

    // Route pour les notifications (exemple, pourrait être un endpoint API)
    $r->addRoute('GET', '/notifications/all', [NotificationController::class, 'getAllNotificationsForUser']);


    // Route de fallback pour les erreurs 404 (gérée par le dispatcher si aucune route ne correspond)
    // Le dispatcher dans index.php gère déjà NOT_FOUND.
};
