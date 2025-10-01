<?php
namespace App\Core;

use PDO;
use App\Core\Logger;

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
    private PDO $connection;

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
    }

    private function getConnection(): PDO
    {
        return $this->connection;
    }

    private function setConnection(PDO $connection): void
    {
        $this->connection = $connection;
    }
}