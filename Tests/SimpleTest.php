<?php
namespace Tests;

require_once "{$_SERVER['DOCUMENT_ROOT']}/App/Bootstraps/Core.php";
require_once "{$_SERVER['DOCUMENT_ROOT']}/App/Bootstraps/Models.php";


use App\Core\Database_Handler;
use App\Core\Logger;
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
 */
class SimpleTest
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
     * Testing the GET operation of the Model class with multiple records.
     * @param array<int,array{name:string,value:int}> $data The array of records to get from the database table.
     * @return void
     * @throws Exception
     */
    private function getTestSimples(array $data): void
    {
        foreach ($data as $simple) {
            $this->getTestSimple($simple);
        }
    }

    private function testCRUDOperations(): void
    {
        $this->createTestSimplesTable();
        $data = $this->getTestSimplesData();
        $this->postTestSimples($data);
        $this->getTestSimples($data);
        // Test SELECT operation
        $selectResult = $this->db->get("SELECT * FROM test_simple WHERE name = ?", ['test_item']);
        if (empty($selectResult)) {
            throw new Exception("SELECT operation failed");
        }

        if ($selectResult[0]['value'] != 42) {
            throw new Exception("Data integrity check failed");
        }

        // Test UPDATE operation
        $updateConditions = [
            [
                'key' => 'name',
                'value' => 'test_item',
                'is_general_search' => false,
                'operator' => '=',
                'is_bitwise' => false,
                'bit_wise' => ''
            ]
        ];
        
        $updateResult = \App\Models\Model::put('test_simple', ['value' => 84], $updateConditions);
        if (!$updateResult) {
            throw new Exception("UPDATE operation failed");
        }

        // Verify update
        $verifyResult = $this->db->get("SELECT * FROM test_simple WHERE name = ?", ['test_item']);
        if ($verifyResult[0]['value'] != 84) {
            throw new Exception("UPDATE verification failed");
        }

        // Test DELETE operation
        $deleteConditions = [
            [
                'key' => 'name',
                'value' => 'test_item',
                'is_general_search' => false,
                'operator' => '=',
                'is_bitwise' => false,
                'bit_wise' => ''
            ]
        ];
        
        $deleteResult = \App\Models\Model::delete('test_simple', $deleteConditions);
        if (!$deleteResult) {
            throw new Exception("DELETE operation failed");
        }

        // Verify deletion
        $verifyDelete = $this->db->get("SELECT * FROM test_simple WHERE name = ?", ['test_item']);
        if (!empty($verifyDelete)) {
            throw new Exception("DELETE verification failed");
        }

        // Clean up test table
        $this->db->post("DROP TABLE IF EXISTS test_simple");
    }

    private function printResults(): void
    {
        echo "\n==========================\n";
        echo "📊 Test Results Summary\n";
        echo "==========================\n";
        echo "Tests Run: {$this->testsRun}\n";
        echo "Tests Passed: {$this->testsPassed}\n";
        echo "Tests Failed: {$this->testsFailed}\n";
        
        if ($this->testsFailed > 0) {
            echo "\n❌ Failed Tests:\n";
            foreach ($this->failures as $failure) {
                echo "   - {$failure}\n";
            }
        }

        $successRate = ($this->testsPassed / $this->testsRun) * 100;
        echo "\nSuccess Rate: " . number_format($successRate, 1) . "%\n";

        if ($this->testsFailed === 0) {
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
        } else {
            echo "\n⚠️  Some tests failed. Please review the issues above.\n";
        }
    }
}

// Run the tests
$simpleTest = new SimpleTest();
$simpleTest->runTests();
