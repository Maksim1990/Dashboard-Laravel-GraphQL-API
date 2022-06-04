<?php

namespace App\GraphQL\Queries;

use App\Models\Bookmark;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Collection;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Bookmarks
{
    /**
     * Return a value for the field.
     *
     * @param null $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param mixed[] $args The arguments that were passed into the field.
     * @param \Nuwave\Lighthouse\Support\Contracts\GraphQLContext $context Arbitrary data that is shared between all fields of a single query.
     * @param \GraphQL\Type\Definition\ResolveInfo $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return mixed
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Collection
    {
        $user_id = $args['user_id'];
        $offset = $args['offset'] ?? 0;
        $number = $args['number'] ?? 10;
        $orderBy = $args['orderBy'][0] ?? [
                'field'=>'created_at',
                'order'=>'ASC',
            ];

        $bookmark = Bookmark::where('user_id',$user_id)
            ->orderBy($orderBy['field'],$orderBy['order'])->skip($offset)->take($number)
            ->get();
        return $bookmark;
    }

}
