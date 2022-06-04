<?php

namespace App\GraphQL\Queries;

use App\Models\User;
use App\GraphQL\Traits\PaginatorResponse;

class UserSearch
{
    use PaginatorResponse;

    public function __invoke($rootValue, array $args): array
    {
        return $this->getAbstractPaginatorResponse(
            User::search($args['search'])->paginate($args['first'], '', $args['page']),
            $args['first']
        );
    }
}
