<?php
// Public/index.php

use App\Config\Container;
use App\Config\Router;
use App\Backend\Util\DatabaseSessionHandler;
use Dotenv\Dotenv;

// --- 1. Initialisation Fondamentale ---

// Définir une constante pour le chemin racine du projet.
define('ROOT_PATH', dirname(__DIR__));

// Charger l'autoloader de Composer.
require_once ROOT_PATH . '/vendor/autoload.php';

// --- 2. Gestion des Erreurs et Exceptions ---

// Définir un gestionnaire d'exceptions global pour intercepter toutes les erreurs non capturées.
// Cela garantit qu'aucune information sensible (comme les traces de pile) n'est jamais affichée à l'utilisateur final.
set_exception_handler(function (Throwable $e) {
    // Logguer l'erreur de manière détaillée pour les développeurs.
    error_log("Uncaught Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());

    // Afficher une page d'erreur générique et sécurisée à l'utilisateur.
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo "<h1>Erreur 500</h1><p>Une erreur critique est survenue. Veuillez réessayer plus tard.</p>";
});

// --- 3. Configuration de l'Environnement ---

// Charger les variables d'environnement depuis le fichier .env.
// Cela permet de séparer la configuration (mots de passe, clés API) du code.
try {
    $dotenv = Dotenv::createImmutable(ROOT_PATH);
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    die("Erreur critique : Le fichier .env est introuvable. Veuillez vous assurer qu'il existe à la racine du projet.");
}

// --- 4. Initialisation du Conteneur de Dépendances ---

// Le conteneur est le cœur de l'application, il gère la création de tous les objets.
$container = new Container();

// --- 5. Configuration et Démarrage de la Session ---

// Utiliser notre gestionnaire de session personnalisé qui stocke les sessions en base de données.
// Cela permet une gestion centralisée et plus sécurisée des sessions.
/** @var DatabaseSessionHandler $sessionHandler */
$sessionHandler = $container->get(DatabaseSessionHandler::class);
session_set_save_handler($sessionHandler, true);

// Démarrer la session. Doit être fait après avoir défini le handler.
session_start();

// --- 6. Routage et Dispatching de la Requête ---

// Charger les définitions de routes depuis le fichier dédié.
$routeDefinitionCallback = require_once ROOT_PATH . '/routes/web.php';

// Instancier le routeur en lui passant le conteneur et les définitions de routes.
$router = new Router($container, $routeDefinitionCallback);

// Récupérer la méthode HTTP et l'URI de la requête actuelle.
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Lancer le processus de dispatching : le routeur va trouver la bonne route et exécuter le contrôleur correspondant.
$router->dispatch($httpMethod, $uri);