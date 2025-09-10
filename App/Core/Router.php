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
}
