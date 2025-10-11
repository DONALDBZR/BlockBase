<?php
require_once "{$_SERVER['DOCUMENT_ROOT']}/App/Bootstraps/Core.php";


use App\Core\Database_Handler;
use App\Core\Logger;


/**
 * Asserting that a query to create data in the database is successful.
 * @param Database_Handler $database The database handler to use for queries.
 * @param string $query The query to use for creating data in the database.
 * @return void
 * @throws Exception If the data cannot be created.
 */
function assertIndividualPost(Database_Handler $database, string $query): void
{
    $response = $database->post($query);
    if ($response) {
        return;
    }
    throw new Exception("The data cannot be created.");
}

/**
 * Asserting that a query to create data in the database is successful.
 * @param Database_Handler $database The database handler to use for queries.
 * @param string $query The query to use for creating data in the database.
 * @return void
 * @throws Exception If the data cannot be created.
 */
function assertPost(Database_Handler $database, string $query): void
{
    if (strpos($query, "CREATE TABLE") === false) {
        return;
    }
    assertIndividualPost($database, trim($query));
}

$_ENV['APP_ENV'] = 'testing';
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = ':memory:';
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_SCHEMA'] = 'test';
$_ENV['DB_USERNAME'] = 'test';
$_ENV['DB_PASSWORD'] = 'test';
$_ENV['REMOTE_ADDR'] = '127.0.0.1';
$logger = new Logger();

try {
    $database = new Database_Handler();
    $entity_file_path = "{$_SERVER['DOCUMENT_ROOT']}/Entities.sql";
    $file = file_get_contents($entity_file_path);
    $queries = explode(";", $file);
    foreach ($queries as $query) {
        assertPost($database, $query);
    }
} catch (Exception $error) {
    $context = [
        "Error" => $error->getMessage(),
        "Line" => $error->getLine(),
        "File" => $error->getFile()
    ];
    $logger::log("Test database setup failed!", $logger::ERROR, $context);
}
