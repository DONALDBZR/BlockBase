<?php
namespace App\Models\ORM;

use App\Core\Errors\ClassNotFoundException;


class ORM_Class_Loader
{
    private string $base_directory;

    /**
     * Initializing the `ORM_Class_Loader` with the given base directory.
     * @param string $base_directory The base directory to search for classes in.
     */
    public function __construct(
        string $base_directory = __DIR__ . "/../../"
    )
    {
        $directory = rtrim($base_directory, "/");
        $this->setBaseDirectory("{$directory}/");
    }

    public function getBaseDirectory(): string
    {
        return $this->base_directory;
    }

    public function setBaseDirectory(string $base_directory): void
    {
        $this->base_directory = $base_directory;
    }

    /**
     * Registering this class loader with the SPL autoloader.
     * @return void
     */
    public function register(): void
    {
        spl_autoload_register(
            [
                $this,
                "loadClass"
            ],
            true,
            true
        );
    }

    /**
     * Attempting to load a class from the ORM namespace.
     * @param string $class_name The name of the class to load.
     * @return void
     * @throws ClassNotFoundException If the class does not exist or is not instantiable.
     */
    public function loadClass(string $class_name): void
    {
        if (strpos($class_name, __NAMESPACE__) !== 0) {
            return;
        }
        $relative_path = str_replace(
            "\\",
            "/",
            substr(
                $class_name,
                strlen(__NAMESPACE__) + 1
            )
        );
        $file_path = "{$this->getBaseDirectory()}{$relative_path}.php";
        if (!file_exists($file_path)) {
            throw new ClassNotFoundException("The file is not found. - File: {$file_path} - Class: {$class_name}");
        }
        require_once $file_path;
    }
}
