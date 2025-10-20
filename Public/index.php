<?php
require_once __DIR__ . "/../vendor/autoload.php";


/**
 * Loading environment variables from a file.
 * @param string $path The path to the file containing the environment variables.
 * @return void
 */
function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || str_starts_with($line, "#")) {
            continue;
        }
        [$key, $value] = array(
            "trim",
            explode(
                "=",
                $line,
                2
            )
        );
        $value = trim($value, "\"'");
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

$environment_path = __DIR__ . "/../.env";
$is_testing = (getenv("APP_ENV") === "testing" || getenv("TEST_ENV") === "1" || (php_sapi_name() === "cli" && file_exists(__DIR__ . "/../.env.testing")));
if ($is_testing) {
    $environment_path = __DIR__ . "/../.env.testing";
}
loadEnv($environment_path);
use App\Core\Router;


require_once __DIR__ . "/../App/Core/routes.php";


$router = new Router();

registerGlobalMiddleware($router);
registerRoutes($router);
$router->dispatch(strval($_SERVER["REQUEST_URI"]), strval($_SERVER["REQUEST_METHOD"]));
