<?php
namespace App\Models;

use App\Core\Database_Handler;
use App\Core\Errors\AtomicityException;
use App\Models\Model;
use App\Core\Errors\NotFoundException;


/**
 * It is an extension of the `Model` class, which is a base class for all models in the application.  it represents a user in the application.
 * @property int $id The ID of the user.
 * @property string $username The username of the user.
 * @property string $email The email address of the user.
 * @property string $password_hash The hashed password of the user.
 * @property int $role The role of the user which is also the ID of the role.
 * @property int $status The status of the user which is also the ID of the status.
 * @property int $created_at The timestamp of when the user was created.
 * @property int $updated_at The timestamp of when the user was last updated.
 * @property string $table_name The name of the table in the database that the user is stored in.
 * @method bool create(array $properties) Creating a new user with the given properties.
 * @method bool update(array $data, array $condition) Updating an existing user record in the database table.
 * @method array<int,self> find(array $conditions) Retrieving a list of user records from the database table based on the conditions.
 * @method array<int,self> getAll() Retrieving all user records from the database table.
 * @method bool deleteData(array $conditions, bool $is_multiple) Deleting user records from the database table based on the conditions.
 */
class User extends Model
{
    public int $id;
    public string $username;
    public string $email;
    public string $password_hash;
    public int $role;
    public int $status;
    public int $created_at;
    public int $updated_at;
    private string $table_name;

    /**
     * Initializing the user model with the given database handler and properties.
     * @param Database_Handler|null $database_handler The database handler to use for queries.
     * @param array{string,mixed} $properties The properties to set in the model object.
     */
    public function __construct(
        ?Database_Handler $database_handler,
        array $properties = []
    )
    {
        parent::__construct($database_handler);
        foreach ($properties as $key => $value) {
            $this->setFields($key, $value);
        }
        $this->setTableName("Users");
    }

    private function getTableName(): string
    {
        return $this->table_name;
    }

    private function setTableName(string $table_name): void
    {
        $this->table_name = $table_name;
    }

    /**
     * Creating a new user with the given properties.
     * @param array{string,mixed} $properties The properties to set in the new user object.
     * @return bool True if the user was successfully created, false otherwise.
     */
    public function create(array $properties): bool
    {
        $excluded_fields = ["table_name"];
        $user = new static(self::getDatabaseHandler(), $properties);
        $data = [];
        foreach ($user as $key => $value) {
            $is_allowed = !in_array($key, $excluded_fields);
            if (!$is_allowed) {
                continue;
            }
            $data[$key] = $value;
        }
        return $user::post($this->getTableName(), $data);
    }

    /**
     * Updating an existing user record in the database table.
     * 
     * This method does the following:
     * 1. Finds a user record based on the given condition.
     * 2. If no record is found, throws a `NotFoundException`.
     * 3. If more than one record is found, throws an `AtomicityException`.
     * 4. Updates the user record with the given data.
     * @param array<string,mixed> $data The data to update in the database table.
     * @param array<int,array{key:string,value:mixed,is_general_search:bool,operator:string,is_bitwise:bool,bit_wise:string}> $condition The conditions to apply to the update query.
     * @return bool True if the data was updated successfully, false otherwise.
     * @throws NotFoundException If no user is found to update.
     * @throws AtomicityException If more than one user is found to update.
     */
    public function update(array $data, array $condition): bool
    {
        $users = $this->find($condition);
        if (empty($users)) {
            throw new NotFoundException("There is no user to update.");
        }
        if (count($users) > 1) {
            throw new AtomicityException("There is more than one user to update.");
        }
        $user = $users[0];
        foreach ($data as $key => $value) {
            $user->setFields($key, $value);
        }
        $excluded_fields = ["table_name"];
        $data = [];
        foreach ($user as $key => $value) {
            $is_allowed = !in_array($key, $excluded_fields);
            if (!$is_allowed) {
                continue;
            }
            $data[$key] = $value;
        }
        return $user::put($this->getTableName(), $data, $condition);
    }

    /**
     * Retrieving a list of user records from the database table based on the conditions.
     * @param array<int,array{key:string,value:mixed,is_general_search:bool,operator:string,is_bitwise:bool,bit_wise:string}> $conditions The conditions to apply to the query.
     * @return array<int,self> A list of User or an empty array if no record is found.
     */
    public function find(array $conditions): array
    {
        return self::get(
            true,
            $this->getTableName(),
            "",
            [],
            $conditions,
            [],
            [],
            []
        );
    }

    /**
     * Retrieving all user records from the database table.
     * @return array<int,self> A list of User objects or an empty array if no record is found.
     */
    public function getAll(): array
    {
        return self::all($this->getTableName());
    }

    /**
     * Deleting user records from the database table based on the conditions.
     * @param array<int,array{key:string,value:mixed,is_general_search:bool,operator:string,is_bitwise:bool,bit_wise:string}> $conditions The conditions to apply to the query.
     * @param bool $is_multiple Whether to allow multiple records to be deleted or not.
     * @return bool True if the data was deleted successfully, false otherwise.
     * @throws NotFoundException If there is no user to delete.
     * @throws AtomicityException If there is more than one user to delete.
     */
    public function deleteData(
        array $conditions,
        $is_multiple = false
    ): bool
    {
        $users = $this->find($conditions);
        if (empty($users)) {
            throw new NotFoundException("There is no user to delete.");
        }
        if (count($users) > 1 && !$is_multiple) {
            throw new AtomicityException("There is more than one user to delete.");
        }
        $user = $users[0];
        return $user::delete($this->getTableName(), $conditions);
    }
}
