<?php
namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class StorageException extends Exception{

    public function __construct($message = null, $code=Response::HTTP_BAD_REQUEST)
    {
        parent::__construct($message, $code);
    }
}