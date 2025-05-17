<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use FastRoute\RouteCollector;
use FastRoute\Dispatcher as FastRouteDispatcher;

define('ROOT_PATH', dirname(__DIR__));
if (!file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    http_response_code(500);
    echo "<h1>Erreur Critique</h1>";
    echo "<p>Le fichier d'autoloading des dépendances (vendor/autoload.php) est introuvable.</p>";
    echo "<p>Veuillez exécuter 'composer install' à la racine de votre projet et reconstruire l'image Docker.</p>";
    exit;
}
require_once ROOT_PATH . '/vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (file_exists(ROOT_PATH . '/.env')) {
    $dotenv = Dotenv::createImmutable(ROOT_PATH);
    $dotenv->load();
}

$appEnv = $_ENV['APP_ENV'] ?? 'production';

if ($appEnv === 'development') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
}

$dispatcher = FastRoute\simpleDispatcher(function(RouteCollector $r) {
    $routesFilePath = ROOT_PATH . '/routes/web.php';
    if (!file_exists($routesFilePath)) {
        http_response_code(500);
        echo "<h1>Erreur Critique</h1>";
        echo "<p>Le fichier de définition des routes (routes/web.php) est introuvable.</p>";
        exit;
    }
    $routeDefinitionCallback = require $routesFilePath;
    if (!is_callable($routeDefinitionCallback)) {
        http_response_code(500);
        echo "<h1>Erreur Critique</h1>";
        echo "<p>Le fichier de définition des routes (routes/web.php) doit retourner une fonction (Closure).</p>";
        exit;
    }
    $routeDefinitionCallback($r);
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRouteDispatcher::NOT_FOUND:
        http_response_code(404);
        $notFoundViewPath = ROOT_PATH . '/src/Frontend/views/errors/404.php';
        if (file_exists($notFoundViewPath)) {
            include $notFoundViewPath;
        } else {
            echo '<h1>404 - Page non trouvée</h1>';
        }
        break;

    case FastRouteDispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        http_response_code(405);
        header('Allow: ' . implode(', ', $allowedMethods));
        $methodNotAllowedViewPath = ROOT_PATH . '/src/Frontend/views/errors/405.php';
        if (file_exists($methodNotAllowedViewPath)) {
            include $methodNotAllowedViewPath;
        } else {
            echo '<h1>405 - Méthode non autorisée</h1>';
            echo '<p>Les méthodes autorisées pour cette ressource sont : ' . implode(', ', $allowedMethods) . '</p>';
        }
        break;

    case FastRouteDispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        if (is_array($handler) && count($handler) === 2 && is_string($handler[0]) && is_string($handler[1])) {
            $controllerClass = $handler[0];
            $methodName = $handler[1];

            if (class_exists($controllerClass)) {
                $controllerInstance = new $controllerClass();
                if (method_exists($controllerInstance, $methodName)) {
                    call_user_func_array([$controllerInstance, $methodName], $vars);
                } else {
                    http_response_code(500);
                    echo "<h1>Erreur Serveur</h1><p>La méthode '$methodName' n'existe pas dans le contrôleur '$controllerClass'.</p>";
                }
            } else {
                http_response_code(500);
                echo "<h1>Erreur Serveur</h1><p>Le contrôleur '$controllerClass' est introuvable.</p>";
            }
        } elseif (is_callable($handler)) {
            call_user_func_array($handler, $vars);
        } else {
            http_response_code(500);
            echo '<h1>Erreur Serveur</h1><p>Le gestionnaire de route est mal configuré.</p>';
        }
        break;
    default:
        http_response_code(500);
        echo '<h1>500 - Erreur interne du serveur</h1><p>Une erreur inattendue est survenue avec le routeur.</p>';
        break;
}

if (isset($_SESSION['error_message'])) {
    unset($_SESSION['error_message']);
}
if (isset($_SESSION['success_message'])) {
    unset($_SESSION['success_message']);
}
