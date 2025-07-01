<?php
// Public/index.php - Contrôleur Frontal de GestionMySoutenance

declare(strict_types=1);

// ==============================================================================
// 1. BOOTSTRAPPING : Initialisation de l'Application
// ==============================================================================

// Définir le chemin racine du projet pour des chemins de fichiers fiables
define('ROOT_PATH', dirname(__DIR__));

// Charger l'autoloader de Composer. C'est une dépendance critique.
// Si absent, on affiche une erreur claire et on arrête tout.
if (!file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    http_response_code(503); // Service Unavailable
    echo "<h1>Erreur Critique d'Initialisation</h1><p>Dépendances introuvables. Veuillez exécuter 'composer install' à la racine du projet.</p>";
    exit;
}
require_once ROOT_PATH . '/vendor/autoload.php';

// ==============================================================================
// 2. GESTION DE L'ENVIRONNEMENT ET DES ERREURS
// ==============================================================================

try {
    // Détecter l'environnement (dev, prod) via une variable d'environnement serveur.
    // Par défaut, on considère être en développement pour plus de verbosité en cas d'erreur.
    $appEnv = $_ENV['APP_ENV'] ?? 'development';

    if (file_exists(ROOT_PATH . '/.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
        $dotenv->load();
    }

} catch (\Throwable $e) {
    http_response_code(503);
    echo "<h1>Erreur Critique de Configuration</h1><p>Impossible de charger les variables d'environnement. Assurez-vous que le fichier .env existe et est correct.</p>";
    // Afficher le message d'erreur réel pour le débogage
    echo "<p><small>Détail de l'erreur : " . htmlspecialchars($e->getMessage()) . "</small></p>";
    error_log("Erreur Dotenv: " . $e->getMessage());
    exit;
}


// Configurer l'affichage des erreurs en fonction de l'environnement.
// En développement : tout afficher pour déboguer facilement.
// En production : ne rien afficher à l'utilisateur et tout logger.
if ($appEnv === 'development') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
    // La journalisation des erreurs est gérée par le ServiceLogger qui sera configuré plus tard.
}

// ==============================================================================
// 3. DÉMARRAGE DU CONTENEUR DE SERVICES ET DE LA SESSION
// ==============================================================================

use App\Config\Container;
use App\Backend\Util\DatabaseSessionHandler;

// Instancier le conteneur d'injection de dépendances. C'est le chef d'orchestre.
$container = new Container();

// Configurer PHP pour utiliser notre gestionnaire de session basé sur la base de données.
// Cela centralise les sessions et permet des fonctionnalités avancées.
$sessionHandler = $container->get(DatabaseSessionHandler::class);
session_set_save_handler($sessionHandler, true);

// Configurer les paramètres de cookie de session pour plus de sécurité.
session_set_cookie_params([
    'lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 3600), // Durée de vie du cookie
    'path' => '/',
    'domain' => $_ENV['SESSION_DOMAIN'] ?? $_SERVER['SERVER_NAME'],
    'secure' => ($_ENV['APP_ENV'] === 'production'), // 'true' en production (HTTPS)
    'httponly' => true, // Empêche l'accès au cookie via JavaScript
    'samesite' => 'Lax' // Protection contre les attaques CSRF
]);

// Démarrer la session.
session_start();

// ==============================================================================
// 4. ROUTAGE : Interprétation de la Requête HTTP
// ==============================================================================

// Utilisation de FastRoute, une bibliothèque de routage légère et performante.
// On définit toutes les routes de l'application en une seule fois.
$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $routesFilePath = ROOT_PATH . '/routes/web.php';
    if (!file_exists($routesFilePath)) {
        throw new \RuntimeException("Fichier de routes introuvable : " . $routesFilePath);
    }
    // Le fichier web.php retourne une fonction qui prend le collecteur de routes en argument.
    $routeDefinitionCallback = require $routesFilePath;
    $routeDefinitionCallback($r);
});

// Récupérer la méthode HTTP (GET, POST, etc.) et l'URI de la requête.
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Nettoyer l'URI pour enlever les paramètres GET (ex: ?page=2).
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode(rtrim($uri, '/')) ?: '/'; // Décoder l'URI et s'assurer qu'elle commence par un /

// Lancer le dispatching : FastRoute compare la requête à la liste des routes définies.
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// ==============================================================================
// 5. DISPATCHING : Exécution du Contrôleur Approprié
// ==============================================================================

try {
    switch ($routeInfo[0]) {
        // Cas 1 : La route n'a pas été trouvée.
        case FastRoute\Dispatcher::NOT_FOUND:
            http_response_code(404);
            // On inclut une vue d'erreur propre.
            include ROOT_PATH . '/src/Frontend/views/errors/404.php';
            break;

        // Cas 2 : La route existe, mais pas pour cette méthode HTTP (ex: POST sur une route GET).
        case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
            $allowedMethods = $routeInfo[1];
            http_response_code(405);
            header('Allow: ' . implode(', ', $allowedMethods)); // Indiquer les méthodes autorisées
            include ROOT_PATH . '/src/Frontend/views/errors/405.php';
            break;

        // Cas 3 : La route a été trouvée !
        case FastRoute\Dispatcher::FOUND:
            $handler = $routeInfo[1]; // Le gestionnaire (ex: [UtilisateurController::class, 'index'])
            $vars = $routeInfo[2];    // Les variables de l'URI (ex: l'ID dans /users/{id})

            // Vérifier que le gestionnaire est bien un tableau [classe, méthode].
            if (is_array($handler) && count($handler) === 2 && class_exists($handler[0])) {
                $controllerClass = $handler[0];
                $methodName = $handler[1];

                // Utiliser notre conteneur pour obtenir une instance du contrôleur.
                // Le conteneur s'occupera d'injecter toutes ses dépendances (services, etc.).
                $controllerInstance = $container->get($controllerClass);

                if (!method_exists($controllerInstance, $methodName)) {
                    throw new \RuntimeException("La méthode '{$methodName}' n'existe pas sur le contrôleur '{$controllerClass}'.");
                }

                // Appeler la méthode du contrôleur en lui passant les variables de l'URI.
                call_user_func_array([$controllerInstance, $methodName], $vars);
            } else {
                throw new \RuntimeException("Gestionnaire de route mal configuré pour l'URI : " . htmlspecialchars($uri));
            }
            break;
    }
} catch (\Throwable $e) {
    // Capture de toutes les erreurs non interceptées pour un affichage propre.
    http_response_code(500);
    // Logger l'erreur complète pour le débogage.
    error_log("Erreur non interceptée dans index.php: " . $e->getMessage() . " dans " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString());

    // Afficher une page d'erreur générique en production, ou les détails en développement.
    if ($appEnv === 'development') {
        echo "<h1>Erreur 500 - Erreur Interne du Serveur</h1><p>Détails: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        include ROOT_PATH . '/src/Frontend/views/errors/500.php';
    }
}