<?php

declare(strict_types=1);

// Constante pour la racine du projet
define('ROOT_PATH', dirname(__DIR__));

// Autoloader de Composer
if (!file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    http_response_code(503); // Service Unavailable
    echo "<h1>Erreur Critique d'Initialisation</h1>";
    echo "<p>Les dépendances de l'application (vendor/autoload.php) sont introuvables.</p>";
    echo "<p>Veuillez exécuter 'composer install' à la racine de votre projet et vérifier la configuration.</p>";
    // En production, loguer cette erreur et afficher une page d'erreur plus générique.
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
    echo "<h1>Erreur Critique de Configuration</h1><p>Impossible de charger les variables d'environnement.</p>";
    error_log("Erreur Dotenv: " . $e->getMessage()); // Log pour l'admin
    exit;
}

// Configuration de la gestion des erreurs basée sur l'environnement
$appEnv = $_ENV['APP_ENV'] ?? 'production';
if ($appEnv === 'development') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
    // En production, vous devriez configurer un gestionnaire d'erreurs plus robuste
    // qui logue les erreurs dans un fichier ou un service externe.
    // Par exemple, en utilisant set_error_handler() et set_exception_handler().
}

// Démarrage de la session (si ce n'est pas déjà fait)
if (session_status() === PHP_SESSION_NONE) {
    // Configurer les paramètres de session avant session_start() pour plus de sécurité
    session_set_cookie_params([
        'lifetime' => $_ENV['SESSION_LIFETIME'] ?? 3600, // Durée de vie du cookie de session
        'path' => '/',
        'domain' => $_ENV['SESSION_DOMAIN'] ?? $_SERVER['SERVER_NAME'],
        'secure' => ($_ENV['APP_ENV'] === 'production' && (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')), // True en HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
    // Optionnel: Régénérer l'ID de session pour prévenir la fixation de session,
    // mais cela doit être géré intelligemment pour ne pas perdre la session à chaque requête.
    // Souvent fait lors du login/logout.
}

// Initialisation du Routeur
$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $routesFilePath = ROOT_PATH . '/routes/web.php';
    if (!file_exists($routesFilePath)) {
        // En production, loguer et afficher une erreur générique.
        throw new \RuntimeException("Fichier de routes introuvable: " . $routesFilePath);
    }
    $routeDefinitionCallback = require $routesFilePath;
    if (!is_callable($routeDefinitionCallback)) {
        throw new \RuntimeException("Le fichier de routes doit retourner une fonction callable.");
    }
    $routeDefinitionCallback($r);
});

// Récupération de la méthode HTTP et de l'URI
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Nettoyage de l'URI (supprimer les query strings)
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode(rtrim($uri, '/')) ?: '/'; // Assurer que la racine est '/'

// Dispatch de la Route
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// Gestion du résultat du dispatch
try {
    switch ($routeInfo[0]) {
        case FastRoute\Dispatcher::NOT_FOUND:
            http_response_code(404);
            // Inclure une vue d'erreur 404 ou appeler un contrôleur d'erreur
            // Pour simplifier, on peut directement inclure une vue si BaseController n'est pas encore dispo
            if (file_exists(ROOT_PATH . '/src/Frontend/views/errors/404.php')) {
                // Vous pourriez vouloir passer un titre et un message à cette vue
                include ROOT_PATH . '/src/Frontend/views/errors/404.php';
            } else {
                echo '<h1>404 - Page Non Trouvée</h1>';
            }
            break;

        case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
            $allowedMethods = $routeInfo[1];
            http_response_code(405);
            header('Allow: ' . implode(', ', $allowedMethods));
            if (file_exists(ROOT_PATH . '/src/Frontend/views/errors/405.php')) {
                // Passer $allowedMethods à la vue
                include ROOT_PATH . '/src/Frontend/views/errors/405.php';
            } else {
                echo '<h1>405 - Méthode Non Autorisée</h1><p>Méthodes autorisées : ' . implode(', ', $allowedMethods) . '</p>';
            }
            break;

        case FastRoute\Dispatcher::FOUND:
            $handler = $routeInfo[1];
            $vars = $routeInfo[2]; // Paramètres de l'URL (ex: {id})

            if (is_array($handler) && count($handler) === 2 && class_exists($handler[0]) && method_exists(new $handler[0](), $handler[1])) {
                $controllerClass = $handler[0];
                $methodName = $handler[1];

                // Ici, vous pourriez implémenter une injection de dépendances basique si nécessaire
                // ou votre BaseController pourrait gérer l'instanciation des services communs.
                $controllerInstance = new $controllerClass();

                // Appel de la méthode du contrôleur avec les variables de route
                call_user_func_array([$controllerInstance, $methodName], $vars);

            } elseif (is_callable($handler)) { // Pour les routes définies avec des Closures
                call_user_func_array($handler, $vars);
            } else {
                throw new \RuntimeException("Gestionnaire de route mal configuré pour URI: " . htmlspecialchars($uri));
            }
            break;
        default:
            // Cas inattendu du dispatcher
            throw new \RuntimeException("Réponse inattendue du dispatcher de routes.");
    }
} catch (\Throwable $e) {
    // Gestionnaire d'exception global (simplifié)
    http_response_code(500);
    error_log("Erreur non interceptée: " . $e->getMessage() . " dans " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString());
    if ($appEnv === 'development') {
        echo "<h1>Erreur Serveur Critique</h1><p>Une erreur est survenue : " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        if (file_exists(ROOT_PATH . '/src/Frontend/views/errors/500.php')) {
            include ROOT_PATH . '/src/Frontend/views/errors/500.php';
        } else {
            echo '<h1>500 - Erreur Interne du Serveur</h1><p>Une erreur inattendue est survenue. Veuillez réessayer plus tard.</p>';
        }
    }
}

// Nettoyage des messages flash (si vous utilisez cette méthode simple)
if (isset($_SESSION['error_message'])) unset($_SESSION['error_message']);
if (isset($_SESSION['success_message'])) unset($_SESSION['success_message']);
if (isset($_SESSION['login_error_message'])) unset($_SESSION['login_error_message']);
if (isset($_SESSION['login_form_data'])) unset($_SESSION['login_form_data']);
// ... etc. pour tous les messages flash que vous pourriez utiliser.