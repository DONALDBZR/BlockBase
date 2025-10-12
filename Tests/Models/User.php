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
     * Getting the creation time of a record from the given value.
     * @param int $created_at The creation time of the record.
     * @return int The creation time of the record. If the given value is empty, it returns the current time.
     */
    private function getCreatedAt(int $created_at): int
    {
        if (!empty($created_at)) {
            return $created_at;
        }
        return time();
    }

    /**
     * Processing the given data.
     * 
     * This function does the following:
     * 1. Sets the username of the record to the given value.
     * 2. Validates the given email address and sets the email of the record to the validated value.
     * 3. Hashes the given password using Argon2i and sets the password_hash of the record to the hashed value.
     * 4. Sets the role of the record to the given value.
     * 5. Sets the status of the record to the given value.
     * 6. Sets the created_at of the record to the given value or the current time if the given value is empty.
     * 7. Sets the updated_at of the record to the current time.
     * @param array{id:?int,username:string,email:string,password_hash:string,role:int,status:int,created_at:int,updated_at:int} $data The data to process.
     * @return void
     * @throws InvalidArgumentException If the email address is invalid.
     */
    private function process(array $data): void
    {
        $this->username = $data["username"];
        $this->email = $this->getEmail($data["email"]);
        $this->password_hash = $this->getPasswordHash($data["password_hash"]);
        $this->role = $data["role"];
        $this->status = $data["status"];
        $this->created_at = $this->getCreatedAt($data["created_at"]);
        $this->updated_at = time();
    }

    /**
     * Pre-processing the given data before saving it to the database.
     * @param array{id:?int,username:string,email:string,password_hash:string,role:int,status:int,created_at:int,updated_at:int} $data The data to pre-process.
     * @return void
     * @throws InvalidArgumentException If the email address is invalid.
     */
    private function preProcess(array $data): void
    {
        $this->process($data);
    }

    /**
     * Pre-processing the given data before saving it to the database.
     * 
     * This function does the following:
     * 1. Validates the given data according to the validation rules.
     * 2. Pre-process the given data before saving it to the database.
     * 3. Returns the pre-processed data.
     * @param array{id:?int,username:string,email:string,password_hash:string,role:int,status:int,created_at:int,updated_at:int} $data The data to pre-process.
     * @return array{id:?int,username:string,email:string,password_hash:string,role:int,status:int,created_at:int,updated_at:int}  The pre-processed data.
     */
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

    /**
     * Post-processing the given data after saving it to the database.
     * 
     * This function does the following:
     * 1. Post-process the given data after saving it to the database.
     * @param array{id:?int,username:string,email:string,password_hash:string,role:int,status:int,created_at:int,updated_at:int} $data The data to post-process.
     * @return void
     */
    protected function afterSave(array $data): void
    {
        try {
            $this->validate($data);
        } catch (InvalidArgumentException $error) {
            $data = [
                "Error" => $error->getMessage(),
                "Code" => $error->getCode(),
                "File" => $error->getFile(),
                "Line" => $error->getLine()
            ];
            $this->logger->log("The data cannot be post-processed.", $this->logger::ERROR, $data);
        }
    }
}
