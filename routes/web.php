<?php

use FastRoute\RouteCollector;
use Backend\Controller\authentification;
use Backend\Controller\BaseController; // Un contrôleur d'exemple

return function(RouteCollector $r) {
    // Route pour la page d'accueil
    $r->addRoute('GET', '/', [BaseController::class, 'home']);

    // Routes pour l'authentification
    $r->addRoute('GET', '/login', [authentification::class, 'showLoginForm']);
    $r->addRoute('POST', '/login', [authentification::class, 'login']);
    $r->addRoute('GET', '/logout', [authentification::class, 'logout']);

};