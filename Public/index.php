<?php
// Public/index.php

// Définir le chemin racine du projet
define('ROOT_PATH', dirname(__DIR__));

// Autoload des classes via Composer
require_once ROOT_PATH . '/vendor/autoload.php';

// Charger les variables d'environnement
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();

// Configuration de l'affichage des erreurs (pour le développement)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Utilisation des classes du projet
use App\Config\Container;
use App\Config\Router;
use App\Backend\Controller\BaseController;
use App\Backend\Util\DatabaseSessionHandler;
use App\Backend\Service\Securite\ServiceSecuriteInterface;

// Initialisation du conteneur de dépendances
$container = new Container();

// --- CORRECTION ICI : Enregistrer le gestionnaire de session AVANT session_start() ---
// Assurez-vous que la table 'sessions' existe dans votre base de données
// et que le modèle 'sessions' est correctement défini dans le conteneur.
$sessionHandler = new DatabaseSessionHandler($container->get(PDO::class), $container->getModelForTable('sessions', 'session_id'));
session_set_save_handler($sessionHandler, true);

// Démarrer la session APRÈS avoir défini le gestionnaire
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// --- FIN DE LA CORRECTION ---


// --- Gestionnaire d'erreurs global ---
set_exception_handler(function (\Throwable $exception) use ($container) {
    // Log l'erreur pour le débogage
    error_log("Unhandled exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
    error_log("Stack trace: " . $exception->getTraceAsString()); // Ajout de la trace complète

    // Récupérer le BaseController pour rendre une page d'erreur stylisée
    try {
        /** @var BaseController $baseController */
        $baseController = $container->get(BaseController::class);
        if ($exception instanceof \App\Backend\Exception\PermissionException) {
            $baseController->renderError(403, $exception->getMessage());
        } elseif ($exception instanceof \App\Backend\Exception\ElementNonTrouveException) {
            $baseController->renderError(404, $exception->getMessage());
        } else {
            // Pour les erreurs 500, on peut donner un message générique en production
            $errorMessage = ($_ENV['APP_ENV'] ?? 'production') === 'development' ? $exception->getMessage() : "Une erreur interne est survenue. Veuillez réessayer plus tard.";
            $baseController->renderError(500, $errorMessage);
        }
    } catch (\Exception $e) {
        // Fallback si même le contrôleur d'erreur ne peut pas être rendu
        http_response_code(500);
        echo "<h1>500 - Erreur Interne du Serveur</h1><p>Une erreur critique est survenue et n'a pas pu être gérée correctement.</p>";
        error_log("Failed to render error page: " . $e->getMessage());
    }
});

// Définir le conteneur pour le routeur
Router::setContainer($container);

// --- Définition des middlewares ---
Router::middleware('auth', function() use ($container) {
    /** @var ServiceSecuriteInterface $securiteService */
    $securiteService = $container->get(ServiceSecuriteInterface::class);
    if (!$securiteService->estUtilisateurConnecte()) {
        // Rediriger vers la page de connexion si non authentifié
        header('Location: /login');
        exit();
    }
});

Router::middleware('guest', function() use ($container) {
    /** @var ServiceSecuriteInterface $securiteService */
    $securiteService = $container->get(ServiceSecuriteInterface::class);
    if ($securiteService->estUtilisateurConnecte()) {
        // Rediriger vers le tableau de bord si déjà authentifié
        header('Location: /dashboard');
        exit();
    }
});

// --- Définition des gestionnaires d'erreurs 404 et 405 ---
Router::notFound(function() use ($container) {
    /** @var BaseController $baseController */
    $baseController = $container->get(BaseController::class);
    $baseController->renderError(404, "La page que vous recherchez n'existe pas.");
});

Router::methodNotAllowed(function() use ($container) {
    /** @var BaseController $baseController */
    $baseController = $container->get(BaseController::class);
    $baseController->renderError(405, "La méthode HTTP utilisée n'est pas autorisée pour cette ressource.");
});

// --- Inclusion des routes ---
require_once ROOT_PATH . '/routes/web.php';

// Dispatcher la requête
Router::dispatch();