<?php


namespace App\GraphQL\Resolvers;

use App\Models\CategoryPosts;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class CategoryResolver
{
    function postIDs($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo){
        $category_id=$rootValue['_id'];
        return  CategoryPosts::where('category_id',$category_id)->pluck('post_id')->toArray();
    }

}
