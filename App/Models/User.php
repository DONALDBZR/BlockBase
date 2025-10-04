<?php
namespace App\Models;

use App\Core\Database_Handler;
use App\Models\Model;


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

    public function create(array $data): int
    {
        return parent::post($this->getTableName(), $data);
    }
}
