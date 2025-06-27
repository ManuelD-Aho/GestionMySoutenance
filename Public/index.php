<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

if (!file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    http_response_code(503);
    echo "<h1>Erreur Critique d'Initialisation</h1><p>Les dépendances sont introuvables. Veuillez exécuter 'composer install'.</p>";
    exit;
}
require_once ROOT_PATH . '/vendor/autoload.php';

try {
    // Détecter l'environnement
    $appEnv = getenv('APP_ENV') ?: 'development'; // Par défaut 'development' si non défini

    // Charger le fichier .env spécifique à l'environnement
    $envFile = ROOT_PATH . '/.env.' . $appEnv;
    if (file_exists($envFile)) {
        $dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH, '.env.' . $appEnv);
        $dotenv->load();
    } else {
        // Fallback pour le fichier .env général si les spécifiques n'existent pas
        // Utile pour les setups locaux simples ou si .env est géré différemment
        if (file_exists(ROOT_PATH . '/.env')) {
            $dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
            $dotenv->load();
        }
    }
} catch (\Throwable $e) {
    http_response_code(503);
    echo "<h1>Erreur Critique de Configuration</h1><p>Impossible de charger le fichier .env.</p>";
    error_log("Erreur Dotenv: " . $e->getMessage());
    exit;
}

// Configuration de l'affichage des erreurs en fonction de l'environnement
if ($appEnv === 'development') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0); // Désactiver l'affichage des erreurs en production
}

use App\Backend\Util\DatabaseSessionHandler;
use App\Config\Container;

$container = new Container();

// Configuration du gestionnaire de session
$handler = $container->get(DatabaseSessionHandler::class);
session_set_save_handler($handler, true);

// Configuration des paramètres de cookie de session
session_set_cookie_params([
    'lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 3600),
    'path' => '/',
    'domain' => $_ENV['SESSION_DOMAIN'] ?? $_SERVER['SERVER_NAME'],
    'secure' => ($_ENV['APP_ENV'] === 'production'), // Utiliser HTTPS en production
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $routesFilePath = ROOT_PATH . '/routes/web.php';
    if (!file_exists($routesFilePath)) {
        throw new \RuntimeException("Fichier de routes introuvable: " . $routesFilePath);
    }
    $routeDefinitionCallback = require $routesFilePath;
    $routeDefinitionCallback($r);
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode(rtrim($uri, '/')) ?: '/';

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

try {
    switch ($routeInfo[0]) {
        case FastRoute\Dispatcher::NOT_FOUND:
            http_response_code(404);
            include ROOT_PATH . '/src/Frontend/views/errors/404.php';
            break;

        case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
            $allowedMethods = $routeInfo[1];
            http_response_code(405);
            header('Allow: ' . implode(', ', $allowedMethods));
            include ROOT_PATH . '/src/Frontend/views/errors/405.php';
            break;

        case FastRoute\Dispatcher::FOUND:
            $handler = $routeInfo[1];
            $vars = $routeInfo[2];

            if (is_array($handler) && count($handler) === 2 && class_exists($handler[0])) {
                $controllerClass = $handler[0];
                $methodName = $handler[1];

                $controllerInstance = $container->get($controllerClass);

                if (!method_exists($controllerInstance, $methodName)) {
                    throw new \RuntimeException("La méthode '{$methodName}' n'existe pas sur le contrôleur '{$controllerClass}'.");
                }

                call_user_func_array([$controllerInstance, $methodName], $vars);
            } else {
                throw new \RuntimeException("Gestionnaire de route mal configuré ou classe/méthode introuvable pour URI: " . htmlspecialchars($uri));
            }
            break;
    }
} catch (\Throwable $e) {
    http_response_code(500);
    error_log("Erreur non interceptée: " . $e->getMessage() . " dans " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString());
    if ($appEnv === 'development') {
        echo "<h1>Erreur 500</h1><p>Détails: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        include ROOT_PATH . '/src/Frontend/views/errors/500.php';
    }
}