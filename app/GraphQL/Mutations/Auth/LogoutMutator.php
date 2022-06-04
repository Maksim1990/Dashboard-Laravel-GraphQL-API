<?php

namespace App\GraphQL\Mutations\Auth;

use App\Services\AWS\Cognito\CognitoClient;
use Illuminate\Support\Facades\Auth;

class LogoutMutator extends BaseAuthMutator
{
    public function resolve($rootValue, array $args)
    {
        app()->make(CognitoClient::class)->revokeToken(Auth::user()->cognito_client_id, $args['refreshToken']);

        return [
            'message' => 'Successfully logged out',
            'code'=> 200
        ];
    }
}
