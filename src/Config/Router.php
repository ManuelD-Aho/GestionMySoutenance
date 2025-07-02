<?php
// src/Config/Router.php

namespace App\Config;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Throwable;

/**
 * Classe Router qui gère le dispatching des requêtes HTTP.
 * Elle utilise la bibliothèque FastRoute pour faire correspondre l'URI à un contrôleur
 * et utilise le conteneur de dépendances pour instancier ce dernier.
 */
class Router
{
    private Container $container;
    private Dispatcher $dispatcher;

    /**
     * Construit le routeur.
     *
     * @param Container $container Le conteneur d'injection de dépendances.
     * @param callable $routeDefinitionCallback Une closure qui définit les routes (généralement depuis routes/web.php).
     */
    public function __construct(Container $container, callable $routeDefinitionCallback)
    {
        $this->container = $container;
        $this->dispatcher = \FastRoute\simpleDispatcher($routeDefinitionCallback);
    }

    /**
     * Traite la requête HTTP entrante.
     *
     * @param string $httpMethod La méthode HTTP de la requête (ex: 'GET', 'POST').
     * @param string $uri L'URI de la requête.
     */
    public function dispatch(string $httpMethod, string $uri): void
    {
        // Nettoie l'URI pour enlever les query strings
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);

        try {
            switch ($routeInfo[0]) {
                case Dispatcher::NOT_FOUND:
                    $this->handleNotFound();
                    break;

                case Dispatcher::METHOD_NOT_ALLOWED:
                    $this->handleMethodNotAllowed($routeInfo[1]);
                    break;

                case Dispatcher::FOUND:
                    $handler = $routeInfo[1];
                    $vars = $routeInfo[2];
                    $this->handleFound($handler, $vars);
                    break;
            }
        } catch (Throwable $e) {
            // Gestionnaire d'exception global pour toutes les erreurs survenant pendant le dispatching.
            $this->handleException($e);
        }
    }

    /**
     * Gère une route trouvée en exécutant le contrôleur approprié.
     *
     * @param array $handler Le handler [Controller::class, 'methodName'].
     * @param array $vars Les variables extraites de l'URI.
     */
    private function handleFound(array $handler, array $vars): void
    {
        [$controllerClass, $methodName] = $handler;

        // Utilise le conteneur pour obtenir une instance du contrôleur
        $controller = $this->container->get($controllerClass);

        // Appelle la méthode du contrôleur avec les paramètres de l'URL
        call_user_func_array([$controller, $methodName], $vars);
    }

    /**
     * Gère le cas où aucune route ne correspond à l'URI.
     */
    private function handleNotFound(): void
    {
        http_response_code(404);
        // Dans une application réelle, vous rendriez une vue ici.
        // $this->container->get(ErrorController::class)->show404();
        echo "<h1>404 - Page Non Trouvée</h1>";
    }

    /**
     * Gère le cas où une route correspond à l'URI mais pas à la méthode HTTP.
     *
     * @param array $allowedMethods Les méthodes HTTP autorisées pour cette URI.
     */
    private function handleMethodNotAllowed(array $allowedMethods): void
    {
        http_response_code(405);
        header('Allow: ' . implode(', ', $allowedMethods));
        // $this->container->get(ErrorController::class)->show405();
        echo "<h1>405 - Méthode Non Autorisée</h1>";
    }

    /**
     * Gère les exceptions non interceptées pour afficher une page d'erreur 500.
     *
     * @param Throwable $exception L'exception interceptée.
     */
    private function handleException(Throwable $exception): void
    {
        // Log l'erreur pour le débogage (ne jamais l'afficher en production)
        error_log($exception->getMessage() . "\n" . $exception->getTraceAsString());

        // Affiche une page d'erreur générique à l'utilisateur
        if (!headers_sent()) {
            http_response_code(500);
        }
        // $this->container->get(ErrorController::class)->show500();
        echo "<h1>500 - Erreur Interne du Serveur</h1><p>Une erreur inattendue est survenue. Notre équipe technique a été notifiée.</p>";
    }
}