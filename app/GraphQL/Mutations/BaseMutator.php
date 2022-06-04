<?php


namespace App\GraphQL\Mutations;


use App\Services\TokenGenerator\TokenGenerator;

class BaseMutator
{
    public function __construct(private TokenGenerator $tokenGenerator)
    {

    }

    protected function getTokenGenerator(): TokenGenerator {
        return $this->tokenGenerator;
    }
}
