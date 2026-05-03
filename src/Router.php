<?php

namespace ScreepsOnline;

/**
 * Simple Router
 *
 * Handles URL routing with middleware support
 */
class Router
{
    private array $routes = [];
    private array $middleware = [];

    /**
     * Add a GET route
     *
     * @param string $path
     * @param string|callable $handler Format: "ControllerName@method"
     * @return Route
     */
    public function get(string $path, $handler): Route
    {
        return $this->addRoute('GET', $path, $handler);
    }

    /**
     * Add a POST route
     *
     * @param string $path
     * @param string|callable $handler
     * @return Route
     */
    public function post(string $path, $handler): Route
    {
        return $this->addRoute('POST', $path, $handler);
    }

    /**
     * Add a route
     *
     * @param string $method
     * @param string $path
     * @param string|callable $handler
     * @return Route
     */
    private function addRoute(string $method, string $path, $handler): Route
    {
        $route = new Route($method, $path, $handler);
        $this->routes[] = $route;
        return $route;
    }

    /**
     * Register global middleware
     *
     * @param string $middlewareClass
     */
    public function addGlobalMiddleware(string $middlewareClass): void
    {
        $this->middleware[] = $middlewareClass;
    }

    /**
     * Dispatch the current request
     *
     * @return mixed
     */
    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Find matching route
        foreach ($this->routes as $route) {
            if ($route->matches($method, $path)) {
                // Run global middleware
                foreach ($this->middleware as $middlewareClass) {
                    $middleware = new $middlewareClass();
                    if (!$middleware->handle()) {
                        return;
                    }
                }

                // Run route-specific middleware
                foreach ($route->getMiddleware() as $middlewareClass) {
                    $middleware = new $middlewareClass();
                    if (!$middleware->handle()) {
                        return;
                    }
                }

                // Execute handler
                return $route->execute();
            }
        }

        // 404 Not Found
        $this->handleNotFound();
    }

    /**
     * Handle 404 errors
     */
    private function handleNotFound(): void
    {
        http_response_code(404);
        echo "404 - Page Not Found";
    }
}

/**
 * Route Class
 *
 * Represents a single route
 */
class Route
{
    private string $method;
    private string $path;
    private $handler;
    private array $middleware = [];

    public function __construct(string $method, string $path, $handler)
    {
        $this->method = $method;
        $this->path = $path;
        $this->handler = $handler;
    }

    /**
     * Add middleware to this route
     *
     * @param string|array $middleware
     * @return Route
     */
    public function middleware($middleware): Route
    {
        $middlewares = is_array($middleware) ? $middleware : [$middleware];
        $this->middleware = array_merge($this->middleware, $middlewares);
        return $this;
    }

    /**
     * Get route middleware
     *
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Check if route matches the request
     *
     * @param string $method
     * @param string $path
     * @return bool
     */
    public function matches(string $method, string $path): bool
    {
        return $this->method === $method && $this->path === $path;
    }

    /**
     * Execute the route handler
     *
     * @return mixed
     */
    public function execute()
    {
        if (is_callable($this->handler)) {
            return call_user_func($this->handler);
        }

        if (is_string($this->handler) && strpos($this->handler, '@') !== false) {
            [$controller, $method] = explode('@', $this->handler);
            $controllerClass = "ScreepsOnline\\Controllers\\{$controller}";

            if (!class_exists($controllerClass)) {
                throw new \RuntimeException("Controller {$controllerClass} not found");
            }

            $instance = new $controllerClass();

            if (!method_exists($instance, $method)) {
                throw new \RuntimeException("Method {$method} not found in {$controllerClass}");
            }

            return $instance->$method();
        }

        throw new \RuntimeException("Invalid route handler");
    }
}
