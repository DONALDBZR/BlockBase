<?php
namespace App\Models;

use App\Core\Database_Handler;
use App\Core\Errors\AtomicityException;
use App\Models\Model;
use App\Core\Errors\NotFoundException;
use InvalidArgumentException;

/**
 * It is an extension of the `Model` class, which is a base class for all models in the application.  it represents the main table model in the application.
 * @property string $table_name The name of the table in the database that the record is stored in.
 * @method bool create(array $properties) Creating a new record with the given properties.
 * @method bool update(array $data, array $condition) Updating an existing record in the database table.
 * @method array<int,self> find(array $conditions) Retrieving a list of records from the database table based on the conditions.
 * @method array<int,self> getAll() Retrieving all records from the database table.
 * @method bool deleteData(array $conditions, bool $is_multiple) Deleting records from the database table based on the conditions.
 * @method static bool enforce(bool $condition, string $message, mixed $error) Enforcing a condition and throwing an exception if it is not met.
 * @method array<string,mixed> getData(self $model) This method takes a model object and returns an array containing all the data in the object.
 * @property array<string,array{is_required:bool,max_length:int,is_unique:bool}> $validation_rules The validation rules to apply to the model object.
 * @method bool isUnique(string $field, mixed $value) Checking if a given field with a given value is unique in the database table.
 * @method void validate(array $data) Validating the given data according to the validation rules.
 */
abstract class Table_Model extends Model
{
    private string $table_name;
    private array $validation_rules;

    /**
     * Initializing the model with the given database handler, table name, properties, and validation rules.
     * @param ?Database_Handler $database_handler The database handler to use for queries.
     * @param string $table_name The name of the table in the database that the record is stored in.
     * @param array<string,mixed> $properties The properties to set in the model object. Defaults to an empty array.
     * @param array<string,array{is_required:bool,max_length:int,is_unique:bool}> $validation_rules The validation rules to apply to the model object. Defaults to an empty array.
     */
    public function __construct(
        ?Database_Handler $database_handler,
        string $table_name,
        array $properties = [],
        array $validation_rules = []
    )
    {
        parent::__construct($database_handler);
        $this->setTableName($table_name);
        $this->setValidationRules($validation_rules);
        foreach ($properties as $key => $value) {
            $this->setFields($key, $value);
        }
    }

    private function getTableName(): string
    {
        return $this->table_name;
    }

    private function setTableName(string $table_name): void
    {
        $this->table_name = $table_name;
    }

    /** @return array<string,array{is_required:bool,max_length:int,is_unique:bool}> */
    public function getValidationRules(): array
    {
        return $this->validation_rules;
    }

    public function setValidationRules(array $validation_rules): void
    {
        $this->validation_rules = $validation_rules;
    }

    /**
     * This method takes a model object and returns an array containing all the data in the object.
     * @param self $model The model object to get the data from.
     * @return array<string,mixed> The data in the model object.
     */
    private function getData(self $model): array
    {
        $excluded_fields = ["table_name", "database_handler", "dirty_attributes"];
        $response = [];
        foreach ($model as $key => $value) {
            $is_allowed = !in_array($key, $excluded_fields);
            if (!$is_allowed) {
                continue;
            }
            $response[$key] = $value;
        }
        return $response;
    }

    /**
     * Creating a new with the given properties.
     * @param array<string,mixed> $properties The properties to set in the new object.
     * @return bool True if the was successfully created, false otherwise.
     */
    public function create(array $properties): bool
    {
        $this->validate($properties);
        $model = new static(self::getDatabaseHandler(), $this->getTableName(), $properties);
        $data = $this->getData($model);
        return $model::post($this->getTableName(), $data);
    }

    /**
     * Updating an existing record in the database table.
     * 
     * This method does the following:
     * 1. Finds a record based on the given condition.
     * 2. If no record is found, throws a `NotFoundException`.
     * 3. If more than one record is found, throws an `AtomicityException`.
     * 4. Updates the record with the given data.
     * @param array<string,mixed> $data The data to update in the database table.
     * @param array<int,array{key:string,value:mixed,is_general_search:bool,operator:string,is_bitwise:bool,bit_wise:string}> $condition The conditions to apply to the update query.
     * @return bool True if the data was updated successfully, false otherwise.
     * @throws NotFoundException If no record is found to update.
     * @throws AtomicityException If more than one record is found to update.
     */
    public function update(array $data, array $condition): bool
    {
        $this->validate($data);
        $models = $this->find($condition);
        self::enforce(!empty($models), "There is no user to update.", NotFoundException::class);
        self::enforce(count($models) === 1, "There is more than one user to update.", AtomicityException::class);
        $model = $models[0];
        foreach ($data as $key => $value) {
            $model->setFields($key, $value);
        }
        $data = $this->getData($model);
        return $model::put($this->getTableName(), $data, $condition);
    }

    /**
     * Retrieving a list of records from the database table based on the conditions.
     * @param array<int,array{key:string,value:mixed,is_general_search:bool,operator:string,is_bitwise:bool,bit_wise:string}> $conditions The conditions to apply to the query.
     * @return array<int,self> A list of Models or an empty array if no record is found.
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
     * Retrieving all records from the database table.
     * @return array<int,self> A list of Model objects or an empty array if no record is found.
     */
    public function getAll(): array
    {
        return self::all($this->getTableName());
    }

    /**
     * Deleting records from the database table based on the conditions.
     * @param array<int,array{key:string,value:mixed,is_general_search:bool,operator:string,is_bitwise:bool,bit_wise:string}> $conditions The conditions to apply to the query.
     * @param bool $is_multiple Whether to allow multiple records to be deleted or not.
     * @return bool True if the data was deleted successfully, false otherwise.
     * @throws NotFoundException If there is no record to delete.
     * @throws AtomicityException If there is more than one record to delete.
     */
    public function deleteData(
        array $conditions,
        $is_multiple = false
    ): bool
    {
        $models = $this->find($conditions);
        self::enforce(!empty($models), "There is no user to delete.", NotFoundException::class);
        self::enforce(((count($models) === 1) && !$is_multiple), "There is more than one user to delete.", AtomicityException::class);
        $model = $models[0];
        return $model::delete($this->getTableName(), $conditions);
    }

    /**
     * Enforcing a condition and throwing an exception if it is not met.
     * @param bool $condition The condition to enforce.
     * @param string $message The message to include in the exception.
     * @param NotFoundException|AtomicityException $error The exception to throw if the condition is not met.
     * @throws NotFoundException|AtomicityException If the condition is not met.
     */
    private static function enforce(bool $condition, string $message, mixed $error): void
    {
        if ($condition) {
            return;
        }
        throw new $error($message);
    }

    /**
     * Validating the given data according to the validation rules.
     * @param array<string,mixed> $data The data to validate.
     * @throws InvalidArgumentException If the data does not meet the validation rules.
     */
    private function validate(array $data): void
    {
        foreach ($this->getValidationRules() as $field => $rules) {
            $value = $data[$field] ?? null;
            self::enforce(($rules["is_required"] && !empty($value)), "The field is required.", InvalidArgumentException::class);
            if (is_string($value)) {
                self::enforce((isset($rules["max_length"]) && is_string($value) && (strlen($value) <= $rules["max_length"])), "The field is too long.", InvalidArgumentException::class);
            }
            self::enforce(($rules["is_unique"] && $this->isUnique($field, $value)), "The field must be unique.", InvalidArgumentException::class);
        }
    }

    /**
     * Checking if a given field with a given value is unique in the database table.
     * @param string $field The name of the field to check.
     * @param mixed $value The value to check for in the field.
     * @return bool True if the given field with the given value is unique, false otherwise.
     */
    private function isUnique(string $field, mixed $value): bool
    {
        $response = $this->find(
            [
                [
                    "key" => $field,
                    "value" => $value,
                    "is_general_search" => false,
                    "operator" => "=",
                    "is_bitwise" => false,
                    "bit_wise" => ""
                ]
            ]
        );
        return empty($response);
    }

    abstract protected function beforeSave(array $data): array;

    abstract protected function afterSave(array $data): void;

    abstract protected function beforeDelete(array $conditions): void;

    abstract protected function afterDelete(array $conditions): void;
}
