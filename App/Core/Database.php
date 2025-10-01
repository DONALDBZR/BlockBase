<?php
namespace App\Core;

use PDO;
use App\Core\Logger;
use InvalidArgumentException;
use PDOException;
use PDOStatement;

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
    private ?PDOStatement $cursor = null;

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

    private function setCursor(PDOStatement $cursor): void
    {
        $this->cursor = $cursor;
    }

    private function getCursor(): ?PDOStatement
    {
        return $this->cursor;
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

    /**
     * Setting the cursor for the database query.
     * @param string $query The database query.
     * @return void
     * @throws PDOException If an error occurs while setting the cursor.
     */
    private function initCursor(string $query): void
    {
        if (!is_null($this->getCursor())) {
            return;
        }
        try {
            $this->connect();
            $this->setCursor($this->getConnection()->prepare($query));
            $this->getLogger()->log("The cursor is initialized.", Logger::INFO);
        } catch (PDOException $error) {
            throw new PDOException("The cursor cannot be initialized. - File: {$error->getFile()} - Line: {$error->getLine()} - Error: {$error->getMessage()}", 503);
        }
    }

    /**
     * Asserting that the given value is of a valid data type.
     * @param string $key The key of the parameter.
     * @param mixed $value The value of the parameter.
     * @return void
     * @throws InvalidArgumentException If the value is not of a valid data type.
     */
    private function assertDataType(string $key, mixed $value): void
    {
        $is_allowed = (is_int($value) || is_float($value) || is_string($value) || is_null($value) || is_resource($value));
        if ($is_allowed) {
            return;
        }
        $value = print_r($value, true);
        throw new InvalidArgumentException("This data type is not allowed in this database. - Key: {$key} - Value: {$value}", 503);
    }

    /**
     * Binding an integer parameter to the database query.
     * @param string $key The key of the parameter.
     * @param mixed $value The value of the parameter.
     * @return void
     */
    private function bindInt(string $key, mixed $value): void
    {
        if (!is_int($value)) {
            return;
        }
        $this->getCursor()->bindValue(":{$key}", $value, PDO::PARAM_INT);
        $this->getLogger()->log("The parameter is bound.", Logger::INFO);
    }

    /**
     * Binding a float parameter to the database query.
     * @param string $key The key of the parameter.
     * @param mixed $value The value of the parameter.
     * @return void
     */
    private function bindFloat(string $key, mixed $value): void
    {
        if (!is_float($value)) {
            return;
        }
        $this->getCursor()->bindValue(":{$key}", $value, PDO::PARAM_STR);
        $this->getLogger()->log("The parameter is bound.", Logger::INFO);
    }

    /**
     * Binding a string parameter to the database query.
     * @param string $key The key of the parameter.
     * @param mixed $value The value of the parameter.
     * @return void
     */
    private function bindString(string $key, mixed $value): void
    {
        if (!is_string($value)) {
            return;
        }
        $this->getCursor()->bindValue(":{$key}", $value, PDO::PARAM_STR);
        $this->getLogger()->log("The parameter is bound.", Logger::INFO);
    }

    /**
     * Binding a null parameter to the database query.
     * @param string $key The key of the parameter.
     * @param mixed $value The value of the parameter.
     * @return void
     */
    private function bindNull(string $key, mixed $value): void
    {
        if (!is_null($value)) {
            return;
        }
        $this->getCursor()->bindValue(":{$key}", $value, PDO::PARAM_NULL);
        $this->getLogger()->log("The parameter is bound.", Logger::INFO);
    }

    /**
     * Binding a blob parameter to the database query.
     * @param string $key The key of the parameter.
     * @param mixed $value The value of the parameter. Must be a resource.
     * @return void
     */
    private function bindBlob(string $key, mixed $value): void
    {
        if (!is_resource($value)) {
            return;
        }
        $this->getCursor()->bindValue(":{$key}", $value, PDO::PARAM_LOB);
        $this->getLogger()->log("The parameter is bound.", Logger::INFO);
    }

    /**
     * Binding a parameter to the database query.
     * @param string $key The key of the parameter.
     * @param mixed $value The value of the parameter.
     * @return void
     * @throws PDOException If an error occurs while binding the parameter.
     * @throws InvalidArgumentException If the value is not of a valid data type.
     */
    private function bindParameter(string $key, mixed $value): void
    {
        try {
            $this->assertDataType($key, $value);
            $this->bindInt($key, $value);
            $this->bindFloat($key, $value);
            $this->bindString($key, $value);
            $this->bindNull($key, $value);
            $this->bindBlob($key, $value);
        } catch (PDOException $error) {
            throw new PDOException("The parameter cannot be bound. - File: {$error->getFile()} - Line: {$error->getLine()} - Error: {$error->getMessage()}", 503);
        } catch (InvalidArgumentException $error) {
            throw new PDOException("The parameter cannot be bound. - File: {$error->getFile()} - Line: {$error->getLine()} - Error: {$error->getMessage()}", 503);
        }
    }

    /**
     * Binding parameters to the database query.
     * @param array<string,mixed> $parameters The parameters to bind. Key is the parameter key, value is the parameter value.
     * @return void
     * @throws PDOException If an error occurs while binding the parameter.
     * @throws InvalidArgumentException If the value is not of a valid data type.
     */
    private function bindParameters(array $parameters): void
    {
        foreach ($parameters as $key => $value) {
            $this->bindParameter($key, $value);
        }
    }

    /**
     * Preparing the database query cursor with the given query and parameters.
     * @param string $query The database query.
     * @param array<string,mixed> $parameters The parameters to bind. Key is the parameter key, value is the parameter value.
     * @return void
     * @throws PDOException If an error occurs while preparing the cursor.
     */
    private function prepareCursor(string $query, array $parameters): void
    {
        try {
            $this->initCursor($query);
            $this->bindParameters($parameters);
        } catch (PDOException|InvalidArgumentException $error) {
            throw new PDOException("The cursor cannot be prepared. - File: {$error->getFile()} - Line: {$error->getLine()} - Error: {$error->getMessage()}", 503);
        }
    }

    /**
     * Executing the database query from the prepared cursor.
     * @param string $query The database query.
     * @param array<string,mixed> $parameters The parameters to bind. Key is the parameter key, value is the parameter value.
     * @return bool The result of the query.
     * @throws PDOException If an error occurs while executing the query.
     */
    private function execute(string $query, array $parameters): bool
    {
        try {
            $this->prepareCursor($query, $parameters);
            return $this->getCursor()->execute();
        } catch (PDOException $error) {
            $parameters = print_r($parameters, true);
            throw new PDOException("The query cannot be executed. - Query: {$query} - Parameters: {$parameters} - File: {$error->getFile()} - Line: {$error->getLine()} - Error: {$error->getMessage()}", 503);
        }
    }

    /**
     * Retrieving all the data from the database query.
     * @param string $query The database query.
     * @param array<string,mixed> $parameters The parameters to bind. Key is the parameter key, value is the parameter value.
     * @return array<int,array<string,mixed>> The data retrieved from the database query.
     * @throws PDOException If an error occurs while retrieving the data.
     */
    private function fetchAll(string $query, array $parameters): array
    {
        try {
            $this->execute($query, $parameters);
            return $this->getCursor()->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $error) {
            throw new PDOException("The data cannot be retrieved from the cursor. - Query: {$query} - Parameters: {$parameters} - File: {$error->getFile()} - Line: {$error->getLine()} - Error: {$error->getMessage()}", 503);
        }
    }

    /**
     * Retrieving data from the database query using a stream.
     * @param string $query The database query.
     * @param array<string,mixed> $parameters The parameters to bind. Key is the parameter key, value is the parameter value.
     * @return iterable The data retrieved from the database query.
     * @throws PDOException If an error occurs while retrieving the data.
     */
    private function fetchStream(string $query, array $parameters): iterable
    {
        try {
            $this->execute($query, $parameters);
            while ($row = $this->getCursor()->fetch(PDO::FETCH_ASSOC)) {
                yield $row;
            }
        } catch (PDOException $error) {
            throw new PDOException("The data cannot be retrieved from the cursor. - Query: {$query} - Parameters: {$parameters} - File: {$error->getFile()} - Line: {$error->getLine()} - Error: {$error->getMessage()}", 503);
        }
    }

    /**
     * Starting a database transaction if there is no transaction.
     * @return void
     * @throws PDOException If an error occurs while starting the transaction.
     */
    private function startTransaction(): void
    {
        if ($this->getConnection()->inTransaction()) {
            return;
        }
        try {
            $this->getConnection()->beginTransaction();
        } catch (PDOException $error) {
            throw new PDOException("The transaction cannot be started. - File: {$error->getFile()} - Line: {$error->getLine()} - Error: {$error->getMessage()}", 503);
        }
    }

    /**
     * Rolling back the current transaction.
     * @param PDOException $previous_error The exception that caused the transaction to fail.
     * @return void
     * @throws PDOException If an error occurs while rolling back the transaction.
     */
    private function rollbackTransaction(PDOException $previous_error): void
    {
        try {
            $this->getConnection()->rollBack();
            throw new PDOException("The transaction cannot be committed. - File: {$previous_error->getFile()} - Line: {$previous_error->getLine()} - Error: {$previous_error->getMessage()}", 503);
        } catch (PDOException $error) {
            throw new PDOException("The transaction cannot be rolled back. - File: {$error->getFile()} - Line: {$error->getLine()} - Error: {$error->getMessage()}", 503);
        }
    }

    /**
     * Committing the database transaction.
     * @return void
     * @throws PDOException If an error occurs while committing the transaction.
     */
    private function commitTransaction(): void
    {
        try {
            $this->getConnection()->commit();
        } catch (PDOException $error) {
            $this->rollbackTransaction($error);
        }
    }

    /**
     * Retrieving data from the database query.
     * @param string $query The database query.
     * @param array<string,mixed> $parameters The parameters to bind. Key is the parameter key, value is the parameter value.
     * @param bool $stream If true, fetchStream will be used. Otherwise, fetchAll will be used.
     * @return iterable The data retrieved from the database query.
     * @throws PDOException If an error occurs while retrieving the data.
     */
    private function get(string $query, array $parameters, bool $stream = false): iterable
    {
        if ($stream) {
            return $this->fetchStream($query, $parameters);
        }
        return $this->fetchAll($query, $parameters);
    }
}