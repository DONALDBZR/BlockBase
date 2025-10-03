<?php
namespace App\Models\ORM;

use Composer\Autoload\ClassLoader;
use ReflectionClass;


class ORM_Class_Loader extends ClassLoader
{
    /**
     * Registering this class loader with the SPL autoloader.
     * @return void
     */
    public function enqueue(): void
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

    public function loadClass(string $className)
    {
        if (strpos($className, __NAMESPACE__) === 0) {
            $fileName = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, substr($className, strlen(__NAMESPACE__) + 1)) . '.php';

            if (file_exists($fileName)) {
                require $fileName;

                $reflectionClass = new ReflectionClass($className);

                if ($reflectionClass->isInstantiable()) {
                    return $reflectionClass->newInstance();
                }
            }
        }

        return null;
    }
}
