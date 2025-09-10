<?php

namespace App\Core;

class Router {
    /**
     * The routes array holds the registered routes and their corresponding callbacks.
     * @var array<string,callable>
     */
    private array $routes = [];

    /**
     * Registering a route for the GET HTTP method.
     * @param string $path The route path.
     * @param callable $callback The callback function to call when the route is matched.
     * @return void
     */
    public function get(string $path, callable $callback): void
    {
        $this->add("GET", $path, $callback);
    }

    /**
     * Registering a route for the POST HTTP method.
     * @param string $path The route path.
     * @param callable $callback The callback function to call when the route is matched.
     * @return void
     */
    public function post(string $path, callable $callback): void
    {
        $this->add("POST", $path, $callback);
    }

    /**
     * Registering a route for the PUT HTTP method.
     * @param string $path The route path.
     * @param callable $callback The callback function to call when the route is matched.
     * @return void
     */
    public function put(string $path, callable $callback): void
    {
        $this->add("PUT", $path, $callback);
    }

    /**
     * Registering a route for the DELETE HTTP method.
     * @param string $path The route path.
     * @param callable $callback The callback function to call when the route is matched.
     * @return void
     */
    public function delete(string $path, callable $callback): void
    {
        $this->add("DELETE", $path, $callback);
    }

    /**
     * Registering a route for a specific HTTP method and path.
     * @param string $method The HTTP method.
     * @param string $path The route path.
     * @param callable $callback The callback function to call when the route is matched.
     * @return void
     */
    private function add(string $method, string $path, callable $callback): void
    {
        $method = strtoupper($method);
        $this->routes[$method][$path] = $callback;
    }

    /**
     * Dispatching the matched route's callback function.
     * @param string $uniform_resource_identifier The URL path of the request.
     * @param string $request_method The HTTP method of the request.
     * @return mixed The result of the callback function, or null if the route is not found or the callback is invalid.
     * @throws \Exception If the callback is invalid.
     */
    public function dispatch(string $uniform_resource_identifier, string $request_method): mixed
    {
        $request_method = strtoupper($request_method);
        $path = parse_url($uniform_resource_identifier, PHP_URL_PATH) ?? "/";
        if (!isset($this->routes[$request_method][$path])) {
            http_response_code(404);
            echo "404 Not Found";
            return null;
        }
        $callback = $this->routes[$request_method][$path];
        if (!is_callable($callback)) {
            http_response_code(500);
            echo "500 Internal Server Error";
            return null;
        }
        return call_user_func($callback);
    }
}
