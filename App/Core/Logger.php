<?php
namespace App\Core;

use DateTime;
use RuntimeException;
use UnexpectedValueException;

class Logger {
    private static ?self $instance = null;
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

    /**
     * Initializing the `Logger` instance.
     * 
     * This constructor functions as follows:
     * 1. Sets the directory.
     * 2. Sets the file.
     * 3. Rotates the log file if it already exists.
     */
    private function __construct() {
        self::setDirectory(__DIR__ . "/Storage/Logs/");
        $date = date("Ymd");
        self::setFile("{$date}.log");
        self::rotateLog();
    }

    /**
     * Initializing the Logger instance with the given file and directory.
     * @param string|null $file The file to write logs to. If null, uses the default file.
     * @param string|null $directory The directory to write logs to. If null, uses the default directory.
     * @return self The initialized Logger instance.
     */
    public static function init(
        ?string $file = null,
        ?string $directory = null
    ): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new Logger($file, $directory);
        }
        return self::$instance;
    }

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

    /**
     * Writing a log message to a file.
     * @param string $message The message to log.
     * @param ?string $severity The severity of the log message. Defaults to ERROR.
     * @param array $context Additional context to log. Defaults to an empty array.
     * @throws RuntimeException If the log file does not exist.
     * @throws UnexpectedValueException If the severity is not one of the allowed values.
     */
    public static function log(
        string $message,
        ?string $severity = null,
        array $context = []
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
        $process_id = getmypid();
        $thread_id = (isset($_SERVER["HTTP_X_REQUESTED_UID"])) ? intval($_SERVER["HTTP_X_REQUESTED_UID"]) : intval(get_current_user());
        $data = "[{$severity}] [{$date}] [{$scope}] [{$_SERVER['REMOTE_ADDR']}] [{$process_id}] [{$thread_id}] {$message}";
        if (!empty($context)) {
            foreach ($context as $key => $value) {
                $data .= " - {$key}: {$value}";
            }
        }
        $data .= "\n";
        fwrite($file_handler, $data);
        fclose($file_handler);
    }

    private static function rotateLog(): void
    {
        $date = new DateTime();
        $name = $date->format("Ymd");
        $file = self::getFile();
        rename($file, "{$name}.log");
        touch($file);
    }
}