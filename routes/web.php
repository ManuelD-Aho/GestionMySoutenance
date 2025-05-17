<?php
use FastRoute\RouteCollector;

/** @var RouteCollector $r */

// … tes autres routes …

// Afficher le formulaire de login (GET /login)
$r->addRoute('GET', '/login', [
    App\Backend\Controller\Authentification::class,
    'showForm'
]);

// Traiter la soumission du formulaire (POST /login)
$r->addRoute('POST', '/login', [
    App\Backend\Controller\Authentification::class,
    'authenticate'
]);
