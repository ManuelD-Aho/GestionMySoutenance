<?php
declare(strict_types=1);

// 1. Chargement de l’autoload Composer
require __DIR__ . '/vendor/autoload.php';

use FastRoute\RouteCollector;
use FastRoute\Dispatcher;
use function FastRoute\simpleDispatcher;

// 2. Construction du routeur : on inclut un fichier de routes
$dispatcher = simpleDispatcher(function(RouteCollector $r) {
    require __DIR__ . '/routes.php';
});

// 3. Récupération de la méthode HTTP et de l’URI
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// 4. Dispatch et gestion des cas
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo "404 – Page introuvable";
        exit;
    case Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        header('Allow: ' . implode(', ', $routeInfo[1]));
        echo "405 – Méthode non autorisée";
        exit;
    case Dispatcher::FOUND:
        // Le handler doit être [NomDuController::class, 'méthode']
        [$controllerClass, $method] = $routeInfo[1];
        $vars = $routeInfo[2]; // paramètres extraits de l’URL

        // Instanciation et appel
        $controller = new $controllerClass();
        call_user_func_array([$controller, $method], $vars);
        break;
}


