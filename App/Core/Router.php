<?php
namespace App\Core;

use App\Core\Logger;
use App\Core\Errors\ClassNotFoundException;
use App\Core\Errors\NotFoundException;


class Router {
    /**
     * The routes array holds the registered routes and their corresponding handlers.
     * @var array<string,array<string,array{handler:callable|string,parameters:array<string,string>,middleware:array<int,callable>}>>
     */
    private array $routes;
    /**
     * Global middleware that runs for all routes.
     * @var array<int,callable>
     */
    private array $global_middleware;
    /**
     * It provides a convenient way to log messages with different severity levels and context information.
     * @var Logger
     */
    private Logger $logger;

    /**
     * Initializing the router with the default logger.
     */
    public function __construct()
    {
        $this->setGlobalMiddleware([]);
        $this->setRoutes([]);
        $this->setLogger(Logger::init());
    }

    public function getLogger(): Logger
    {
        return $this->logger;
    }

    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    public function getGlobalMiddleware(): array
    {
        return $this->global_middleware;
    }

    public function setGlobalMiddleware(array $global_middleware): void
    {
        $this->global_middleware = $global_middleware;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function setRoutes(array $routes): void
    {
        $this->routes = $routes;
    }

    /**
     * Registering a route for the GET HTTP method.
     * @param string $path The route path.
     * @param callable|string $handler The callback function or controller@method string.
     * @param array<int,callable> $middleware Optional middleware for this route.
     * @return void
     */
    public function get(
        string $path,
        mixed $handler,
        array $middleware = []
    ): void
    {
        $this->add(
            "GET",
            $path,
            $handler,
            $middleware
        );
    }

    /**
     * Registering a route for the POST HTTP method.
     * @param string $path The route path.
     * @param callable|string $handler The callback function or controller@method string.
     * @param array<int,callable> $middleware Optional middleware for this route.
     * @return void
     */
    public function post(
        string $path,
        mixed $handler,
        array $middleware = []
    ): void
    {
        $this->add(
            "POST",
            $path,
            $handler,
            $middleware
        );
    }

    /**
     * Registering a route for the PUT HTTP method.
     * @param string $path The route path.
     * @param callable|string $handler The callback function or controller@method string.
     * @param array<int,callable> $middleware Optional middleware for this route.
     * @return void
     */
    public function put(
        string $path,
        mixed $handler,
        array $middleware = []
    ): void
    {
        $this->add(
            "PUT",
            $path,
            $handler,
            $middleware
        );
    }

    /**
     * Registering a route for the DELETE HTTP method.
     * @param string $path The route path.
     * @param callable|string $handler The callback function or controller@method string.
     * @param array<int,callable> $middleware Optional middleware for this route.
     * @return void
     */
    public function delete(
        string $path,
        mixed $handler,
        array $middleware = []
    ): void
    {
        $this->add(
            "DELETE",
            $path,
            $handler,
            $middleware
        );
    }

    /**
     * Registering a route for any HTTP method.
     * @param string $path The route path.
     * @param callable|string $handler The callback function or controller@method string.
     * @param array<int,callable> $middleware Optional middleware for this route.
     * @return void
     */
    public function any(
        string $path,
        mixed $handler,
        array $middleware = []
    ): void
    {
        $this->add(
            "ANY",
            $path,
            $handler,
            $middleware
        );
    }

    /**
     * Adding global middleware that runs for all routes.
     * @param callable $middleware The middleware function.
     * @return void
     */
    public function middleware(callable $middleware): void
    {
        $this->getGlobalMiddleware()[] = $middleware;
    }

    /**
     * Registering a route for a specific HTTP method and path.
     * @param string $method The HTTP method.
     * @param string $path The route path.
     * @param callable|string $handler The callback function or controller@method string.
     * @param array<int,callable> $middleware Optional middleware for this route.
     * @return void
     */
    private function add(
        string $method,
        string $path,
        mixed $handler,
        array $middleware = []
    ): void
    {
        $method = strtoupper($method);
        $parameters = $this->parseRouteParameters($path);
        $this->getHandler($handler);
        $this->getRoutes()[$method][$path] = [
            "handler" => $handler,
            "parameters" => $parameters,
            "middleware" => $middleware
        ];
    }

    /**
     * Ensuring that the handler is a callable function.
     * 
     * this function does the following:
     * 1. If the handler is a string that contains "@", it is parsed into a callable using parseControllerHandler.
     * 2. If the handler is already a callable function, it is left unchanged.
     * @param callable|string &$handler The handler to be processed.
     * @return void
     */
    private function getHandler(mixed &$handler): void
    {
        if (!is_string($handler) || str_contains($handler, "@")) {
            return;
        }
        $this->parseControllerHandler($handler);
    }

    /**
     * Parsing route parameters from path.
     * 
     * This function does the following:
     * 1. It uses a regular expression to match any occurrences of curly braces containing one or more characters that are not curly braces.
     * 2. It returns an array of parameter names.
     * @param string $path The route path.
     * @return array<string> The parameter names.
     */
    private function parseRouteParameters(string $path): array
    {
        preg_match_all('/\{([^}]+)\}/', $path, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Parsing a controller@method string to a callable handler.
     * 
     * This function does the following:
     * 1. It uses explode to split the string into a controller class name and a method name.
     * 2. It checks if the controller class name exists and if the method exists in the controller class.
     * 3. It returns a callable handler that creates an instance of the controller class and calls the method.
     * @param string &$handler The controller@method string.
     * @return void
     * @throws ClassNotFoundException If the controller class name is not found.
     * @throws NotFoundException If the method is not found in the controller class.
     */
    private function parseControllerHandler(string &$handler): void
    {
        [$controller, $method] = explode("@", $handler, 2);
        $handler = function() use ($controller, $method) {
            $controller_class = "App\\Controllers\\{$controller}";
            if (!class_exists($controller_class)) {
                throw new ClassNotFoundException("Controller class not found: {$controller_class}", 503);
            }
            $controller_instance = new $controller_class();
            if (!method_exists($controller_instance, $method)) {
                throw new NotFoundException("Method not found: {$method} in {$controller_class}", 503);
            }
            return $controller_instance->$method();
        };
    }

    /**
     * Dispatching the matched route's handler function.
     * @param string $uniform_resource_identifier The URL path of the request.
     * @param string $request_method The HTTP method of the request.
     * @return mixed The result of the handler function, or null if the route is not found.
     */
    public function dispatch(string $uniform_resource_identifier, string $request_method): mixed
    {
        $request_method = strtoupper($request_method);
        $path = parse_url($uniform_resource_identifier, PHP_URL_PATH) ?? "/";
        $this->getLogger()->log("Dispatching route: {$request_method} {$path}", Logger::INFO);
        $route = $this->findRoute($request_method, $path);
        if (!$route) {
            $route = $this->findRouteWithParameters($request_method, $path);
        }
        if (!$route) {
            $this->getLogger()->log("Route not found: {$request_method} {$path}", Logger::WARNING);
            $this->handle404();
            return null;
        }
        foreach ($this->getGlobalMiddleware() as $middleware) {
            $response = call_user_func($middleware, $request_method, $path);
            if ($response !== false) {
                continue;
            }
            return null;
        }
        foreach ($route["middleware"] as $middleware) {
            $result = call_user_func($middleware, $request_method, $path);
            if ($result !== true) {
                continue;
            }
            return null;
        }
        try {
            return call_user_func($route["handler"]);
        } catch (\Exception $error) {
            $context = [
                "request_method" => $request_method,
                "path" => $path,
                "Line" => $error->getLine(),
                "Error" => $error->getMessage(),
                "File" => $error->getFile()
            ];
            $this->getLogger()->log("The request cannot be dispatched for a certain reason.", Logger::ERROR, $context);
            $this->handle500($error);
            return null;
        }
    }

    /**
     * Finding a route with exact path match.
     * 
     * This function does the following:
     * 1. It checks if there is a route in the routes array with the given HTTP method and path and if so, it returns the route data.
     * 2. If not, it returns null.
     * @param string $method The HTTP method.
     * @param string $path The request path.
     * @return array{handler:callable|string,parameters:array<string,string>,middleware:array<int,callable>}|null The route data or null if not found.
     */
    private function findRoute(string $method, string $path): ?array
    {
        if (isset($this->getRoutes()[$method][$path])) {
            return $this->getRoutes()[$method][$path];
        }
        if (isset($this->getRoutes()['ANY'][$path])) {
            return $this->getRoutes()['ANY'][$path];
        }
        return null;
    }

    /**
     * Finding a route with parameter matching.
     * 
     * This function does the following:
     * 1. It merges the routes for the given HTTP method and the 'ANY' method.
     * 2. It loops through the merged routes and checks if the route path matches the request path using the matchesRoute method.
     * 3. If a match is found, it returns the route data.
     * 4. If no match is found, it returns null.
     * @param string $method The HTTP method.
     * @param string $path The request path.
     * @return array{handler:callable|string,parameters:array<string,string>,middleware:array<int,callable>}|null The route data or null if not found.
     */
    private function findRouteWithParameters(string $method, string $path): ?array
    {
        $method_routes = $this->getRoutes()[$method] ?? [];
        $any_routes = $this->getRoutes()["ANY"] ?? [];
        $all_routes = array_merge($method_routes, $any_routes);
        foreach ($all_routes as $route_path => $data) {
            if (!$this->matchesRoute($route_path, $path)) {
                continue;
            }
            return $data;
        }
        return null;
    }

    /**
     * Checking if a route path matches the request path.
     * 
     * This function does the following:
     * 1. It replaces any occurrences of curly braces containing one or more characters that are not curly braces with a regular expression pattern that matches one or more characters that are not a forward slash.
     * 2. It adds the start and end of the string anchors to the pattern.
     * 3. It returns true if the request path matches the route path pattern and false otherwise.
     * @param string $route The route path pattern.
     * @param string $request The request path.
     * @return bool True if the route matches.
     */
    private function matchesRoute(string $route, string $path): bool
    {
        $pattern = preg_replace("/\{[^}]+\}/", "([^/]+)", $route);
        $pattern = "#^{$pattern}$#";
        return preg_match($pattern, $path);
    }

    /**
     * Handling 404 Not Found error.
     * 
     * This function does the following:
     * 1. It sets the HTTP status code to 404.
     * 2. It includes the Not_Found.html file from the Errors directory.
     * 3. It returns to prevent further execution of the current request.
     * @return void
     */
    private function handle404(): void
    {
        http_response_code(404);
        require_once __DIR__ . "./Errors/Not_Found.html";


        return;
    }

    /**
     * Handling 500 Internal Server Error.
     * 
     * This function does the following:
     * 1. It sets the HTTP status code to 500.
     * 2. It includes the Internal_Server_Error.html file from the Errors directory.
     * 3. It returns to prevent further execution of the current request.
     * @param \Exception $error The exception that caused the error.
     * @return void
     */
    private function handle500(\Exception $error): void
    {
        http_response_code(500);
        require_once __DIR__ . "./Errors/Internal_Server_Error.html";


        return;
    }
}
