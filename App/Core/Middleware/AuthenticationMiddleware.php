<?php
namespace App\Core\Middleware;

class Authentication
{
    /**
     * Checking if the user is authenticated.
     * @param string $method The HTTP method
     * @param string $path The request path
     * @return bool True if the user is logged in, false otherwise
     * @throws Unauthorized If the user is not logged in.
     */
    public static function check(string $method, string $path): bool
    {
        session_start();
        if (isset($_SESSION["user_id"])) {
            return true;
        }
        http_response_code(401);
        require_once __DIR__ . "/../Errors/Unauthorized.html";


        return false;
    }

    /**
     * Checking if the user is an admin.
     * @param string $method The HTTP method
     * @param string $path The request path
     * @return bool True if the user is an admin, false otherwise
     * @throws Forbidden If the user is not an admin
     */
    public static function checkAdmin(string $method, string $path): bool
    {
        session_start();
        if (isset($_SESSION["user_id"]) && isset($_SESSION["user_role"]) && $_SESSION["user_role"] === "admin") {
            return true;
        }
        http_response_code(403);
        require_once __DIR__ . "/../Errors/Forbidden.html";


        return false;
    }
}
