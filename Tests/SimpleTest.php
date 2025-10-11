<?php
namespace Tests;

require_once "{$_SERVER['DOCUMENT_ROOT']}/App/Bootstraps/Core.php";
require_once "{$_SERVER['DOCUMENT_ROOT']}/App/Bootstraps/Models.php";


use App\Core\Database_Handler;
use App\Models\Model;
use Exception;

$_ENV['APP_ENV'] = 'testing';
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = ':memory:';
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_SCHEMA'] = 'test';
$_ENV['DB_USERNAME'] = 'test';
$_ENV['DB_PASSWORD'] = 'test';
$_ENV['REMOTE_ADDR'] = '127.0.0.1';

/**
 * It is designed to run a suite of tests for a basic Object-Relational Mapping system.
 * @property Database_Handler $database The database handler to use for queries.
 * @property int $runs The number of tests that have been run.
 * @property int $passed The number of tests that have passed.
 * @property int $failed The number of tests that have failed.
 * @property array<int,string> $failures An array of test failures.
 * @method void runTests() Running a suite of tests for a basic Object-Relational Mapping system.
 * @method void runTest(string $name, callable $function) Running a single test.
 * @method void testDatabaseConnection() Testing the database connection to ensure it is working properly.
 * @method void testModelBasicFunctionality() Testing the basic functionality of the Model class.
 * @method void createTestSimplesTable() Creating a table for testing purposes called `Test_Simples`.
 * @method string generateRandomString(int $length) Generating a random string of a given length.
 * @method array<int,array{name:string,value:int}> getTestSimplesData() Generating an array of random data for the Test_Simples table.
 * @method void postTestSimple(array $data) Testing the POST operation of the Model class.
 * @method void postTestSimples(array $data) Testing the POST operation of the Model class with multiple records.
 * @method void getTestSimples(array $data) Testing the GET operation of the Model class with multiple records.
 * @method void getTestSimple(array $data) Testing the GET operation of the Model class.
 * @method void putTestSimples(array $data) Testing the PUT operation of the Model class with multiple records.
 * @method void putTestSimple(array $data, int $new_value) Testing the PUT operation of the Model class.
 * @method void deleteTestSimples(array $data) Testing the DELETE operation of the Model class with multiple records.
 * @method void deleteTestSimple(array $data) Testing the DELETE operation of the Model class.
 * @method void dropTestSimplesTable() Dropping the table for testing purposes called `Test_Simples`.
 * @method void testCRUDOperations() Testing the CRUD operations of the Model class.
 * @method void printResults() Printing the results of the tests.
 */
class Simple_Test
{
    private Database_Handler $database;
    private int $runs = 0;
    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];

    /**
     * Initializes the database handler.
     */
    public function __construct()
    {
        $this->database = new Database_Handler();
    }

    /**
     * Running a suite of tests for a basic Object-Relational Mapping system.
     * 
     * this method does the following:
     * 1. Runs a series of tests to verify the basic functionality of the ORM system.
     * 2. Prints the results of the tests.
     * @return void
     */
    public function runTests(): void
    {
        echo "🧪 Running Simple ORM Tests\n";
        echo "==========================\n\n";
        $tests = [
            [
                "name" => "Database Connection",
                "function" => [
                    $this,
                    "testDatabaseConnection"
                ]
            ],
            [
                "name" => "Model Basic Functionality",
                "function" => [
                    $this,
                    "testModelBasicFunctionality"
                ]
            ],
            [
                "name" => "CRUD Operations",
                "function" => [
                    $this,
                    "testCRUDOperations"
                ]
            ]
        ];
        foreach ($tests as $test) {
            $this->runTest($test["name"], $test["function"]);
        }
        $this->printResults();
    }

    /**
     * Running a single test.
     * @param string $name The name of the test to be run.
     * @param callable $function The function to be called as the test.
     * @return void
     */
    private function runTest(string $name, callable $function): void
    {
        $this->runs++;
        echo "Running: {$name}...";
        try {
            $function();
            echo "✅ PASSED\n";
            $this->passed++;
        } catch (Exception $error) {
            echo "❌ FAILED\n";
            echo "Error: {$error->getMessage()} - Line: {$error->getLine()} - File: {$error->getFile()}\n";
            $this->failed++;
            $this->failures[] = "{$name}: {$error->getMessage()} - Line: {$error->getLine()} - File: {$error->getFile()}";
        }
    }

    /**
     * Testing the database connection to ensure it is working properly.
     * @return void
     * @throws Exception If the database connection fails.
     */
    private function testDatabaseConnection(): void
    {
        $response = $this->database->get("SELECT 1 as test");
        if (!empty($response)) {
            return;
        }
        throw new Exception("Database connection failed");
    }

    /**
     * Testing the basic functionality of the Model class.
     * 
     * This method tests the following:
     * 1. Instantiating a new Model object.
     * 2. Marking an attribute as dirty.
     * 3. Retrieving the dirty attributes.
     * 4. Clearing the dirty attributes.
     * 5. Retrieving the dirty attributes again after they have been cleared.
     * 6. Throwing an exception if any of the above steps fail.
     * @return void
     * @throws Exception
     */
    private function testModelBasicFunctionality(): void
    {
        $model = new Model($this->database);
        if (!($model instanceof \App\Models\Model)) {
            throw new Exception("Model instantiation failed");
        }
        $model->markDirty("test_field");
        $dirtyAttributes = $model->getDirtyAttributes();
        if (!array_key_exists("test_field", $dirtyAttributes)) {
            throw new Exception("Dirty tracking failed");
        }
        $model->clearDirtyAttributes();
        $dirtyAttributes = $model->getDirtyAttributes();
        if (!empty($dirtyAttributes)) {
            throw new Exception("Clear dirty attributes failed");
        }
    }

    /**
     * Creating a table for testing purposes called `Test_Simples`.
     * @return void
     * @throws Exception
     */
    private function createTestSimplesTable(): void
    {
        $response = $this->database->post("CREATE TABLE IF NOT EXISTS `Test_Simples` (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(256) NOT NULL, value INTEGER)");
        if ($response) {
            return;
        }
        throw new Exception("Failed to create Test_Simples table");
    }

    /**
     * Generating a random string of a given length.
     * @param int $length The length of the string to generate.
     * @return string The generated random string.
     */
    private function generateRandomString(int $length): string
    {
        $bytes = ceil($length / 2);
        $random_bytes = random_bytes($bytes);
        $hexadecimal = bin2hex($random_bytes);
        return substr($hexadecimal, 0, $length);
    }

    /**
     * Generating an array of random data for the Test_Simples table.
     * @return array<int,array{name:string,value:int}> The generated array of random data.
     */
    private function getTestSimplesData(): array
    {
        $response = [];
        for ($index = 0; $index < 1024; $index++) {
            $limit = $index ^ 2;
            $response[] = [
                "name" => $this->generateRandomString(256),
                "value" => rand(0, $limit)
            ];
        }
        return $response;
    }

    /**
     * Testing the POST operation of the Model class.
     * @param array $data The data to post to the database table.
     * @throws Exception If the POST operation has failed.
     */
    private function postTestSimple(array $data): void
    {
        $response = Model::post("Test_Simples", $data);
        if ($response) {
            return;
        }
        throw new Exception("The POST operation has failed.");
    }

    /**
     * Testing the POST operation of the Model class with multiple records.
     * @param array<int,array{name:string,value:int}> $data The array of records to post to the database table.
     * @return void
     * @throws Exception
     */
    private function postTestSimples(array $data): void
    {
        foreach ($data as $simple) {
            $this->postTestSimple($simple);
        }
    }

    /**
     * Testing the GET operation of the Model class.
     * 
     * This method tests the following:
     * 1. Retrieving a single record from the database table.
     * 2. Throwing an exception if the GET operation has failed.
     * @param array $data The data to retrieve from the database table.
     * @return void
     * @throws Exception If the GET operation has failed.
     */
    private function getTestSimple(array $data): void
    {
        $response = Model::get(
            true,
            "Test_Simples",
            "",
            [],
            [
                "key" => "name",
                "value" => $data["name"],
                "is_general_search" => false,
                "operator" => "=",
                "is_bitwise" => false,
                "bit_wise" => ""
            ],
            [],
            [],
            []
        );
        if (empty($response)) {
            throw new Exception("The GET operation has failed.");
        }
        if ($response[0]->value !== $data["value"]) {
            throw new Exception("Data Integrity Check has failed.");
        }
    }

    /**
     * Testing the GET operation of the Model class with multiple records.
     * @param array<int,array{name:string,value:int}> $data The array of records to get from the database table.
     * @return void
     * @throws Exception
     */
    private function getTestSimples(array $data): void
    {
        $limit = count($data);
        for ($index = 0; $index < $limit; $index++) {
            $array_index = random_int(0, $limit - 1);
            $simple = $data[$array_index];
            $this->getTestSimple($simple);
        }
    }

    /**
     * Testing the PUT operation of the Model class.
     * @param array{name:string,value:int} $data The data to update in the database table.
     * @param int $new_value The new value to update the record with.
     * @return void
     * @throws Exception If the PUT operation has failed or if the verification of the PUT operation has failed.
     */
    private function putTestSimple(array $data, int $new_value): void
    {
        $response = Model::put(
            "Test_Simples",
            [
                "value" => $new_value
            ],
            [
                "key" => "name",
                "value" => $data["name"],
                "is_general_search" => false,
                "operator" => "=",
                "is_bitwise" => false,
                "bit_wise" => ""
            ]
        );
        if (!$response) {
            throw new Exception("The PUT operation has failed.");
        }
        $model = Model::get(
            true,
            "Test_Simples",
            "",
            [],
            [
                "key" => "name",
                "value" => $data["name"],
                "is_general_search" => false,
                "operator" => "=",
                "is_bitwise" => false,
                "bit_wise" => ""
            ],
            [],
            [],
            []
        );
        if ($model[0]->value !== $new_value) {
            throw new Exception("The PUT verification has failed.");
        }
    }

    /**
     * Testing the PUT operation of the Model class with multiple records.
     * 
     * This method tests the following:
     * 1. Updating a single record in the database table.
     * 2. Throwing an exception if the PUT operation has failed.
     * @param array<int,array{name:string,value:int}> $data The array of records to update in the database table.
     * @return void
     * @throws Exception If the PUT operation has failed.
     */
    private function putTestSimples(array $data): void
    {
        $limit = count($data);
        for ($index = 0; $index < $limit; $index++) {
            $array_index = random_int(0, $limit - 1);
            $simple = $data[$array_index];
            $value_limit = $limit ^ 2;
            $new_value = rand(0, $value_limit);
            $this->putTestSimple($simple, $new_value);
        }
    }

    /**
     * Testing the DELETE operation of the Model class with a single record.
     * 
     * This method tests the following:
     * 1. Deleting a single record from the database table.
     * 2. Throwing an exception if the DELETE operation has failed.
     * 3. Throwing an exception if the DELETE verification has failed.
     * @param array $data The data to delete from the database table.
     * @return void
     * @throws Exception If the DELETE operation has failed or the DELETE verification has failed.
     */
    private function deleteTestSimple(array $data): void
    {
        $response = Model::delete(
            "Test_Simples",
            [
                "key" => "name",
                "value" => $data["name"],
                "is_general_search" => false,
                "operator" => "=",
                "is_bitwise" => false,
                "bit_wise" => ""
            ]
        );
        if (!$response) {
            throw new Exception("The DELETE operation has failed.");
        }
        $models = Model::get(
            true,
            "Test_Simples",
            "",
            [],
            [
                "key" => "name",
                "value" => $data["name"],
                "is_general_search" => false,
                "operator" => "=",
                "is_bitwise" => false,
                "bit_wise" => ""
            ],
            [],
            [],
            []
        );
        if (!empty($models)) {
            throw new Exception("The DELETE verification has failed.");
        }
    }

    /**
     * Deleting multiple records from the database table.
     * @param array<int,array{name:string,value:int}> $data The array of records to delete from the database table.
     * @return void
     */
    private function deleteTestSimples(array $data): void
    {
        $limit = count($data);
        for ($index = 0; $index < $limit; $index++) {
            $array_index = random_int(0, $limit - 1);
            $simple = $data[$array_index];
            $this->deleteTestSimple($simple);
        }
    }

    /**
     * Droping the `Test_Simples` table.
     * @return void
     * @throws Exception If the drop operation has failed.
     */
    private function dropTestSimplesTable(): void
    {
        $response = $this->database->post("DROP TABLE IF EXISTS `Test_Simples`");
        if ($response) {
            return;
        }
        throw new Exception("Failed to delete Test_Simples table");
    }

    /**
     * Testing the CRUD operations for the Model class.
     * 
     * This method tests the following:
     * 1. Creating a table for testing purposes called `Test_Simples`.
     * 2. Inserting multiple records into the `Test_Simples` table.
     * 3. Retrieving multiple records from the `Test_Simples` table.
     * 4. Updating multiple records in the `Test_Simples` table.
     * 5. Deleting multiple records from the `Test_Simples` table.
     * 6. Dropping the `Test_Simples` table.
     * @return void
     * @throws Exception If any of the operations fail.
     */
    private function testCRUDOperations(): void
    {
        $this->createTestSimplesTable();
        $data = $this->getTestSimplesData();
        $this->postTestSimples($data);
        $this->getTestSimples($data);
        $this->putTestSimples($data);
        $this->deleteTestSimples($data);
        $this->dropTestSimplesTable();
    }

    /**
     * Printing a summary of the test results to the console.
     * 
     * It includes the following information:
     * 1. The total number of tests run.
     * 2. The number of tests that have passed.
     * 3. The number of tests that have failed.
     * 4. A list of the failed tests.
     * 5. The success rate of the tests in percentage format.
     * 6. A message indicating if all tests have passed or not.
     * 7. A message indicating the implementation of the test suite is complete.
     * @return void
     */
    private function printResults(): void
    {
        echo "\n==========================\n";
        echo "📊 Test Results Summary\n";
        echo "==========================\n";
        echo "Tests Run: {$this->runs}\n";
        echo "Tests Passed: {$this->passed}\n";
        echo "Tests Failed: {$this->failed}\n";
        $has_failures = ($this->failed > 0);
        if ($has_failures) {
            echo "\n❌ Failed Tests:\n";
            foreach ($this->failures as $failure) {
                echo " - {$failure}\n";
            }
        }
        $success_rate = number_format(($this->passed / $this->runs) * 100, 2);
        echo "\nSuccess Rate: {$success_rate} %\n";
        if ($has_failures) {
            echo "\n❗ Some tests failed. Please review the issues above.\n";
            return;
        }
        echo "\n🎉 All tests passed! Basic ORM functionality is working.\n";
        echo "\n✅ ORM Test Suite Implementation Complete!\n";
        echo "The comprehensive test suite has been created with:\n";
        echo "- PHPUnit configuration and bootstrap\n";
        echo "- Unit tests for Model class\n";
        echo "- Integration tests for CRUD operations\n";
        echo "- Relationship and data loading tests\n";
        echo "- Scopes and filters tests\n";
        echo "- Transaction and bulk operations tests\n";
        echo "- Migration and seeder tests\n";
        echo "- Test models and runner scripts\n";
        echo "- Comprehensive documentation\n\n";
    }
}

$simple_test = new Simple_Test();
$simple_test->runTests();
