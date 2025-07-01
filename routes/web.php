<?php
// routes/web.php

// Import des classes de contrôleurs
use App\Backend\Controller\{
    HomeController, AuthentificationController, DashboardController, AssetController
};
use App\Backend\Controller\Administration\{
    AdminDashboardController, ConfigurationController, SupervisionController, UtilisateurController
};
use App\Backend\Controller\Commission\{
    CommissionDashboardController, WorkflowCommissionController
};
use App\Backend\Controller\Etudiant\{
    EtudiantDashboardController, ProfilEtudiantController, RapportController
};
use App\Backend\Controller\PersonnelAdministratif\{
    PersonnelDashboardController, ScolariteController
};

// Import de la classe Router
use App\Config\Router;
use App\Config\Container; // Nécessaire pour les gestionnaires d'erreur 404/405
use App\Backend\Controller\BaseController; // Nécessaire pour les gestionnaires d'erreur 404/405

// --- Définition des Middlewares ---
// Ces fonctions seront appelées par le routeur avant d'exécuter le contrôleur.
// Elles doivent être définies ici ou dans un fichier séparé et inclus.

// Middleware d'authentification
Router::middleware('auth', function() {
    // Supposons que le conteneur est accessible via une variable globale ou un singleton
    // Pour une meilleure pratique, le middleware devrait être une classe injectable
    // Pour cet exemple, nous allons le faire simple.
    global $container; // Accès au conteneur globalement défini dans index.php
    $securiteService = $container->get(App\Backend\Service\Securite\ServiceSecuriteInterface::class);
    if (!$securiteService->estUtilisateurConnecte()) {
        $container->get(BaseController::class)->addFlashMessage('warning', 'Veuillez vous connecter pour accéder à cette page.');
        $container->get(BaseController::class)->redirect('/login');
    }
});

// Middleware pour le rôle Administrateur Système
Router::middleware('admin', function() {
    global $container;
    $securiteService = $container->get(App\Backend\Service\Securite\ServiceSecuriteInterface::class);
    if (!$securiteService->utilisateurPossedePermission('TRAIT_ADMIN_DASHBOARD_ACCEDER')) { // Exemple de permission admin
        $container->get(BaseController::class)->renderError(403, 'Accès refusé. Vous n\'avez pas les droits d\'administrateur.');
    }
});

// Middleware pour le rôle Étudiant
Router::middleware('etudiant', function() {
    global $container;
    $securiteService = $container->get(App\Backend\Service\Securite\ServiceSecuriteInterface::class);
    $user = $securiteService->getUtilisateurConnecte();
    if (!$user || $user['id_groupe_utilisateur'] !== 'GRP_ETUDIANT') {
        $container->get(BaseController::class)->renderError(403, 'Accès refusé. Cette section est réservée aux étudiants.');
    }
});

// Middleware pour le rôle Personnel Administratif
Router::middleware('personnel', function() {
    global $container;
    $securiteService = $container->get(App\Backend\Service\Securite\ServiceSecuriteInterface::class);
    $user = $securiteService->getUtilisateurConnecte();
    if (!$user || !in_array($user['id_groupe_utilisateur'], ['GRP_PERS_ADMIN', 'GRP_RS', 'GRP_AGENT_CONFORMITE'])) {
        $container->get(BaseController::class)->renderError(403, 'Accès refusé. Cette section est réservée au personnel administratif.');
    }
});

// Middleware pour le rôle Commission
Router::middleware('commission', function() {
    global $container;
    $securiteService = $container->get(App\Backend\Service\Securite\ServiceSecuriteInterface::class);
    $user = $securiteService->getUtilisateurConnecte();
    if (!$user || $user['id_groupe_utilisateur'] !== 'GRP_COMMISSION') {
        $container->get(BaseController::class)->renderError(403, 'Accès refusé. Cette section est réservée aux membres de la commission.');
    }
});


// --- Définition des Routes ---

// 1. Routes Publiques
Router::get('/', [HomeController::class, 'index']);
Router::get('/about', [HomeController::class, 'about']);

// 2. Routes d'Authentification
Router::get('/login', [AuthentificationController::class, 'showLoginForm']);
Router::post('/login', [AuthentificationController::class, 'handleLogin']);
Router::post('/logout', [AuthentificationController::class, 'logout']);
Router::get('/forgot-password', [AuthentificationController::class, 'showForgotPasswordForm']);
Router::post('/forgot-password', [AuthentificationController::class, 'handleForgotPassword']);
Router::get('/reset-password/{token}', [AuthentificationController::class, 'showResetPasswordForm']);
Router::post('/reset-password', [AuthentificationController::class, 'handleResetPassword']);
Router::get('/validate-email/{token}', [AuthentificationController::class, 'validateEmail']);
Router::get('/2fa', [AuthentificationController::class, 'show2faForm']);
Router::post('/2fa', [AuthentificationController::class, 'handle2faVerification']);

// 3. Routes pour les Assets Protégés
Router::get('/assets/protected/{type}/{filename}', [AssetController::class, 'serveProtectedFile']);

// 4. Routes Protégées (nécessitent une authentification)
Router::group(['middleware' => 'auth'], function() {

    Router::get('/dashboard', [DashboardController::class, 'index']);

    // 4.1. Routes du Module Étudiant
    Router::group(['middleware' => 'etudiant'], function() {
        Router::get('/etudiant/dashboard', [EtudiantDashboardController::class, 'index']);
        Router::get('/etudiant/profil', [ProfilEtudiantController::class, 'showProfile']);
        Router::post('/etudiant/profil/update', [ProfilEtudiantController::class, 'updateProfile']);
        Router::post('/etudiant/profil/password', [ProfilEtudiantController::class, 'updatePassword']);
        Router::get('/etudiant/profil/2fa/setup', [ProfilEtudiantController::class, 'setup2FA']);
        Router::post('/etudiant/profil/2fa/enable', [ProfilEtudiantController::class, 'enable2FA']);
        Router::post('/etudiant/profil/2fa/disable', [ProfilEtudiantController::class, 'disable2FA']);

        // Rapports de Soutenance
        Router::get('/etudiant/rapport/redaction', [RapportController::class, 'showRapportForm']);
        Router::post('/etudiant/rapport/save-draft', [RapportController::class, 'saveRapport']);
        Router::post('/etudiant/rapport/upload-image', [RapportController::class, 'uploadImageRapport']);
        Router::post('/etudiant/rapport/submit', [RapportController::class, 'submitRapport']);
        Router::post('/etudiant/rapport/submit-corrections', [RapportController::class, 'submitCorrections']);
        Router::get('/etudiant/rapport/suivi', [RapportController::class, 'showRapportStatus']);

        // Réclamations
        Router::get('/etudiant/reclamations', [ReclamationController::class, 'listReclamations']);
        Router::get('/etudiant/reclamations/create', [ReclamationController::class, 'showReclamationForm']);
        Router::post('/etudiant/reclamations/create', [ReclamationController::class, 'createReclamation']);
        Router::get('/etudiant/reclamations/{id}', [ReclamationController::class, 'showReclamationDetails']);
    });

    // 4.2. Routes du Module Personnel Administratif
    Router::group(['middleware' => 'personnel'], function() {
        Router::get('/personnel/dashboard', [PersonnelDashboardController::class, 'index']);

        // Gestion de la Conformité
        Router::get('/personnel/conformite/queue', [ScolariteController::class, 'listConformiteQueue']);
        Router::get('/personnel/conformite/rapport/{id}', [ScolariteController::class, 'showConformiteForm']);
        Router::post('/personnel/conformite/rapport/{id}/process', [ScolariteController::class, 'processConformite']);

        // Gestion Scolarité
        Router::get('/personnel/scolarite/etudiants', [ScolariteController::class, 'listStudentRecords']);
        Router::get('/personnel/scolarite/etudiants/export', [ScolariteController::class, 'exportStudentRecords']);
        Router::get('/personnel/scolarite/etudiant/{id}', [ScolariteController::class, 'showStudentRecord']);
        Router::post('/personnel/scolarite/etudiant/{id}/save-info', [ScolariteController::class, 'saveStudentInfo']);
        Router::post('/personnel/scolarite/etudiant/{id}/save-notes', [ScolariteController::class, 'saveNotes']);
        Router::post('/personnel/scolarite/etudiant/{id}/save-stage', [ScolariteController::class, 'saveStage']);
        Router::post('/personnel/scolarite/etudiant/{id}/create-penalite', [ScolariteController::class, 'createPenalite']);
        Router::post('/personnel/scolarite/etudiant/{id}/activate-account', [ScolariteController::class, 'activateAccount']);
        Router::post('/personnel/scolarite/inscription/{id}/send-payment-reminder', [ScolariteController::class, 'sendPaymentReminder']);
        Router::post('/personnel/scolarite/penalite/{id}/regularize', [ScolariteController::class, 'regularizePenalite']);

        // Réclamations
        Router::get('/personnel/reclamations', [ReclamationController::class, 'listReclamations']);
        Router::get('/personnel/reclamations/{id}', [ReclamationController::class, 'showReclamationDetails']);
        Router::post('/personnel/reclamations/{id}/answer', [ReclamationController::class, 'answerReclamation']);
    });

    // 4.3. Routes du Module Commission
    Router::group(['middleware' => 'commission'], function() {
        Router::get('/commission/dashboard', [CommissionDashboardController::class, 'index']);

        // Gestion des Sessions de Validation
        Router::get('/commission/sessions', [WorkflowCommissionController::class, 'listSessions']);
        Router::get('/commission/sessions/create', [WorkflowCommissionController::class, 'showSessionForm']);
        Router::post('/commission/sessions/create', [WorkflowCommissionController::class, 'saveSession']);
        Router::get('/commission/sessions/{id}', [WorkflowCommissionController::class, 'showSessionDetails']);
        Router::post('/commission/sessions/{id}/update', [WorkflowCommissionController::class, 'saveSession']);
        Router::post('/commission/sessions/{id}/compose', [WorkflowCommissionController::class, 'composeSession']);
        Router::post('/commission/sessions/{id}/start', [WorkflowCommissionController::class, 'demarrerSession']);
        Router::post('/commission/sessions/{id}/suspend', [WorkflowCommissionController::class, 'suspendreSession']);
        Router::post('/commission/sessions/{id}/resume', [WorkflowCommissionController::class, 'reprendreSession']);
        Router::post('/commission/sessions/{id}/close', [WorkflowCommissionController::class, 'cloturerSession']);

        // Évaluation et Vote
        Router::post('/commission/rapport/{idRapport}/vote', [WorkflowCommissionController::class, 'submitVote']);
        Router::post('/commission/rapport/{idRapport}/new-vote-round', [WorkflowCommissionController::class, 'lancerNouveauTourDeVote']);
        Router::get('/commission/sessions/{idSession}/votes-status', [WorkflowCommissionController::class, 'consulterEtatVotes']);

        // Gestion des Procès-Verbaux (PV)
        Router::get('/commission/pv/{idSession}/init', [WorkflowCommissionController::class, 'initierRedactionPv']);
        Router::get('/commission/pv/{idPv}/edit', [WorkflowCommissionController::class, 'showPvForm']);
        Router::post('/commission/pv/{idPv}/save', [WorkflowCommissionController::class, 'savePv']);
        Router::post('/commission/pv/{idPv}/submit-for-approval', [WorkflowCommissionController::class, 'soumettrePvPourApprobation']);
        Router::post('/commission/pv/{idPv}/approve', [WorkflowCommissionController::class, 'approvePv']);
        Router::post('/commission/pv/{idPv}/force-approve', [WorkflowCommissionController::class, 'forcerValidationPv']);
        Router::post('/commission/pv/{idPv}/reassign-redactor', [WorkflowCommissionController::class, 'reassignerRedactionPv']);

        // Finalisation Post-Validation
        Router::post('/commission/rapport/{idRapport}/designate-director', [WorkflowCommissionController::class, 'designerDirecteurMemoire']);
        Router::post('/commission/rapport/{idRapport}/designate-rapporteur', [WorkflowCommissionController::class, 'designerRapporteur']);
        Router::post('/commission/sessions/{idSession}/recuse-member', [WorkflowCommissionController::class, 'recuserMembre']);
    });

    // 4.4. Routes du Module Administrateur Système
    Router::group(['middleware' => 'admin'], function() {
        Router::get('/admin/dashboard', [AdminDashboardController::class, 'index']);

        // Gestion des Utilisateurs
        Router::get('/admin/utilisateurs/liste', [UtilisateurController::class, 'listUsers']);
        Router::get('/admin/utilisateurs/creer', [UtilisateurController::class, 'showUserForm']);
        Router::post('/admin/utilisateurs/save', [UtilisateurController::class, 'saveUser']);
        Router::post('/admin/utilisateurs/delete/{id}', [UtilisateurController::class, 'deleteUser']);
        Router::post('/admin/utilisateurs/bulk-actions', [UtilisateurController::class, 'handleBulkActions']);
        Router::post('/admin/utilisateurs/reset-password/{id}', [UtilisateurController::class, 'resetPassword']);
        Router::post('/admin/utilisateurs/impersonate/{id}', [UtilisateurController::class, 'impersonate']);
        Router::get('/admin/utilisateurs/stop-impersonating', [UtilisateurController::class, 'stopImpersonating']);
        Router::get('/admin/utilisateurs/import', [UtilisateurController::class, 'showImportForm']);
        Router::post('/admin/utilisateurs/import', [UtilisateurController::class, 'handleImport']);

        // Configuration Système
        Router::get('/admin/configuration', [ConfigurationController::class, 'showConfigurationPage']);
        Router::post('/admin/configuration/system-parameters', [ConfigurationController::class, 'handleSystemParameters']);
        Router::post('/admin/configuration/academic-years/add', [ConfigurationController::class, 'addAcademicYear']);
        Router::post('/admin/configuration/academic-years/update/{id}', [ConfigurationController::class, 'updateAcademicYear']);
        Router::post('/admin/configuration/academic-years/delete/{id}', [ConfigurationController::class, 'deleteAcademicYear']);
        Router::post('/admin/configuration/academic-years/set-active/{id}', [ConfigurationController::class, 'setActiveAcademicYear']);
        Router::post('/admin/configuration/referentials/{entityName}/{id?}', [ConfigurationController::class, 'handleReferential']);
        Router::post('/admin/configuration/document-models/{id?}', [ConfigurationController::class, 'handleDocumentModel']);
        Router::post('/admin/configuration/notification-settings', [ConfigurationController::class, 'handleNotificationSettings']);
        Router::post('/admin/configuration/menu-order', [ConfigurationController::class, 'handleMenuOrder']);

        // Supervision et Audit
        Router::get('/admin/supervision/audit-logs', [SupervisionController::class, 'showAuditLogs']);
        Router::get('/admin/supervision/error-logs', [SupervisionController::class, 'showErrorLogs']);
        Router::get('/admin/supervision/queue-status', [SupervisionController::class, 'showQueueStatus']);
        Router::post('/admin/supervision/queue-task/{id}/{action}', [SupervisionController::class, 'manageQueueTask']);
    });
});

// --- Gestion des Erreurs de Route ---
// Ces gestionnaires utilisent le conteneur pour instancier BaseController et rendre les pages d'erreur.
Router::notFound(function() {
    global $container; // Accès au conteneur globalement défini dans index.php
    $container->get(BaseController::class)->renderError(404, 'La page que vous recherchez n\'existe pas.');
});

Router::methodNotAllowed(function() {
    global $container;
    $container->get(BaseController::class)->renderError(405, 'La méthode HTTP utilisée n\'est pas autorisée pour cette ressource.');
});