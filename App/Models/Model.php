<?php
namespace App\Models;

use App\Core\Database_Handler;
use InvalidArgumentException;

/**
 * It provides a base for all models in the application.  It includes methods for retrieving data from the database, as well as for creating, updating, and deleting data.
 * @package App\Models
 * @property ?Database_Handler $database_handler The database handler to use for queries.
 * @property array<string,mixed> $dirty_attributes The attributes that have been changed.
 * @method static void setCondition(string $table_name, array $conditions, array &$parameters, string &$query) Adding a WHERE clause to the query based on the given conditions.
 * @method static void setGrouping(array $groupings, string &$query) Adding a GROUP BY clause to the query based on the given groupings.
 * @method static void setOrdering(array $orderings, string &$query) Adding an ORDER BY clause to the query based on the given orderings.
 * @method static void setLimitation(array $limitations, string &$query) Adding a LIMIT clause to the query based on the given limitations.
 * @method static array get(bool $is_single_entity, string $dataset, string $table_name, array $fields, array $conditions, array $groupings, array $ordering, array $limitation) Retrieving records from the database.
 * @method void setFields(string $field, mixed $data) Setting the value of a field in the model object.
 * @method static self getModel(array<string,mixed> $row) Converting a database row to a model object.
 * @method static array<int,self> all(string $table_name) Retrieving all records from the database table.
 * @method void markDirty(string $attribute) Marking an attribute as dirty.
 * @method array<string,mixed> getDirtyAttributes() Getting the dirty attributes.
 * @method void clearDirtyAttributes() Clearing the dirty attributes.
 * @method static bool post(string $table_name, array $data) Posting data to the database table.
 * @method static bool put(string $table_name, array $data, array $conditions) Updating data in the database table.
 * @method static bool delete(string $table_name, array $conditions) Deleting data from the database table.
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
     * Posting data to the database table.
     * @param string $table_name The name of the table to post the data to.
     * @param array<string,mixed> $data The data to post to the database table.
     * @return bool True if the data was posted successfully, false otherwise.
     */
    public static function post(string $table_name, array $data): bool
    {
        $column = implode(", ", array_keys($data));
        $values = [];
        $parameters = [];
        foreach ($data as $key => $value) {
            $name = ":{$table_name}_{$key}";
            $values[] = $name;
            $parameters[$name] = $value;
        }
        $value = implode(", ", $values);
        $query = "INSERT INTO {$table_name} ({$column}) VALUES ({$value})";
        return self::getDatabaseHandler()->post($query, $parameters);
    }

    /**
     * Updating data in the database table.
     * @param string $table_name The name of the table to update the data in.
     * @param array<string,mixed> $data The data to update in the database table.
     * @param array<int,array{key:string,value:mixed,is_general_search:bool,operator:string,is_bitwise:bool,bit_wise:string}> $conditions The conditions to apply to the update query.
     * @return bool True if the data was updated successfully, false otherwise.
     */
    public static function put(
        string $table_name,
        array $data,
        array $conditions
    ): bool
    {
        $query = "UPDATE {$table_name}";
        $parameters = [];
        $sets = [];
        foreach ($data as $key => $value) {
            $name = ":{$table_name}_{$key}";
            $parameters[$name] = $value;
            $sets[] = "{$key} = {$name}";
        }
        $set = implode(", ", $sets);
        $query .= " SET {$set}";
        self::setCondition(
            $table_name,
            $conditions,
            $parameters,
            $query
        );
        return self::getDatabaseHandler()->put($query, $parameters);
    }

    /**
     * Adding a WHERE clause to the query based on the given conditions.
     * @param string $table_name The name of the table to query.
     * @param array<int,array{key:string,value:mixed,is_general_search:bool,operator:string,is_bitwise:bool,bit_wise:string}> $conditions The conditions to filter the records by.
     * @param array &$parameters The parameters to bind to the query.
     * @param string &$query The query to add the WHERE clause to.
     * @return void
     */
    private static function setCondition(
        string $table_name,
        array $conditions,
        array &$parameters,
        string &$query
    ): void
    {
        if (empty($conditions)) {
            return;
        }
        $where = " WHERE";
        foreach ($conditions as $condition) {
            $name = ":{$table_name}_{$condition['key']}";
            $parameters[$name] = $condition['value'];
            $operator = ($condition['is_general_search']) ? "LIKE" : $condition['operator'];
            $value = ($condition['is_general_search']) ? "%{$condition['value']}%" : $name;
            $where .= " {$condition['key']} {$operator} {$value}";
            $where .= ($condition['is_bitwise']) ? " {$condition['bit_wise']}" : "";
        }
        $query .= $where;
    }

    /**
     * Adding a GROUP BY clause to the query based on the given groupings.
     * @param array<int,string> $groupings The fields to group the records by.
     * @param string &$query The query to add the GROUP BY clause to.
     * @return void
     */
    private static function setGrouping(array $groupings, string &$query): void
    {
        if (empty($groupings)) {
            return;
        }
        $group = " GROUP BY";
        $specification = implode(", ", $groupings);
        $group .= " {$specification}";
        $query .= $group;
    }

    /**
     * Adding an ORDER BY clause to the query based on the given orderings.
     * @param array<int,array{field:string,direction:string}> $orderings The fields to order the records by and the direction of the ordering. The direction should be either "ASC" or "DESC".
     * @param string &$query The query to add the ORDER BY clause to.
     * @return void
     */
    private static function setOrdering(array $orderings, string &$query): void
    {
        if (empty($orderings)) {
            return;
        }
        $orders = [];
        foreach ($orderings as $ordering) {
            $orders[] = "{$ordering['field']} {$ordering['direction']}";
        }
        $specification = implode(", ", $orders);
        $order = " ORDER BY {$specification}";
        $query .= $order;
    }

    /**
     * Adding a LIMIT clause to the query based on the given limitations.
     * @param array{limit:int,offset:int} $limitations The limitations to apply to the query. The array should contain the following keys:
     * - limit: The maximum number of records to return.
     * - offset: The number of records to skip before returning the result.
     * @param string &$query The query to add the LIMIT clause to.
     * @return void
     */
    private static function setLimitation(array $limitations, string &$query): void
    {
        if (empty($limitation)) {
            return;
        }
        $query .= " LIMIT {$limitations['limit']} OFFSET {$limitations['offset']}";
    }

    /**
     * Retrieving records from the database.
     * @param bool $is_single_entity Whether to return a single entity or multiple records.
     * @param string $dataset The dataset to retrieve the records from.
     * @param string $table_name The name of the table to retrieve the records from. If not provided, the dataset name will be used.
     * @param array<string> $fields The fields to retrieve from the database. If empty, all fields will be retrieved.
     * @param array<int,array{key:string,value:mixed,is_general_search:bool,operator:string,is_bitwise:bool,bit_wise:string}> $conditions The conditions to apply to the query.
     * @param array<int,string> $grouping The grouping conditions to apply to the query.
     * @param array<int,array{field:string,direction:string}> $ordering The fields to order the records by and the direction of the ordering.
     * @param array{limit:int,offset:int} $limitation The limitations to apply to the query.
     * @return array<int,self> The retrieved records. If no records were found, an empty array will be returned.
     * @throws InvalidArgumentException If any of the provided parameters are invalid.
     */
    public static function get(
        bool $is_single_entity,
        string $dataset,
        string $table_name = "",
        array $fields = [],
        array $conditions = [],
        array $grouping = [],
        array $ordering = [],
        array $limitation = []
    ): array
    {
        try {
            $column = (empty($fields)) ? "*" : implode(", ", $fields);
            $table_name = ($is_single_entity) ? $dataset : $table_name;
            $query = "SELECT {$column} FROM {$dataset}";
            $parameters = [];
            self::setCondition(
                $table_name,
                $conditions,
                $parameters,
                $query
            );
            self::setGrouping($grouping, $query);
            self::setOrdering($ordering, $query);
            self::setLimitation($limitation, $query);
            $database_response = self::getDatabaseHandler()->get($query, $parameters);
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
                "Dataset" => $dataset
            ];
            $message = "The data cannot be retrieved.";
            self::getDatabaseHandler()->getLogger()::log($message, self::getDatabaseHandler()->getLogger()::ERROR, $data);
            return [];
        }
    }

    /**
     * Setting the value of a field in the model object.  It supports several data types: int, float, string, bool, null, resource.
     * @param string $field The field to set.
     * @param mixed $data The data to set in the field.
     * @throws InvalidArgumentException If the given data type is not supported.
     */
    private function setFields(string $field, mixed $data): void
    {
        if (is_int($data)) {
            $this->$field = intval($data);
            return;
        }
        if (is_float($data)) {
            $this->$field = floatval($data);
            return;
        }
        if (is_string($data)) {
            $this->$field = strval($data);
            return;
        }
        if (is_bool($data)) {
            $this->$field = boolval($data);
            return;
        }
        if (is_null($data)) {
            $this->$field = null;
            return;
        }
        if (is_resource($data)) {
            $this->$field = $data;
            return;
        }
        $log_data = [
            "Field" => $field,
            "Data Type" => gettype($data)
        ];
        $message = "This data type is not allowed in this database.";
        self::getDatabaseHandler()->getLogger()::log($message, self::getDatabaseHandler()->getLogger()::ERROR, $log_data);
        throw new InvalidArgumentException($message, 503);
    }

    /**
     * Converting a database row into a model object.
     * @param array<string,int|float|string|bool|null|resource|array|object> $row The database row to convert into a model object.
     * @return self The model object created from the database row.
     * @throws InvalidArgumentException If the given data type is not supported.
     */
    private static function getModel(array $row): self
    {
        $response = new static(self::getDatabaseHandler());
        foreach ($row as $field => $data) {
            $response->setFields($field, $data);
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

    /**
     * Deleting data from the database by executing a query with the given parameters.
     * @param string $table_name The name of the table to delete from.
     * @param array<int,array{key:string,value:mixed,is_general_search:bool,operator:string,is_bitwise:bool,bit_wise:string}> $conditions The conditions to apply to the query.
     * @return bool True if the data was deleted successfully, false otherwise.
     */
    public static function delete(string $table_name, array $conditions): bool
    {
        $query = "DELETE FROM {$table_name}";
        $parameters = [];
        self::setCondition(
            $table_name,
            $conditions,
            $parameters,
            $query
        );
        return self::getDatabaseHandler()->delete($query, $parameters);
    }
}
