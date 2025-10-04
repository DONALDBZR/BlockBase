<?php
namespace App\Models;

use App\Core\Database_Handler;

/**
 * It provides a base for all models in the application.  It includes methods for retrieving data from the database, as well as for creating, updating, and deleting data.
 * @package App\Models
 * @property ?Database_Handler $database_handler The database handler to use for queries.
 * @method static array<int,self> all() Retrieving all records from the database table.
 * @method static ?self find(int $id) Retrieving a single record from the database table based on the given ID.
 * @method static int create(array $data) Creating a new record in the database table.
 * @method static void update(int $id, array $data) Updating an existing record in the database table.
 * @method static void delete(int $id) Deleting a record from the database table.
 */
abstract class Model
{
    /**
     * The database handler for the model.
     *
     * @var ?Database_Handler
     */
    private static ?Database_Handler $database_handler;

    /**
     * Constructor for the model.
     *
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
    abstract public static function post(array $data): int;

    /**
     * Updating an existing record in the database table.
     * @param int $id The ID of the record to update.
     * @param array<string,mixed> $data The data to update in the database table.
     * @return void
     */
    abstract public static function put(int $id, array $data): void;

    /**
     * Retrieving a single record from the database table based on the given ID.
     * @param int $id The ID of the record to retrieve.
     * @return ?self The record retrieved from the database table, or null if not found.
     */
    abstract public static function get(int $id): ?self;

    /**
     * Retrieving all records from the database table.
     * @return array<int,self> The records retrieved from the database table.
     */
    abstract public static function all(): array;

    /**
     * Retrieving a single record from the database table based on the given ID.
     * @param int $id The ID of the record to retrieve.
     * @return ?self The record retrieved from the database table.
     */
    abstract public static function find(int $id): ?self;

    /**
     * Deleting a record from the database table.
     * @param int $id The ID of the record to delete.
     * @return void
     */
    abstract public static function delete(int $id): void;
}
