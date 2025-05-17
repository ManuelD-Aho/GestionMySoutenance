<?php

use FastRoute\RouteCollector;
use Backend\Controller\AuthentificationController;
use Backend\Controller\BaseController;

return function(RouteCollector $r) {
    $r->addRoute('GET', '/', [BaseController::class, 'home']);

    $r->addRoute('GET', '/login', [AuthentificationController::class, 'showLoginForm']);
    $r->addRoute('POST', '/login', [AuthentificationController::class, 'login']);
    $r->addRoute('GET', '/logout', [AuthentificationController::class, 'logout']);

    // Tu ajouteras d'autres routes ici, par exemple pour le dashboard:
    // $r->addRoute('GET', '/dashboard', [DashboardController::class, 'index']);
    // N'oublie pas de créer DashboardController si tu utilises cette route.
};