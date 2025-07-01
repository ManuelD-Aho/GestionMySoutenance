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
 * Ce fichier ne fait que retourner une fonction qui définit les routes de l'application.
 * Le contrôleur frontal (index.php) se charge de l'appeler.
 */
return function(RouteCollector $r) {
    // --- Routes Publiques (Authentification & Assets) ---
    $r->addRoute('GET', '/', [HomeController::class, 'index']);
    $r->addRoute('GET', '/login', [AuthentificationController::class, 'showLoginForm']);
    $r->addRoute('POST', '/login', [AuthentificationController::class, 'handleLogin']);
    $r->addRoute('GET', '/logout', [AuthentificationController::class, 'logout']);
    $r->addRoute('GET', '/forgot-password', [AuthentificationController::class, 'showForgotPasswordForm']);
    $r->addRoute('POST', '/forgot-password', [AuthentificationController::class, 'handleForgotPassword']);
    $r->addRoute('GET', '/reset-password/{token}', [AuthentificationController::class, 'showResetPasswordForm']);
    $r->addRoute('POST', '/reset-password', [AuthentificationController::class, 'handleResetPassword']);
    $r->addRoute('GET', '/verify-2fa', [AuthentificationController::class, 'show2faForm']);
    $r->addRoute('POST', '/verify-2fa', [AuthentificationController::class, 'handle2faVerification']);
    $r->addRoute('GET', '/assets/{type}/{file}', [AssetController::class, 'serve']);

    // --- Route de Dashboard principal ---
    $r->addRoute('GET', '/dashboard', [DashboardController::class, 'index']);

    // --- Routes pour les Étudiants ---
    $r->addGroup('/etudiant', function (RouteCollector $r) {
        $r->addRoute('GET', '/dashboard', [EtudiantDashboardController::class, 'index']);
        $r->addRoute('GET', '/profil', [ProfilEtudiantController::class, 'show']);
        $r->addRoute('POST', '/profil', [ProfilEtudiantController::class, 'update']);
        $r->addRoute('GET', '/rapport', [RapportController::class, 'show']);
        $r->addRoute('POST', '/rapport/upload', [RapportController::class, 'upload']);
    });

    // --- Routes pour le Personnel Administratif ---
    $r->addGroup('/personnel', function (RouteCollector $r) {
        $r->addRoute('GET', '/dashboard', [PersonnelDashboardController::class, 'index']);
        $r->addRoute('GET', '/scolarite', [ScolariteController::class, 'index']);
        $r->addRoute('POST', '/scolarite/validate', [ScolariteController::class, 'validate']);
    });

    // --- Routes pour la Commission ---
    $r->addGroup('/commission', function (RouteCollector $r) {
        $r->addRoute('GET', '/dashboard', [CommissionDashboardController::class, 'index']);
        $r->addRoute('GET', '/workflow', [WorkflowCommissionController::class, 'index']);
        $r->addRoute('POST', '/workflow/update', [WorkflowCommissionController::class, 'update']);
    });

    // --- Routes pour l'Administration ---
    $r->addGroup('/admin', function (RouteCollector $r) {
        $r->addRoute('GET', '/dashboard', [AdminDashboardController::class, 'index']);
        $r->addRoute('GET', '/supervision', [SupervisionController::class, 'index']);

        // CRUD Utilisateurs
        $r->addRoute('GET', '/utilisateurs', [UtilisateurController::class, 'index']);
        $r->addRoute('GET', '/utilisateurs/new', [UtilisateurController::class, 'create']);
        $r->addRoute('POST', '/utilisateurs', [UtilisateurController::class, 'store']);
        $r->addRoute('GET', '/utilisateurs/{id:\d+}/edit', [UtilisateurController::class, 'edit']);
        $r->addRoute('POST', '/utilisateurs/{id:\d+}', [UtilisateurController::class, 'update']);
        $r->addRoute('POST', '/utilisateurs/{id:\d+}/delete', [UtilisateurController::class, 'delete']);

        // Configuration
        $r->addRoute('GET', '/configuration', [ConfigurationController::class, 'index']);
        $r->addRoute('POST', '/configuration', [ConfigurationController::class, 'save']);
    });
};

