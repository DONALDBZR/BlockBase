<?php
namespace App\Controllers;

use App\Core\Errors\NotFoundException;
use App\Core\Logger;


abstract class Controller
{
    /**
     * It provides a convenient way to log messages with different severity levels and context information.
     * @var Logger
     */
    protected Logger $logger;
    /**
     * The data to be processed.
     * @var array
     */
    protected array $unprocessed_data;

    /**
     * Initializing the controller with an empty array for unprocessed data and a default logger.
     */
    public function __construct()
    {
        $this->setData([]);
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

    public function getData(): array
    {
        return $this->unprocessed_data;
    }

    public function setData(array $unprocessed_data): void
    {
        $this->unprocessed_data = $unprocessed_data;
    }

    /**
     * Checking if a view exists at the given path.
     * @param string $path The path to the view
     * @param array<string,string> $context The context in which the error occurred
     * @throws NotFoundException If the view does not exist
     * @return void
     */
    private function checkViewPath(string $path, array $context): void
    {
        if (file_exists($path)) {
            return;
        }
        $message = "The view is not found";
        $this->getLogger()->log($message, $this->getLogger()::ERROR, $context);
        throw new NotFoundException($message, 404);
    }

    /**
     * Rendering a view with optional data.
     * @param string $view The view name to render
     * @param array $data Optional data to pass to the view
     * @param string $layout The layout to use (default: app)
     * @return void
     * @throws NotFoundException If the view does not exist
     */
    protected function view(
        string $view,
        array $data = [],
        string $layout = "app"
    ): void
    {
        $context = [
            "View" => $view,
            "Data" => $data,
            "Layout" => $layout
        ];
        $this->setData(
            array_merge(
                $this->getData(),
                $data
            )
        );
        $view_path = $this->getViewPath($view);
        $this->checkViewPath($view_path, $context);
        ob_start();
        extract($this->getData());
        require_once $view_path;


        $content = ob_get_clean();
        $this->getData()["content"] = $content;
        $this->renderLayout($layout);
    }

    /**
     * Checking if the given path exists and throws a `NotFoundException` if it doesn't.
     * @param string $path The path to check
     * @param array<string,string> $context The context to pass to the logger
     * @throws NotFoundException If the path does not exist
     * @return void
     */
    private function checkLayoutPath(string $path, array $context): void
    {
        if (file_exists($path)) {
            return;
        }
        $message = "The layout is not found";
        $this->getLogger()->log($message, $this->getLogger()::ERROR, $context);
        throw new NotFoundException($message, 404);
    }

    /**
     * Rendering a layout with the captured content.
     * @param string $layout The layout name
     * @return void
     * @throws NotFoundException If the layout does not exist
     */
    protected function renderLayout(string $layout): void
    {
        $context = [
            "Layout" => $layout
        ];
        $path = $this->getLayoutPath($layout);
        $this->checkLayoutPath($path, $context);
        extract($this->getData());
        require_once $path;
    }

    /**
     * Getting the full path to a layout file.
     * @param string $layout The layout name
     * @return string The full path to the layout file
     */
    protected function getLayoutPath(string $layout): string
    {
        return __DIR__ . "/../../App/Views/layouts/{$layout}.php";
    }

    /**
     * Getting the full path to a view file.
     * @param string $view The view name
     * @return string The full path to the view file
     */
    protected function getViewPath(string $view): string
    {
        $view = str_replace(".", "/", $view);
        return __DIR__ . "/../../App/Views/{$view}.php";
    }

    /**
     * Returning JSON response.
     * @param array<string,mixed> $data Data to encode as JSON
     * @param int $status_code HTTP status code
     * @return void
     */
    protected function json(
        array $data,
        int $status_code = 200
    ): void
    {
        http_response_code($status_code);
        header("Content-Type: application/json");
        echo json_encode($data);
    }

    /**
     * Redirecting to a URL.
     * @param string $uniform_resource_locator The URL to redirect to
     * @param int $status_code HTTP status code for redirect
     * @return void
     */
    protected function redirect(
        string $uniform_resource_locator,
        int $status_code = 302
    ): void
    {
        http_response_code($status_code);
        header("Location: {$uniform_resource_locator}");
        exit;
    }

    /**
     * Retrieving the request data (GET, POST, PUT, DELETE).
     * @return array<string,mixed> The request data
     */
    protected function getRequestData(): array
    {
        $method = $_SERVER["REQUEST_METHOD"] ?? "GET";
        switch ($method) {
            case "GET":
                return $_GET;
            case "POST":
                return $_POST;
            case "PUT":
            case "DELETE":
                parse_str(file_get_contents("php://input"), $data);
                return $data;
            default:
                return [];
        }
    }

    /**
     * Getting a specific request parameter.
     * @param string $key The parameter key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed The parameter value or default
     */
    protected function getParam(
        string $key,
        mixed $default = null
    ): mixed
    {
        $data = $this->getRequestData();
        return $data[$key] ?? $default;
    }
}
