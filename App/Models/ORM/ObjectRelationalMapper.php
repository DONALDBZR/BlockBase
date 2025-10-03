<?php

namespace App\Models\ORM;

use App\Core\Database_Handler;


/**
 * Abstract class for the Object Relational Mapper.
 * @package App\Models\ORM
 * @property-read Database_Handler $database_handler The database handler to use for queries.
 * @method array<int,self> all() Retrieving all the records from the database table.
 * @method self|null find(int $id) Retrieving a single record from the database table based on the given ID.
 * @method int create(array $data) Creating a new record in the database table.
 * @method void update(int $id, array $data) Updating an existing record in the database table.
 * @method void delete(int $id) Deleting a record from the database table.
 */
abstract class Object_Relational_Mapper
{
    /**
     * The database handler to use for queries.
     * @var Database_Handler
     */
    protected Database_Handler $database_handler;

    /**
     * Initializing the object relational mapper with the given database handler.
     * @param Database_Handler $database_handler The database handler to use for queries.
     */
    public function __construct(Database_Handler $database_handler)
    {
        $this->setDatabaseHandler($database_handler);
    }

    public function getDatabaseHandler(): Database_Handler
    {
        return $this->database_handler;
    }

    private function setDatabaseHandler(Database_Handler $database_handler): void
    {
        $this->database_handler = $database_handler;
    }

    /**
     * Retrieving all the records from the database table.
     * @return array<int,self> The records retrieved from the database table.
     * @abstract
     */
    abstract public function all(): array;

    /**
     * Retrieving a single record from the database table based on the given ID.
     * @param int $id The ID of the record to retrieve.
     * @return ?self The record retrieved from the database table.
     * @abstract
     */
    abstract public function find(int $id): mixed;

    /**
     * Creating a new record in the database table.
     * @param array<string,mixed> $data The data to insert into the database table.
     * @return int The ID of the newly created record.
     * @abstract
     */
    abstract public function create(array $data): int;

    /**
     * Updating an existing record in the database table.
     * @param int $id The ID of the record to update.
     * @param array<string,mixed> $data The data to update in the database table.
     * @return void
     * @abstract
     */
    abstract public function update(int $id, array $data): void;

    /**
     * Deleting a record from the database table.
     * @param int $id The ID of the record to delete.
     * @return void
     * @abstract
     */
    abstract public function delete(int $id): void;
}