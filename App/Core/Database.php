<?php
namespace App\Core;

use PDO;
use App\Core\Logger;
use PDOException;

class Database_Handler {
    private string $host;
    private string $schema;
    private string $username;
    private string $password;
    private array $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];
    private ?PDO $connection = null;
    private Logger $logger;

    /**
     * Initializing the database handler with the given environment variables.
     * @var string $_ENV["DB_HOST"] The database host.
     * @var string $_ENV["DB_SCHEMA"] The database schema.
     * @var string $_ENV["DB_USERNAME"] The database username.
     * @var string $_ENV["DB_PASSWORD"] The database password.
     */
    public function __construct()
    {
        $this->host = $_ENV["DB_HOST"];
        $this->schema = $_ENV["DB_SCHEMA"];
        $this->username = $_ENV["DB_USERNAME"];
        $this->password = $_ENV["DB_PASSWORD"];
        $this->setLogger(Logger::init());
        $this->connect();
    }

    private function getConnection(): ?PDO
    {
        return $this->connection;
    }

    private function setConnection(?PDO $connection): void
    {
        $this->connection = $connection;
    }

    private function getLogger(): Logger
    {
        return $this->logger;
    }

    private function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Establishing a connection to the database.
     * @return void
     * @throws PDOException If an error occurs while establishing the connection.
     */
    private function connect(): void
    {
        if (!is_null($this->getConnection())) {
            return;
        }
        try {
            $data_source_name = "mysql:host={$this->host};dbname={$this->schema}";
            $this->setConnection(
                new PDO(
                    $data_source_name,
                    $this->username,
                    $this->password,
                    $this->options
                )
            );
            $this->getLogger()->log("The database is connected.", Logger::INFO);
        } catch (PDOException $error) {
            $context = [
                "file" => $error->getFile(),
                "line" => $error->getLine(),
                "message" => $error->getMessage()
            ];
            $this->getLogger()->log($error->getMessage(), Logger::ERROR, $context);
        }
    }
}