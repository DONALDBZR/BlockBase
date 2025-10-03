<?php
namespace App\Models;

use App\Core\Database_Handler;
use UnexpectedValueException;

abstract class Model
{
    private Database_Handler $database_handler;
    private string $table_name;
    private ?string $query;
    private ?array $parameters;

    /**
     * Initializing the `Model` instance with the given database handler and table name.
     * @param Database_Handler $database_handler The database handler to use for queries.
     * @param string $table_name The name of the database table to query.
     */
    public function __construct(Database_Handler $database_handler, string $table_name)
    {
        $this->setDatabaseHandler($database_handler);
        $this->setTableName($table_name);
        $this->setQuery(null);
        $this->setParameters(null);
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
        return $this->table_name;
    }

    private function setTableName(string $table_name): void
    {
        $this->table_name = $table_name;
    }

    private function getQuery(): ?string
    {
        return $this->query;
    }

    private function setQuery(?string $query): void
    {
        $this->query = $query;
    }

    private function getParameters(): ?array
    {
        return $this->parameters;
    }

    private function setParameters(?array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * Retrieving models from the database based on the given parameters.
     * @param array<int,string> $columns The columns to select. Defaults to ["*"].
     * @param array<string,mixed> $conditions The conditions to filter the results. Defaults to [].
     * @param array<int,string> $ordering The ordering to apply to the results. Defaults to [].
     * @param int|null $limitation The limitation to apply to the results. Defaults to null.
     * @return array<int,Model> The retrieved models.
     */
    public function get(
        array $columns = ["*"],
        array $conditions = [],
        array $ordering = [],
        ?int $limitation = null
    ): array
    {
        $fields = implode(", ", array_map(fn($column) => "`{$column}`", $columns));
        $this->setQuery("SELECT {$fields} FROM `{$this->getTableName()}`");
        $this->setConditions($conditions);
        $this->setOrdering($ordering);
        $this->setLimitation($limitation);
        $response = $this->getDatabaseHandler()->get($this->getQuery(), $this->getParameters());
        $models = [];
        foreach ($response as $row) {
            $model = new static($this->getDatabaseHandler(), $this->getTableName());
            $model->setAttributes($row);
            $models[] = $model;
        }
        return $models;
    }

    /**
     * Appending a LIMIT clause to the query.
     * @param int|null $limitation The limitation to apply to the results. If null, no limitation is applied.
     * @return void
     */
    private function setLimitation(?int $limitation): void
    {
        if (is_null($limitation)) {
            return;
        }
        $this->setQuery("{$this->getQuery()} LIMIT {$limitation}");
    }

    /**
     * Appending an ORDER BY clause to the query.
     * @param array<int,string> $ordering The ordering to apply to the results.
     * @return void
     */
    private function setOrdering(array $ordering): void
    {
        if (empty($ordering)) {
            return;
        }
        $order = implode(", ", array_map(fn($order) => "`{$order}`", $ordering));
        $this->setQuery("{$this->getQuery()} ORDER BY {$order}");
    }

    /**
     * Appending conditions to the query.
     * @param array<string,mixed> $conditions The conditions to filter the results.
     * @return void
     */
    private function setConditions(array $conditions): void
    {
        if (empty($conditions)) {
            return;
        }
        $where = [];
        $parameters = [];
        foreach ($conditions as $column => $value) {
            $parameter = ":{$column}";
            $where[] = "`{$column}` = {$parameter}";
            $parameters[$parameter] = $this->getValue($value);
        }
        $this->setParameters($parameters);
        $condition = implode(" AND ", $where);
        $this->setQuery("{$this->getQuery()} WHERE {$condition}");
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
}
