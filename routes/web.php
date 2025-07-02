<?php
// routes/web.php

use App\Backend\Controller\Administration\AdminDashboardController;
use App\Backend\Controller\Administration\ConfigurationController;
use App\Backend\Controller\Administration\SupervisionController;
use App\Backend\Controller\Administration\UtilisateurController;
use App\Backend\Controller\AssetController;
use App\Backend\Controller\AuthentificationController;
use App\Backend\Controller\Commission\CommissionDashboardController;
use App\Backend\Controller\Commission\WorkflowCommissionController;
use App\Backend\Controller\DashboardController;
use App\Backend\Controller\Etudiant\EtudiantDashboardController;
use App\Backend\Controller\Etudiant\ProfilEtudiantController;
use App\Backend\Controller\Etudiant\RapportController;
use App\Backend\Controller\HomeController;
use App\Backend\Controller\PersonnelAdministratif\PersonnelDashboardController;
use App\Backend\Controller\PersonnelAdministratif\ScolariteController;
use FastRoute\RouteCollector;

/**
 * Définit toutes les routes de l'application.
 * Ce fichier retourne une closure qui sera utilisée par le Router pour construire la collection de routes.
 *
 * @param RouteCollector $router L'instance du collecteur de routes de FastRoute.
 */
return function(RouteCollector $router) {
    // --- Routes Publiques et Authentification ---
    $router->addRoute('GET', '/', [HomeController::class, 'index']);
    $router->addRoute('GET', '/login', [AuthentificationController::class, 'showLoginForm']);
    $router->addRoute('POST', '/login', [AuthentificationController::class, 'login']); // Renommée de handleLogin
    $router->addRoute('GET', '/logout', [AuthentificationController::class, 'logout']);
    $router->addRoute('GET', '/forgot-password', [AuthentificationController::class, 'showForgotPasswordForm']);
    $router->addRoute('POST', '/forgot-password', [AuthentificationController::class, 'handleForgotPassword']);
    $router->addRoute('GET', '/reset-password/{token}', [AuthentificationController::class, 'showResetPasswordForm']);
    $router->addRoute('POST', '/reset-password/{token}', [AuthentificationController::class, 'handleResetPassword']);
    $router->addRoute('GET', '/validate-email/{token}', [AuthentificationController::class, 'validateEmail']);
    $router->addRoute('GET', '/2fa', [AuthentificationController::class, 'show2faForm']);
    $router->addRoute('POST', '/2fa', [AuthentificationController::class, 'handle2faVerification']);


    // --- Routes Protégées (nécessitent une connexion) ---

    // Dashboard principal (point d'entrée après connexion)
    $router->addRoute('GET', '/dashboard', [DashboardController::class, 'index']);

    // --- Section Administration ---
    $router->addGroup('/admin', function (RouteCollector $r) {
        $r->addRoute('GET', '/dashboard', [AdminDashboardController::class, 'index']);

        // Gestion des utilisateurs
        $r->addRoute('GET', '/utilisateurs', [UtilisateurController::class, 'list']); // Renommée de listUsers
        $r->addRoute('GET', '/utilisateurs/creer', [UtilisateurController::class, 'showCreateUserForm']); // Formulaire de création
        $r->addRoute('POST', '/utilisateurs/creer', [UtilisateurController::class, 'create']); // Renommée de handleCreateUser
        $r->addRoute('GET', '/utilisateurs/{id}', [UtilisateurController::class, 'show']); // Renommée de showEditUserForm
        $r->addRoute('POST', '/utilisateurs/{id}/modifier', [UtilisateurController::class, 'update']); // Renommée de handleEditUser
        $r->addRoute('POST', '/utilisateurs/{id}/supprimer', [UtilisateurController::class, 'delete']); // Renommée de handleUserAction (delete)

        // Configuration
        $r->addRoute('GET', '/configuration', [ConfigurationController::class, 'index']); // Renommée de showConfigurationPage
        $r->addRoute('POST', '/configuration/parametres', [ConfigurationController::class, 'handleSystemParameters']);
        $r->addRoute('POST', '/configuration/annees', [ConfigurationController::class, 'handleAcademicYearAction']);
        $r->addRoute('POST', '/configuration/referentiels', [ConfigurationController::class, 'handleReferentialAction']);
        $r->addRoute('GET', '/configuration/referentiels/{entityName}', [ConfigurationController::class, 'getReferentialDetails']); // AJAX
        $r->addRoute('POST', '/configuration/documents', [ConfigurationController::class, 'handleDocumentModelAction']);
        $r->addRoute('POST', '/configuration/notifications', [ConfigurationController::class, 'handleNotificationAction']);
        $r->addRoute('POST', '/configuration/menus', [ConfigurationController::class, 'handleMenuOrder']);
        $r->addRoute('POST', '/configuration/cache/clear', [ConfigurationController::class, 'clearCache']);

        // Supervision
        $r->addRoute('GET', '/supervision', [SupervisionController::class, 'index']); // Renommée de showAuditLogs
        $r->addRoute('GET', '/supervision/logs/{id}', [SupervisionController::class, 'getAuditLogDetails']); // AJAX
        $r->addRoute('POST', '/supervision/logs/purge', [SupervisionController::class, 'purgeAuditLogs']);
        $r->addRoute('POST', '/supervision/tasks/{idTache}', [SupervisionController::class, 'handleTaskAction']);
    });

    // --- Section Étudiant ---
    $router->addGroup('/etudiant', function (RouteCollector $r) {
        $r->addRoute('GET', '/dashboard', [EtudiantDashboardController::class, 'index']);
        $r->addRoute('GET', '/profil', [ProfilEtudiantController::class, 'show']); // Renommée de showProfile
        $r->addRoute('POST', '/profil', [ProfilEtudiantController::class, 'update']); // Renommée de updateProfile
        $r->addRoute('POST', '/profil/photo', [ProfilEtudiantController::class, 'handlePhotoUpload']);

        // Rapports
        $r->addRoute('GET', '/rapport/redaction', [RapportController::class, 'edit']); // Renommée de showChoiceOrRedirect
        $r->addRoute('POST', '/rapport/creer-depuis-modele', [RapportController::class, 'create']); // Renommée de handleCreateFromTemplate
        $r->addRoute('GET', '/rapport/redaction/{idRapport}', [RapportController::class, 'show']); // Renommée de showRapportForm
        $r->addRoute('POST', '/rapport/sauvegarder/{idRapport}', [RapportController::class, 'save']); // Renommée de saveRapport
        $r->addRoute('POST', '/rapport/soumettre/{idRapport}', [RapportController::class, 'submit']); // Renommée de submitRapport
        $r->addRoute('POST', '/rapport/soumettre-corrections/{idRapport}', [RapportController::class, 'submitCorrections']);
    });

    // --- Section Commission ---
    $router->addGroup('/commission', function (RouteCollector $r) {
        $r->addRoute('GET', '/dashboard', [CommissionDashboardController::class, 'index']);

        // Workflow de session
        $r->addRoute('GET', '/workflow', [WorkflowCommissionController::class, 'index']); // Renommée de listSessions
        $r->addRoute('POST', '/workflow/sessions/creer', [WorkflowCommissionController::class, 'create']); // Renommée de createSession
        $r->addRoute('POST', '/workflow/rapports/{idRapport}/voter', [WorkflowCommissionController::class, 'vote']); // Renommée de submitVote
        $r->addRoute('POST', '/workflow/sessions/{idSession}/initier-pv', [WorkflowCommissionController::class, 'initierPv']);
        $r->addRoute('POST', '/workflow/pv/{idCompteRendu}/approuver', [WorkflowCommissionController::class, 'approuverPv']);
        $r->addRoute('POST', '/workflow/pv/{idCompteRendu}/forcer-validation', [WorkflowCommissionController::class, 'forcerValidationPv']);
    });

    // --- Section Personnel Administratif ---
    $router->addGroup('/personnel', function (RouteCollector $r) {
        $r->addRoute('GET', '/dashboard', [PersonnelDashboardController::class, 'index']);

        // Conformité
        $r->addRoute('GET', '/conformite/queue', [ScolariteController::class, 'conformiteQueue']); // Renommée de listConformiteQueue
        $r->addRoute('GET', '/conformite/verifier/{idRapport}', [ScolariteController::class, 'showConformite']); // Renommée de showConformiteForm
        $r->addRoute('POST', '/conformite/process/{idRapport}', [ScolariteController::class, 'processConformite']);

        // Scolarité
        $r->addRoute('GET', '/scolarite/etudiants', [ScolariteController::class, 'index']); // Renommée de listStudentRecords
        $r->addRoute('GET', '/scolarite/etudiants/{idEtudiant}', [ScolariteController::class, 'showStudent']); // Renommée de showStudentDetails
        $r->addRoute('POST', '/scolarite/etudiants/activer-compte', [ScolariteController::class, 'activateAccount']);
        $r->addRoute('POST', '/scolarite/etudiants/inscription', [ScolariteController::class, 'handleInscriptionUpdate']);
        $r->addRoute('POST', '/scolarite/etudiants/note', [ScolariteController::class, 'handleNoteEntry']);
        $r->addRoute('POST', '/scolarite/etudiants/{numeroEtudiant}/stage/{idEntreprise}/valider', [ScolariteController::class, 'validerStage']);
        $r->addRoute('POST', '/scolarite/penalites/{idPenalite}/regulariser', [ScolariteController::class, 'regulariserPenalite']);
        $r->addRoute('POST', '/scolarite/reclamations/{idReclamation}/repondre', [ScolariteController::class, 'handleReponseReclamation']);
        $r->addRoute('POST', '/scolarite/reclamations/{idReclamation}/cloturer', [ScolariteController::class, 'cloturerReclamation']);
        $r->addRoute('GET', '/scolarite/etudiants/export/{format}', [ScolariteController::class, 'exportStudents']);
    });

    // --- Route pour servir les assets (CSS, JS, images) ---
    // Le pattern {filePath:.+} permet de capturer les chemins avec des sous-dossiers.
    $router->addRoute('GET', '/assets/{filePath:.+}', [AssetController::class, 'serve']);
};