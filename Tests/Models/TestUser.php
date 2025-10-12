<?php
namespace Tests\Models;

require_once "{$SERVER['DOCUMENT_ROOT']}/App/Bootstraps/Models.php";
require_once "{$SERVER['DOCUMENT_ROOT']}/App/Bootstraps/Core.php";

use App\Models\Table_Model;
use App\Core\Database_Handler;
use InvalidArgumentException;
use App\Core\Logger;


class User extends Table_Model
{
    public ?int $id = null;
    public string $username;
    public string $email;
    public string $password_hash;
    public int $role;
    public int $status;
    public int $created_at;
    public int $updated_at;
    private string $table = "Test_Users";
    private Logger $logger;

    /**
     * Initializing the user model with the given database handler.
     * @param ?Database_Handler $database_handler The database handler to use for queries.
     */
    public function __construct(?Database_Handler $database_handler = null)
    {
        parent::__construct($database_handler, $this->table);
    }

    /**
     * Getting a validated email address from the given string.
     * @param string $email The email address to validate.
     * @return string The validated email address.
     * @throws InvalidArgumentException If the email address is invalid.
     */
    private function getEmail(string $email): string
    {
        $response = filter_var($email, FILTER_VALIDATE_EMAIL);
        if (!empty($response)) {
            return $response;
        }
        throw new InvalidArgumentException("The email is invalid.", 400);
    }

    /**
     * Getting a hashed password from the given string.
     * 
     * This function does the following:
     * 1. Checks if the password is already hashed using Argon2i.
     * 2. If not, it hashes the password using Argon2i.
     * 3. Returns the hashed password.
     * @param string $password_hash The password to hash.
     * @return string The hashed password.
     */
    private function getPasswordHash(string $password_hash): string
    {
        $hashing_information = password_get_info($password_hash);
        if ($hashing_information["algoName"] === "argon2i") {
            return $password_hash;
        }
        return password_hash($password_hash, PASSWORD_ARGON2I);
    }

    /**
     * Pre-processing the given data before saving it to the database.
     * @param array $data The data to pre-process.
     * @return void
     * @throws InvalidArgumentException If the email address is invalid.
     */
    private function preProcess(array $data): void
    {
        $this->username = $data["username"];
        $this->email = $this->getEmail($data["email"]);
        $this->password_hash = $this->getPasswordHash($data["password_hash"]);
        $this->role = $data["role"];
        $this->status = $data["status"];
        $this->created_at = $data["created_at"];
        $this->updated_at = time();
    }

    protected function beforeSave(array $data): array
    {
        try {
            $this->validate($data);
            $this->preProcess($data);
            return $data;
        } catch (InvalidArgumentException $error) {
            $data = [
                "Error" => $error->getMessage(),
                "Code" => $error->getCode(),
                "File" => $error->getFile(),
                "Line" => $error->getLine()
            ];
            $this->logger::log("The data cannot be pre-processed, hence, it will not be saved.", $this->logger::ERROR, $data);
            return [];
        }
    }
}
