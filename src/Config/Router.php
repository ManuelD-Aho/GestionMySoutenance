<?php
// src/Config/Router.php

namespace App\Config;

use App\Backend\Controller\BaseController;
use App\Backend\Exception\ElementNonTrouveException;
use Closure;

class Router
{
    private static ?Container $container = null;
    private static array $routes = [];
    private static array $middlewares = [];
    private static mixed $notFoundHandler = null;
    private static mixed $methodNotAllowedHandler = null;

    public static function setContainer(Container $container): void
    {
        self::$container = $container;
    }

    public static function get(string $path, array $handler): void
    {
        self::addRoute('GET', $path, $handler);
    }

    public static function post(string $path, array $handler): void
    {
        self::addRoute('POST', $path, $handler);
    }

    public static function put(string $path, array $handler): void
    {
        self::addRoute('PUT', $path, $handler);
    }

    public static function delete(string $path, array $handler): void
    {
        self::addRoute('DELETE', $path, $handler);
    }

    private static function addRoute(string $method, string $path, array $handler): void
    {
        self::$routes[$method][$path] = [
            'handler' => $handler,
            'middlewares' => []
        ];
    }

    public static function group(array $options, callable $callback): void
    {
        $currentMiddlewares = $options['middleware'] ?? [];
        if (!is_array($currentMiddlewares)) {
            $currentMiddlewares = [$currentMiddlewares];
        }

        $tempRoutes = self::$routes;
        self::$routes = [];

        $callback();

        foreach (self::$routes as $method => $routesByMethod) {
            foreach ($routesByMethod as $path => $routeData) {
                $existingMiddlewares = $routeData['middlewares'] ?? [];
                if (!is_array($existingMiddlewares)) {
                    $existingMiddlewares = [$existingMiddlewares];
                }
                self::$routes[$method][$path]['middlewares'] = array_merge($existingMiddlewares, $currentMiddlewares);
            }
        }
        self::$routes = array_merge_recursive($tempRoutes, self::$routes);
    }

    public static function middleware(string $name, callable $handler): void
    {
        self::$middlewares[$name] = $handler;
    }

    public static function notFound(callable $handler): void
    {
        self::$notFoundHandler = $handler;
    }

    public static function methodNotAllowed(callable $handler): void
    {
        self::$methodNotAllowedHandler = $handler;
    }

    public static function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requestUri = rtrim($requestUri, '/');
        if ($requestUri === '') {
            $requestUri = '/';
        }

        $matchedRoute = null;
        $params = [];

        if (isset(self::$routes[$requestMethod][$requestUri])) {
            $matchedRoute = self::$routes[$requestMethod][$requestUri];
        } else {
            foreach (self::$routes[$requestMethod] ?? [] as $routePath => $routeData) {
                $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_.-]+)', $routePath);
                $pattern = '#^' . $pattern . '$#';

                if (preg_match($pattern, $requestUri, $matches)) {
                    array_shift($matches);
                    preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $routePath, $paramNames);
                    $paramNames = $paramNames[1];
                    $params = array_combine($paramNames, $matches);
                    $matchedRoute = $routeData;
                    break;
                }
            }
        }

        if ($matchedRoute === null) {
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
                                echo "<h1>405 - Méthode non autorisée</h1>";
                            }
                            exit();
                        }
                    }
                }
            }

            if (self::$notFoundHandler) {
                call_user_func(self::$notFoundHandler);
            } else {
                http_response_code(404);
                echo "<h1>404 - Page non trouvée</h1>";
            }
            exit();
        }

        // ✅ CORRECTION : Passer le conteneur au middleware
        foreach ($matchedRoute['middlewares'] as $middlewareName) {
            if (isset(self::$middlewares[$middlewareName])) {
                call_user_func(self::$middlewares[$middlewareName], self::$container);
            } else {
                throw new \RuntimeException("Middleware '{$middlewareName}' non défini.");
            }
        }

        if (self::$container === null) {
            throw new \RuntimeException("Le conteneur de dépendances n'a pas été défini pour le routeur.");
        }

        $controllerClass = $matchedRoute['handler'][0];
        $methodName = $matchedRoute['handler'][1];
        $controllerInstance = self::$container->get($controllerClass);
        call_user_func_array([$controllerInstance, $methodName], $params);
    }
}