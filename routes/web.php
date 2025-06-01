<?php

use FastRoute\RouteCollector;
use App\Backend\Controller\BaseController;
use App\Backend\Controller\AssetController;
use App\Backend\Controller\AuthentificationController; // Assurez-vous que le nom est correct
use App\Backend\Controller\DashboardController;
// Ajoutez ici les use pour les autres contrôleurs si nécessaire

return function(RouteCollector $r) {
    // Routes Publiques
    $r->addRoute('GET', '/', [BaseController::class, 'home']);
    //$r->addRoute('GET', '/assets/css/{filename:.+\.css}', [AssetController::class, 'serveCss']);
    // Ajoutez d'autres routes pour assets si nécessaire (JS, images)
    // $r->addRoute('GET', '/assets/js/{filename:.+\.js}', [AssetController::class, 'serveJs']);
    // $r->addRoute('GET', '/assets/img/[{subdir:.+}/]{filename:.+\.(png|jpg|jpeg|gif|svg)}', [AssetController::class, 'serveImage']);


    // --- Authentification ---
    $r->addRoute('GET', '/login', [AuthentificationController::class, 'showLoginForm']);
    $r->addRoute('POST', '/handle-login', [AuthentificationController::class, 'handleLogin']); // Ou 'login' si c'est le nom de votre méthode
    $r->addRoute('GET', '/logout', [AuthentificationController::class, 'logout']);

    // Validation d'Email
    $r->addRoute('GET', '/validate-email', [AuthentificationController::class, 'handleValidateEmailToken']); // Gère le token depuis $_GET['token']

    // Mot de Passe Oublié
    $r->addRoute('GET', '/forgot-password', [AuthentificationController::class, 'showForgotPasswordForm']);
    $r->addRoute('POST', '/handle-forgot-password', [AuthentificationController::class, 'handleForgotPasswordRequest']);
    $r->addRoute('GET', '/reset-password', [AuthentificationController::class, 'showResetPasswordForm']); // Attend un token en query param
    $r->addRoute('POST', '/handle-reset-password', [AuthentificationController::class, 'handleResetPasswordSubmission']);

    // Authentification à Deux Facteurs (2FA)
    $r->addRoute('GET', '/login-2fa', [AuthentificationController::class, 'show2FAForm']);
    $r->addRoute('POST', '/handle-2fa', [AuthentificationController::class, 'handle2FASubmission']);

    // --- Routes Protégées (Nécessitent Connexion) ---
    // La protection doit être gérée dans les contrôleurs ou un mécanisme de middleware simulé dans index.php

    $r->addRoute('GET', '/dashboard', [DashboardController::class, 'index']);

    // Gestion de Profil (Utilisateur Connecté)
    $r->addGroup('/profile', function (RouteCollector $r) {
        // $r->addRoute('GET', '', [UserProfileController::class, 'showProfile']); // Exemple
        // $r->addRoute('POST', '/update', [UserProfileController::class, 'updateProfile']); // Exemple
        $r->addRoute('GET', '/change-password', [AuthentificationController::class, 'showChangePasswordForm']);
        $r->addRoute('POST', '/handle-change-password', [AuthentificationController::class, 'handleChangePassword']);
        $r->addRoute('GET', '/setup-2fa', [AuthentificationController::class, 'showSetup2FAForm']);
        $r->addRoute('POST', '/activate-2fa', [AuthentificationController::class, 'handleActivate2FA']);
        $r->addRoute('POST', '/disable-2fa', [AuthentificationController::class, 'handleDisable2FA']);
    });

    // Section Administration (Exemple)
    $r->addGroup('/admin', function (RouteCollector $r) {
        // $r->addRoute('GET', '', [AdminDashboardController::class, 'index']); // Exemple
        $r->addRoute('GET', '/users', ['App\Backend\Controller\Administration\UtilisateurController', 'listeTous']); // Assurez-vous du namespace et méthode
        // ... autres routes admin ...

        // Exemple de route pour la gestion des permissions (CRUD traitements)
        // $r->addRoute('GET', '/permissions/traitements', [HabilitationController::class, 'listerTraitements']);
        // $r->addRoute('GET', '/permissions/traitement/creer', [HabilitationController::class, 'showFormCreerTraitement']);
        // $r->addRoute('POST', '/permissions/traitement/creer', [HabilitationController::class, 'handleCreerTraitement']);
    });

    // Ajoutez ici d'autres groupes de routes pour les modules Etudiant, Commission, PersonnelAdministratif

};