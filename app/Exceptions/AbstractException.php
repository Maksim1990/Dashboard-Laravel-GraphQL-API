<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

abstract class AbstractException extends Exception implements HttpExceptionInterface
{

    public function __construct(string $message = null, int $code = Response::HTTP_UNAUTHORIZED)
    {
        parent::__construct($message, $code);
    }

    public function getStatusCode()
    {
        return $this->code;
    }

    public function getHeaders()
    {
        // TODO: Implement getHeaders() method.
    }
}
