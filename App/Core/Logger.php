<?php
namespace App\Core;

use DateTime;
use RuntimeException;
use UnexpectedValueException;

class Logger {
    private static string $directory;
    private static string $file;
    public const INFO = "INFO";
    public const DEBUG = "DEBUG";
    public const WARNING = "WARNING";
    public const ERROR = "ERROR";
    private static array $allowed_severities = [
        self::INFO,
        self::DEBUG,
        self::WARNING,
        self::ERROR
    ];

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

    /**
     * Getting the scope of the logger call.
     * @return string
     */
    private static function getScope(): string
    {
        $backtrace = debug_backtrace(3);
        $class = $backtrace[0]["class"] ?? "";
        $function = $backtrace[0]["function"] ?? "";
        return "{$class}::{$function}";
    }

    public static function log(
        string $message,
        ?string $severity = null
    ): void
    {
        if ($severity === null) {
            $severity = self::ERROR;
        }
        $file_handler = fopen(self::getFile(), "a");
        if (!$file_handler) {
            throw new RuntimeException("The file does not exist.");
        }
        $is_allowed_severity = in_array($severity, self::$allowed_severities);
        if (!$is_allowed_severity) {
            throw new UnexpectedValueException("Invalid severity.");
        }
        $datetime = new DateTime();
        $date = $datetime->format("Y-m-d H:i:s");
        $scope = self::getScope();
        $data = "[{$severity}] [{$date}] [{$scope}] [{$client}] [{$process_id}] [{$thread_id}] {$message}\n";
        fwrite($file_handler, $data);
        fclose($file_handler);
    }

    private static function rotateLog(): void
    {
        $date = new DateTime();
        $name = $date->format("Ymd");
        rename(self::getFile(), $name);
        touch(self::getFile());
    }
}