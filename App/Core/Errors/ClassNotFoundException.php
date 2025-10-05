<?php
namespace App\Core\Errors;

use App\Core\Errors\NotFoundException;


/**
 * It extends the `Exception` class in PHP.  It does not have any specific methods defined within it.  It is typically used to indicate that a requested class could not be found.  Since it extends `Exception`, it inherits all the methods and properties of the `Exception` class, allowing you to handle and throw this exception in your code.
 */
class ClassNotFoundException extends NotFoundException
{}