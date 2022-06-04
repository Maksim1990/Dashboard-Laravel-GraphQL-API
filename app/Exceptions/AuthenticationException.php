<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class AuthenticationException extends AbstractException
{
    public function __construct($message = null, $code = Response::HTTP_UNAUTHORIZED)
    {
        parent::__construct($message, $code);
    }
}
