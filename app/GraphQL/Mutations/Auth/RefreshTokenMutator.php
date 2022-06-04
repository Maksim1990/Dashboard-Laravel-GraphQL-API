<?php

namespace App\GraphQL\Mutations\Auth;

use App\Exceptions\GraphQL\GraphqlException;
use Illuminate\Http\Response;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class RefreshTokenMutator extends BaseAuthMutator
{

    public function resolve($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $token=Auth::guard('api')->claims(['user_id' => Auth::id()])->refresh();
        if (!$token ) {
            throw new GraphqlException('Credentials are invalid',[
                'type'=>'Auth',
                'category'=>'auth',
                'reason'=>'Unauthorized error',
                'code'=>Response::HTTP_UNAUTHORIZED,
            ]);
        }else{
            return $this->respondWithToken($token);
        }
    }
}
