<?php
namespace App\Models;

use App\Core\Database_Handler;
use InvalidArgumentException;

/**
 * It provides a base for all models in the application.  It includes methods for retrieving data from the database, as well as for creating, updating, and deleting data.
 * @package App\Models
 * @property ?Database_Handler $database_handler The database handler to use for queries.
 * @property array<string,mixed> $dirty_attributes The attributes that have been changed.
 * @method static int post(array<string,mixed> $data) Creating a new record in the database table.
 * @method static void put(int $id, array<string,mixed> $data) Updating an existing record in the database table.
 * @method void setFields(string $field, mixed $data) Setting the value of a field in the model object.
 * @method static self getModel(array<string,mixed> $row) Converting a database row to a model object.
 * @method static array<int,self> all(string $table_name) Retrieving all records from the database table.
 * @method static void delete(int $id) Deleting a record from the database table.
 * @method void markDirty(string $attribute) Marking an attribute as dirty.
 * @method array<string,mixed> getDirtyAttributes() Getting the dirty attributes.
 * @method void clearDirtyAttributes() Clearing the dirty attributes.
 */
class Model
{
    /**
     * The database handler for the model.
     * @var ?Database_Handler
     */
    private static ?Database_Handler $database_handler;
    /**
     * Tracking changes to the model.
     * @var array<string,mixed> $dirty_attributes The attributes that have been changed.
     */
    private array $dirty_attributes = [];

    /**
     * Constructor for the model.
     * @param ?Database_Handler $database_handler The database handler to use for queries.
     */
    public function __construct(
        ?Database_Handler $database_handler = null
    )
    {
        self::setDatabaseHandler($database_handler ?? new Database_Handler());
    }

    public static function getDatabaseHandler(): ?Database_Handler
    {
        return self::$database_handler;
    }

    public static function setDatabaseHandler(?Database_Handler $database_handler)
    {
        self::$database_handler = $database_handler;
    }

    /**
     * Creating a new record in the database table.
     * @param array<string,mixed> $data The data to insert into the database table.
     * @return int The ID of the newly created record.
     */
    public static function post(array $data): int
    {}

    /**
     * Updating an existing record in the database table.
     * @param int $id The ID of the record to update.
     * @param array<string,mixed> $data The data to update in the database table.
     * @return void
     */
    public static function put(int $id, array $data): void
    {}

    /**
     * Adding a WHERE clause to the query based on the given conditions.
     * @param string $table_name The name of the table to query.
     * @param array $conditions The conditions to filter the records by.
     * @param array &$parameters The parameters to bind to the query.
     * @param string &$query The query to add the WHERE clause to.
     * @return void
     */
    private static function setCondition(
        string $table_name,
        array $conditions,
        array &$parameters,
        string &$query
    ): void
    {
        if (empty($conditions)) {
            return;
        }
        $where = " WHERE";
        foreach ($conditions as $condition) {
            $name = ":{$table_name}_{$condition['key']}";
            $parameters[$name] = $condition['value'];
            $operator = ($condition['is_general_search']) ? "LIKE" : $condition['operator'];
            $value = ($condition['is_general_search']) ? "%{$condition['value']}%" : $name;
            $where .= " {$condition['key']} {$operator} {$value}";
            $where .= ($condition['is_bitwise']) ? " {$condition['bit_wise']}" : "";
        }
        $query .= $where;
    }

    public static function get(
        string $table_name,
        array $fields = [],
        array $conditions = [],
    ): ?self
    {
        $column = (empty($fields)) ? "*" : implode(", ", $fields);
        $query = "SELECT {$column} FROM {$table_name}";
        $parameters = [];
        self::setCondition(
            $table_name,
            $conditions,
            $parameters,
            $query
        );
    }

    /**
     * Setting the value of a field in the model object.  It supports several data types: int, float, string, bool, null, resource.
     * @param string $field The field to set.
     * @param mixed $data The data to set in the field.
     * @throws InvalidArgumentException If the given data type is not supported.
     */
    private function setFields(string $field, mixed $data): void
    {
        if (is_int($data)) {
            $this->$field = intval($data);
            return;
        }
        if (is_float($data)) {
            $this->$field = floatval($data);
            return;
        }
        if (is_string($data)) {
            $this->$field = strval($data);
            return;
        }
        if (is_bool($data)) {
            $this->$field = boolval($data);
            return;
        }
        if (is_null($data)) {
            $this->$field = null;
            return;
        }
        if (is_resource($data)) {
            $this->$field = $data;
            return;
        }
        $log_data = [
            "Field" => $field,
            "Data Type" => gettype($data)
        ];
        $message = "This data type is not allowed in this database.";
        self::getDatabaseHandler()->getLogger()::log($message, self::getDatabaseHandler()->getLogger()::ERROR, $log_data);
        throw new InvalidArgumentException($message, 503);
    }

    /**
     * Converting a database row into a model object.
     * @param array<string,int|float|string|bool|null|resource|array|object> $row The database row to convert into a model object.
     * @return self The model object created from the database row.
     * @throws InvalidArgumentException If the given data type is not supported.
     */
    private static function getModel(array $row): self
    {
        $response = new static(self::getDatabaseHandler());
        foreach ($row as $field => $data) {
            $response->setFields($field, $data);
        }
        return $response;
    }

    /**
     * Retrieving all records from the database table.
     * @return array<int,self> The records retrieved from the database table.
     */
    public static function all(string $table_name): array
    {
        try {
            $query = "SELECT * FROM {$table_name}";
            $database_response = self::getDatabaseHandler()->get($query);
            if (empty($database_response)) {
                return [];
            }
            $response = [];
            foreach ($database_response as $row) {
                $response[] = self::getModel($row);
            }
            return $response;
        } catch (InvalidArgumentException $error) {
            $data = [
                "Error" => $error->getMessage(),
                "File" => $error->getFile(),
                "Line" => $error->getLine(),
                "Table Name" => $table_name
            ];
            $message = "The data cannot be retrieved.";
            self::getDatabaseHandler()->getLogger()::log($message, self::getDatabaseHandler()->getLogger()::ERROR, $data);
            return [];
        }
    }

    /**
     * Deleting a record from the database table.
     * @param int $id The ID of the record to delete.
     * @return void
     */
    public static function delete(int $id): void
    {}

    /**
     * Marking an attribute as dirty.
     * @param string $attribute The attribute to mark as dirty.
     * @return void
     */
    public function markDirty(string $attribute): void
    {
        $this->dirty_attributes[$attribute] = true;
    }

    /**
     * Getting the dirty attributes.
     * @return array<string,mixed> The dirty attributes.
     */
    public function getDirtyAttributes(): array
    {
        return $this->dirty_attributes;
    }

    /**
     * Clearing the dirty attributes.
     * @return void
     */
    public function clearDirtyAttributes(): void
    {
        $this->dirty_attributes = [];
    }
}
