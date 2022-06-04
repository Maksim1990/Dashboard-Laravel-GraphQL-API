<?php

namespace App\GraphQL\Queries;

class GetVersion
{
    public function __invoke(): array
    {
        return ['version' => config('system.version')];
    }
}
