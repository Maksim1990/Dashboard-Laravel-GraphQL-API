<?php

namespace App\GraphQL\Queries;

use App\GraphQL\Traits\PostTrait;
use App\Models\Like;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Likes
{
    use PostTrait;

    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): int
    {
        $this->checkIfPostExist($args['post_id']);
        return Like::where('post_id', $args['post_id'])->count();
    }

}
