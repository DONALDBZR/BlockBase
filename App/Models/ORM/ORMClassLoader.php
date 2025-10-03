<?php
namespace App\Models\ORM;

use ReflectionClass;
use ReflectionException;

class ORM_Class_Loader
{
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
     * @throws ReflectionException If the class does not exist or is not instantiable.
     */
    public function loadClass(string $class_name): void
    {
        if (strpos($class_name, __NAMESPACE__) !== 0) {
            return;
        }
        $directory = dirname(__DIR__, 2);
        $full_directory = "{$directory}/";
        $file = str_replace(
            "\\",
            "/",
            substr(
                $class_name,
                strlen(__NAMESPACE__) + 1
            )
        );
        $file_name = "{$full_directory}{$file}.php";
        if (!file_exists($file_name)) {
            throw new ReflectionException("The file is not found. - File: {$file_name} - Class: {$class_name}");
        }
        $reflection_class = new ReflectionClass($class_name);
        if (!$reflection_class->isInstantiable()) {
            return;
        }
        require_once $file_name;
    }
}
