<?php
require_once __DIR__ . "/../vendor/autoload.php";


$environment_path = __DIR__ . "/../.env";
$is_testing = (getenv("APP_ENV") === "testing" || getenv("TEST_ENV") === "1" || (php_sapi_name() === "cli" && file_exists(__DIR__ . "/../.env.testing")));
if ($is_testing) {
    $environment_path = __DIR__ . "/../.env.testing";
}
if (file_exists($environment_path)) {
    $environment = parse_ini_file($environment_path);
    foreach ($environment as $key => $value) {
        $_ENV[$key] = $value;
        putenv("{$key}={$value}");
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
