<?php
namespace App\Models;

use App\Core\Database_Handler;
use Attribute;
use Enum;
use UnexpectedValueException;

abstract class Model
{
    private array $attributes;
    private Database_Handler $database_handler;
    private string $table_name;

    public function __construct(Database_Handler $database_handler, string $table_name)
    {
        $this->setDatabaseHandler($database_handler);
        $this->setTableName($table_name);
    }

    private function getDatabaseHandler(): Database_Handler
    {
        return $this->database_handler;
    }

    private function setDatabaseHandler(Database_Handler $database_handler): void
    {
        $this->database_handler = $database_handler;
    }

    private function getTableName(): string
    {
        return $this->$table_name;
    }

    private function setTableName(string $table_name): void
    {
        $this->$table_name = $table_name;
    }

    /**
     * Retrieving all records from the table associated with this model.
     * @return array<int,Model> An array of model objects, each representing a record from the table.
     */
    public function get(): array
    {
        $query = "SELECT * FROM `{$this->getTableName()}`";
        $response = $this->getDatabaseHandler()->get($query);
        $models = [];
        foreach ($response as $row) {
            $model = new static($this->getDatabaseHandler());
            $model->setAttributes($row);
            $models[] = $model;
        }
        return $models;
    }

    /**
     * Converting a value of any type to a type that is supported by the database.
     * @param mixed $value The value to be converted.
     * @return int|float|string|bool|null|resource The converted value.
     * @throws UnexpectedValueException If the value is of an unsupported type.
     */
    private function getValue(mixed $value): mixed
    {
        if (is_int($value)) {
            return intval($value);
        } else if (is_float($value)) {
            return floatval($value);
        } else if (is_string($value)) {
            return strval($value);
        } else if (is_bool($value)) {
            return (bool) $value;
        } else if (is_null($value)) {
            return null;
        } else if (is_resource($value)) {
            return $value;
        } else {
            throw new UnexpectedValueException("The value is of an unsupported type.");
        }
    }

    /**
     * Setting the attributes of the model object based on the given data.
     * @param array<string,mixed> $data The associative array containing the attribute data.
     * @return void
     */
    protected function setAttributes(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->$key = $this->getValue($value);
        }
    }

    public function find(mixed $identifier): ?self
    {
        $table_name =::getTableName();
        $query = "SELECT * FROM `{$table_name}` WHERE identifier = :identifier";
        $parameters = [
            ":identifier" => $identifier
        ];
        $response = $this->getDatabaseHandler()->get($query, $parameters);
        if (empty($response)) {
            return null;
        }
        $model = new($this->getDatabaseHandler());
        $model->setAttributes($response[0]);
        return $model;
    }

    abstract public function findAll(): array;

    abstract public function create(array $data): self;

    abstract public function update(string $id, array $data): self;

    abstract public function delete(string $id): bool;

    protected function executeQuery(string $query, array $params = []): array
    {
        return $this->databaseHandler->executeQuery($query, $params);
    }

    protected function executeTransaction(callable $callback): bool
    {
        return $this->databaseHandler->executeTransaction($callback);
    }
}
