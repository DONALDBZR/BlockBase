<?php
use App\Core\Router;
use App\Core\Logger;


/**
 * Registering all routes with the given router instance.
 * 
 * This function does the following:
 * 1. It registers a set of routes that demonstrate the features of the router.
 * 2. It registers global middleware that runs for all routes.
 * 3. It registers a route for the health check API endpoint.
 * 4. It registers a route for the admin dashboard page that is protected by middleware.
 * 5. It registers a route for the user profile page that demonstrates parameter support.
 * 6. It registers a route for the contact form page that handles form submissions.
 * @param Router $router The router instance to register routes with.
 * @return void
 */
function registerRoutes(Router $router): void
{
    $router->get("/", "Home@index");
    $router->get("/About", "Home@about");
    $router->get("/Contact", "Home@contact");
    $router->get("/User/{id}", function() {
        echo "
        <div class='alert alert-info'>
            <h2>User Profile</h2>
            <p>This route demonstrates parameter support.</p>
            <p>In a real application, the user ID would be extracted from the URL.</p>
            <a href='/' class='btn btn-primary'>Return to Home</a>
        </div>
        ";
    });
    $router->post("/Contact", function() {
        $data = $_POST;
        echo "Thank you for your message!";
    });
    $router->get("/Admin", function() {
        echo "
        <div class='alert alert-info'>
            <h2>Admin Dashboard</h2>
            <p>Welcome to the admin area! This is a protected route.</p>
            <p>In a real application, this would contain:</p>
            <ul>
                <li>User management</li>
                <li>Content management</li>
                <li>System settings</li>
                <li>Analytics and reports</li>
            </ul>
            <a href='/' class='btn btn-primary'>Return to Home</a>
        </div>
        ";
    }, [
        [\App\Core\Middleware\AuthenticationMiddleware::class, "check"]
    ]);
    $router->get('/API/Health', function() {
        header("Content-Type: application/json");
        http_response_code(200);
        echo json_encode(
            [
                "status" => "ok",
                "timestamp" => date("c")
            ]
        );
    });
}

/**
 * Registering global middleware that runs for all routes.
 * 
 * This function does the following:
 * 1. The first one sets the following security headers.
 * 2. The second one logs a message for each request with the method and path.
 * @param Router $router The router instance.
 * @return void
 */
function registerGlobalMiddleware(Router $router): void
{
    $router->middleware(function(string $method, string $path) {
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: DENY");
        header("X-XSS-Protection: 1; mode=block");
        return true;
    });
    $router->middleware(function(string $method, string $path) {
        $context = [
            "Method" => $method,
            "Path" => $path
        ];
        $logger = Logger::init();
        $logger->log("Processing request.", \App\Core\Logger::INFO, $context);
        return true;
    });
}
