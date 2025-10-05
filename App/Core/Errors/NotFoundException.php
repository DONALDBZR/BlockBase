<?php
namespace App\Core\Errors;

use Exception;



/**
 * It extends the `Exception` class in PHP.  It is typically used to indicate that a requested resource could not be found.
 * @see Exception
 */
class NotFoundException extends Exception {}