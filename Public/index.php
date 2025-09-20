<?php
require_once __DIR__ . "/../vendor/autoload.php";

// Loading environment variables
if (file_exists(__DIR__ . "/../.env")) {
    $environment = parse_ini_file(__DIR__ . "/../.env");
    foreach ($environment as $key => $value) {
        $_ENV[$key] = $value;
    }
}

use App\Core\Router;
$router = new Router();
$router->get("/", function () {
    echo "Welcome to your PHP CMS 🚀";
});
$router->get("/about", function () {
    echo "This is the About Page.";
});
$router->dispatch($_SERVER["REQUEST_URI"], $_SERVER["REQUEST_METHOD"]);
