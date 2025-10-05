<?php
namespace App\Core\Errors;

use Exception;


/**
 * It is thrown when data atomicity is not respected.  This happens when more than one record or no record is getting updated.
 */
class AtomicityException extends Exception {}
