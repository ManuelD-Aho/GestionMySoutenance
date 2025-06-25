<?php

declare(strict_types=1);

// Constante pour la racine du projet
define('ROOT_PATH', dirname(__DIR__));

// Autoloader de Composer
if (!file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    http_response_code(503);
    echo "<h1>Erreur Critique d'Initialisation</h1><p>Les dépendances sont introuvables. Veuillez exécuter 'composer install'.</p>";
    exit;
}
require_once ROOT_PATH . '/vendor/autoload.php';

// Chargement des variables d'environnement
try {
    if (file_exists(ROOT_PATH . '/.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
        $dotenv->load();
    }
} catch (\Throwable $e) {
    http_response_code(503);
    echo "<h1>Erreur Critique de Configuration</h1><p>Impossible de charger le fichier .env.</p>";
    error_log("Erreur Dotenv: " . $e->getMessage());
    exit;
}

// Configuration de la gestion des erreurs
$appEnv = $_ENV['APP_ENV'] ?? 'production';
if ($appEnv === 'development') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
    // En production, utiliser un gestionnaire d'erreurs plus robuste (ex: set_exception_handler)
}

// =======================================================================
// DÉMARRAGE SÉCURISÉ DE LA SESSION
// =======================================================================
use App\Backend\Util\DatabaseSessionHandler;
use App\Config\Container; // AJOUT : Import du Container

// Création de l'instance du conteneur de dépendances
$container = new Container(); // AJOUT : Instance du conteneur

// 1. Initialisation de notre gestionnaire de session en base de données
// NOTE : Le DatabaseSessionHandler DOIT être instancié via le conteneur si ses dépendances sont gérées par le conteneur
// Pour l'instant, on le laisse instancié directement ici, mais il faudra peut-être l'injecter via le conteneur plus tard
// si son constructeur prend des dépendances complexes (comme un PDO).
// ACTUELLEMENT : Votre DatabaseSessionHandler::getDb() gère sa propre connexion via Database::getConnection()
// Donc l'instanciation directe ici est acceptable.
$handler = new DatabaseSessionHandler();
session_set_save_handler($handler, true);

// 2. Configuration des cookies de session
session_set_cookie_params([
    'lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 3600),
    'path' => '/',
    'domain' => $_ENV['SESSION_DOMAIN'] ?? $_SERVER['SERVER_NAME'],
    'secure' => ($_ENV['APP_ENV'] === 'production'),
    'httponly' => true,
    'samesite' => 'Lax'
]);

// 3. Démarrage effectif de la session
session_start();
// =======================================================================


// Initialisation du Routeur
$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $routesFilePath = ROOT_PATH . '/routes/web.php';
    if (!file_exists($routesFilePath)) {
        throw new \RuntimeException("Fichier de routes introuvable: " . $routesFilePath);
    }
    $routeDefinitionCallback = require $routesFilePath;
    $routeDefinitionCallback($r);
});

// Récupération de la méthode HTTP et de l'URI
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode(rtrim($uri, '/')) ?: '/';

// Dispatch de la Route
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

try {
    switch ($routeInfo[0]) {
        case FastRoute\Dispatcher::NOT_FOUND:
            http_response_code(404);
            // AJOUT : Utiliser le conteneur pour les pages d'erreur pour une approche cohérente
            // Si vos pages d'erreur sont de simples fichiers HTML/PHP sans logique complexe,
            // un simple include est suffisant. Mais pour être cohérent avec le DI, on peut faire:
            // $errorController = $container->get(ErrorController::class); // Si vous avez un ErrorController
            // $errorController->show404();
            include ROOT_PATH . '/src/Frontend/views/errors/404.php';
            break;

        case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
            $allowedMethods = $routeInfo[1];
            http_response_code(405);
            header('Allow: ' . implode(', ', $allowedMethods));
            // Même remarque ici pour un ErrorController
            include ROOT_PATH . '/src/Frontend/views/errors/405.php';
            break;

        case FastRoute\Dispatcher::FOUND:
            $handler = $routeInfo[1]; // $handler est de la forme [ControllerClass::class, 'methodName']
            $vars = $routeInfo[2];

            // MODIFICATION CRUCIALE : Instancier le contrôleur via le conteneur de dépendances
            if (is_array($handler) && count($handler) === 2 && class_exists($handler[0])) {
                $controllerClass = $handler[0];
                $methodName = $handler[1];

                // Utilisez le conteneur pour obtenir l'instance du contrôleur
                // C'est ici que le conteneur va injecter les dépendances (ServiceAuthentification, etc.)
                $controllerInstance = $container->get($controllerClass); // MODIFICATION

                // Vérifiez si la méthode existe AVANT d'appeler call_user_func_array
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
} finally {
    // Nettoyage des messages flash de la session
    unset($_SESSION['error_message']);
    unset($_SESSION['success_message']);
}