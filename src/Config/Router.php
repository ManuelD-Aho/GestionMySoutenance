<?php
// src/Config/Router.php

namespace App\Config;

use App\Backend\Controller\BaseController; // Pour la gestion des erreurs 404/405
use App\Backend\Exception\ElementNonTrouveException; // Pour les vues d'erreur
use Closure; // Import de la classe Closure pour un typage plus précis si désiré

class Router
{
    private static ?Container $container = null;
    private static array $routes = [];
    private static array $middlewares = [];

    // Correction ici : Utiliser 'mixed' ou '?Closure' pour les propriétés de type callable
    private static mixed $notFoundHandler = null; // Anciennement ?callable
    private static mixed $methodNotAllowedHandler = null; // Anciennement ?callable

    /**
     * Définit le conteneur de dépendances pour le routeur.
     * Doit être appelé une fois au démarrage de l'application.
     */
    public static function setContainer(Container $container): void
    {
        self::$container = $container;
    }

    /**
     * Enregistre une route GET.
     */
    public static function get(string $path, array $handler): void
    {
        self::addRoute('GET', $path, $handler);
    }

    /**
     * Enregistre une route POST.
     */
    public static function post(string $path, array $handler): void
    {
        self::addRoute('POST', $path, $handler);
    }

    /**
     * Enregistre une route PUT.
     */
    public static function put(string $path, array $handler): void
    {
        self::addRoute('PUT', $path, $handler);
    }

    /**
     * Enregistre une route DELETE.
     */
    public static function delete(string $path, array $handler): void
    {
        self::addRoute('DELETE', $path, $handler);
    }

    /**
     * Ajoute une route au tableau des routes.
     */
    private static function addRoute(string $method, string $path, array $handler): void
    {
        // S'assurer que 'middlewares' est toujours un tableau vide par défaut
        self::$routes[$method][$path] = [
            'handler' => $handler,
            'middlewares' => []
        ];
    }

    /**
     * Définit un groupe de routes avec des middlewares.
     */
    public static function group(array $options, callable $callback): void
    {
        $currentMiddlewares = $options['middleware'] ?? [];
        if (!is_array($currentMiddlewares)) { // S'assurer que c'est un tableau
            $currentMiddlewares = [$currentMiddlewares];
        }

        $tempRoutes = self::$routes;
        self::$routes = [];

        $callback();

        foreach (self::$routes as $method => $routesByMethod) {
            foreach ($routesByMethod as $path => $routeData) {
                // S'assurer que $routeData['middlewares'] est un tableau avant de fusionner
                $existingMiddlewares = $routeData['middlewares'] ?? [];
                if (!is_array($existingMiddlewares)) {
                    $existingMiddlewares = [$existingMiddlewares];
                }
                self::$routes[$method][$path]['middlewares'] = array_merge($existingMiddlewares, $currentMiddlewares);
            }
        }

        // Utiliser array_merge_recursive pour fusionner correctement les routes
        // Cela gérera mieux les cas où des routes sont définies à la fois dans et hors des groupes
        self::$routes = array_merge_recursive($tempRoutes, self::$routes);
    }

    /**
     * Enregistre un middleware.
     */
    public static function middleware(string $name, callable $handler): void
    {
        self::$middlewares[$name] = $handler;
    }

    /**
     * Définit le gestionnaire pour les routes non trouvées (404).
     */
    public static function notFound(callable $handler): void
    {
        self::$notFoundHandler = $handler;
    }

    /**
     * Définit le gestionnaire pour les méthodes non autorisées (405).
     */
    public static function methodNotAllowed(callable $handler): void
    {
        self::$methodNotAllowedHandler = $handler;
    }

    /**
     * Dispatche la requête entrante vers le contrôleur approprié.
     */
    public static function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Nettoyer l'URI pour correspondre aux routes (ex: supprimer le slash final)
        $requestUri = rtrim($requestUri, '/');
        if ($requestUri === '') {
            $requestUri = '/';
        }

        $matchedRoute = null;
        $params = [];

        // Chercher une correspondance exacte d'abord
        if (isset(self::$routes[$requestMethod][$requestUri])) {
            $matchedRoute = self::$routes[$requestMethod][$requestUri];
        } else {
            // Chercher une correspondance avec des paramètres dynamiques
            foreach (self::$routes[$requestMethod] ?? [] as $routePath => $routeData) {
                // Convertir le chemin de la route en regex
                $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_.-]+)', $routePath);
                $pattern = '#^' . $pattern . '$#';

                if (preg_match($pattern, $requestUri, $matches)) {
                    array_shift($matches); // Supprimer la correspondance complète

                    // Extraire les noms des paramètres de la route
                    preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $routePath, $paramNames);
                    $paramNames = $paramNames[1];

                    // Assigner les valeurs aux noms de paramètres
                    $params = array_combine($paramNames, $matches);

                    $matchedRoute = $routeData;
                    break;
                }
            }
        }

        // Gérer les cas 404 et 405
        if ($matchedRoute === null) {
            // Vérifier si l'URI existe pour une autre méthode (405)
            foreach (self::$routes as $method => $routesByMethod) {
                if ($method !== $requestMethod) {
                    foreach ($routesByMethod as $routePath => $routeData) {
                        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_.-]+)', $routePath);
                        $pattern = '#^' . $pattern . '$#';
                        if (preg_match($pattern, $requestUri)) {
                            if (self::$methodNotAllowedHandler) {
                                call_user_func(self::$methodNotAllowedHandler);
                            } else {
                                http_response_code(405);
                                echo "<h1>405 - Méthode non autorisée</h1><p>La méthode HTTP utilisée n'est pas autorisée pour cette ressource.</p>";
                            }
                            exit();
                        }
                    }
                }
            }

            // Si aucune correspondance du tout (404)
            if (self::$notFoundHandler) {
                call_user_func(self::$notFoundHandler);
            } else {
                http_response_code(404);
                echo "<h1>404 - Page non trouvée</h1><p>La page que vous recherchez n'existe pas.</p>";
            }
            exit();
        }

        // Exécuter les middlewares
        foreach ($matchedRoute['middlewares'] as $middlewareName) {
            if (isset(self::$middlewares[$middlewareName])) {
                call_user_func(self::$middlewares[$middlewareName]);
            } else {
                throw new \RuntimeException("Middleware '{$middlewareName}' non défini.");
            }
        }

        // Exécuter le contrôleur
        if (self::$container === null) {
            throw new \RuntimeException("Le conteneur de dépendances n'a pas été défini pour le routeur.");
        }

        $controllerClass = $matchedRoute['handler'][0];
        $methodName = $matchedRoute['handler'][1];

        // Instancier le contrôleur via le conteneur
        $controllerInstance = self::$container->get($controllerClass);

        // Appeler la méthode du contrôleur avec les paramètres extraits de l'URL
        call_user_func_array([$controllerInstance, $methodName], $params);
    }

}