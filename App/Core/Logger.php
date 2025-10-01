<?php
namespace App\Core;

use RuntimeException;

class Logger {
    private static string $directory;
    private static string $file;

    private static function getDirectory(): string
    {
        return self::$directory;
    }

    private static function setDirectory(string $directory): void
    {
        self::$directory = $directory;
    }

    private static function getFile(): string
    {
        return self::$file;
    }

    private static function setFile(string $file): void
    {
        self::$file = $file;
    }

    public static function log(string $message): void
    {
        $file_handler = fopen(self::getFile(), "a");
        if (!$file_handler) {
            throw new RuntimeException("The file does not exist.");
        }
        $data = "[{$severity}] [{$date}] [{$scope}] [{$client}] [{$process_id}] [{$thread_id}] {$message}\n";
        fwrite($file_handler, $data);
        fclose($file_handler);
    }
}