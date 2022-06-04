<?php


namespace App\Exceptions\GraphQL;


use GraphQL\Error\ClientAware;
use Illuminate\Auth\AuthenticationException;

class ClientAwareAuthenticationException extends AuthenticationException implements ClientAware
{
    public function isClientSafe()
    {
        return true;
    }

    public function getCategory()
    {
        return 'authentication';
    }
}
