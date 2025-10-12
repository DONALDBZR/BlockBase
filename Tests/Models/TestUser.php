<?php
namespace Tests\Models;

require_once "{$SERVER['DOCUMENT_ROOT']}/App/Bootstraps/Models.php";
require_once "{$SERVER['DOCUMENT_ROOT']}/App/Bootstraps/Core.php";

use App\Models\Table_Model;
use App\Core\Database_Handler;

class User extends Table_Model
{
    public ?int $id = null;
    public string $username;
    public string $email;
    public string $password_hash;
    public int $role;
    public int $status_id;
    public int $created_at;
    public int $updated_at;
    private string $table = "Test_Users";

    /**
     * Initializing the user model with the given database handler.
     * @param ?Database_Handler $database_handler The database handler to use for queries.
     */
    public function __construct(?Database_Handler $database_handler = null)
    {
        parent::__construct($database_handler, $this->table);
    }

    /**
     * Create a new user record
     */
    public static function create(array $data): int
    {
        $user = new static();
        return static::post('test_users', $data) ? 1 : 0; // Simplified for testing
    }

    /**
     * Update user record
     */
    public static function update(array $data, array $condition): bool
    {
        $user = new static();
        $users = static::find($condition);
        if (count($users) === 1) {
            return static::put('test_users', $data, $condition);
        }
        return false;
    }

    /**
     * Find users by conditions
     */
    public static function find(array $conditions): array
    {
        $formattedConditions = [];
        foreach ($conditions as $key => $value) {
            $formattedConditions[] = [
                'key' => $key,
                'value' => $value,
                'is_general_search' => false,
                'operator' => '=',
                'is_bitwise' => false,
                'bit_wise' => ''
            ];
        }
        
        return static::get(false, 'test_users', 'test_users', [], $formattedConditions);
    }

    /**
     * Get all users
     */
    public static function getAll(): array
    {
        return static::all('test_users');
    }

    /**
     * Delete users by conditions
     */
    public static function deleteUsers(array $conditions): bool
    {
        $formattedConditions = [];
        foreach ($conditions as $key => $value) {
            $formattedConditions[] = [
                'key' => $key,
                'value' => $value,
                'is_general_search' => false,
                'operator' => '=',
                'is_bitwise' => false,
                'bit_wise' => ''
            ];
        }
        
        return static::delete('test_users', $formattedConditions);
    }
}
