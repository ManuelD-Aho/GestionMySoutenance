<?php

use Backend\Controller\DashboardController;
use FastRoute\RouteCollector;
use Backend\Controller\AuthentificationController;
use Backend\Controller\BaseController;
use Backend\Controller\AssetController;

return function(RouteCollector $r) {
    $r->addRoute('GET', '/', [BaseController::class, 'home']);
    $r->addRoute('GET', '/assets/css/{filename:.+\.css}', [AssetController::class, 'serveCss']);
    $r->addRoute('GET', '/login', [AuthentificationController::class, 'showLoginForm']);
    $r->addRoute('POST', '/login', [AuthentificationController::class, 'login']);
    $r->addRoute('GET', '/logout', [AuthentificationController::class, 'logout']);
    $r->addRoute('GET', '/admin/users', ['Backend\Controller\Admin\UserController','index']);
    $r->addRoute('GET', '/dashboard', [DashboardController::class, 'index']);
};