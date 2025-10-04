<?php
namespace App\Models;

use App\Core\Database_Handler;
use App\Models\Model;


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
     * @param array<string,mixed> $data The data to update in the database table.
     * @param array<int,array{key:string,value:mixed,is_general_search:bool,operator:string,is_bitwise:bool,bit_wise:string}> $condition The conditions to apply to the update query.
     * @return bool True if the data was updated successfully, false otherwise.
     */
    public function update(array $data, array $condition): bool
    {
        $logger = self::getDatabaseHandler()->getLogger();
        $users = self::get(
            true,
            $this->getTableName(),
            "",
            [],
            $condition,
            [],
            [],
            []
        );
        $log_data = [
            "Condition" => $condition,
        ];
        if (empty($users)) {
            $logger::log("There is no user to update.", $logger::ERROR, $log_data);
            return false;
        }
        if (count($users) > 1) {
            $logger::log("There is more than one user to update.", $logger::ERROR, $log_data);
            return false;
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
}
