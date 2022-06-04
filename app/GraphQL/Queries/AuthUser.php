<?php


namespace App\GraphQL\Queries;

use App\Models\User;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class AuthUser
{
    public function getAuthUser($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo):User
    {
        return User::where('_id',Auth::id())->with('settings')->first();
    }
}
