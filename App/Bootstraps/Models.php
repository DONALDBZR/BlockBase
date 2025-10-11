<?php
spl_autoload_register(
    function(string $class) {
        if (strpos($class, "App\\Models\\") !== false) {
            $names = explode("\\", $class);
            $class = $names[array_key_last($names)];
        }
        $class_path = $_SERVER["DOCUMENT_ROOT"] . "/App/Models/{$class}.php";
        if (file_exists($class_path)) {
            return require_once $class_path;
        }
    }
);