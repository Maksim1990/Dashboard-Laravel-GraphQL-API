<?php

namespace App\GraphQL\Queries;

use App\Models\Post;
use App\GraphQL\Traits\PaginatorResponse;

class PostSearch
{
    use PaginatorResponse;

    public function __invoke($rootValue, array $args): array
    {
        return $this->getAbstractPaginatorResponse(
            Post::search($args['search'])->paginate($args['first'], '', $args['page']),
            $args['first']
        );
    }
}
