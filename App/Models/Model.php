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
 * @method static ?self get(int $id) Retrieving a single record from the database table based on the given ID.
 * @method static array<int,self> all() Retrieving all records from the database table.
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
     * Retrieving a single record from the database table based on the given ID.
     * @param int $id The ID of the record to retrieve.
     * @return ?self The record retrieved from the database table, or null if not found.
     */
    public static function get(int $id): ?self
    {}

    /**
     * Converting a database row into a model object.
     * @param array<string,mixed> $row The database row to convert into a model object.
     * @return self The model object created from the database row.
     * @throws InvalidArgumentException If the given data type is not supported.
     */
    private static function getModel(array $row): self
    {
        $response = new static(self::getDatabaseHandler());
        foreach ($row as $field => $data) {
            if (is_int($data)) {
                $response->$field = intval($data);
                continue;
            }
            if (is_float($data)) {
                $response->$field = floatval($data);
                continue;
            }
            if (is_string($data)) {
                $response->$field = strval($data);
                continue;
            }
            if (is_bool($data)) {
                $response->$field = boolval($data);
                continue;
            }
            if (is_null($data)) {
                $response->$field = null;
                continue;
            }
            if (is_resource($data)) {
                $response->$field = $data;
                continue;
            }
            throw new InvalidArgumentException("This data type is not allowed in this database. - Field: {$field} - Data: {$data}", 503);
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
