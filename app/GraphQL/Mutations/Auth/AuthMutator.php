<?php

namespace App\GraphQL\Mutations\Auth;

use App\Exceptions\GraphQL\GraphqlException;
use App\Models\User;
use App\Services\AWS\Cognito\CognitoClient;
use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthMutator extends BaseAuthMutator
{
    public function resolve($rootValue, array $args)
    {
        $validator = Validator::make($args, [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            throw new GraphqlException(
                implode(',', $validator->messages()->all()),
                [
                    'type' => 'Login error',
                    'category' => 'login',
                    'reason' => 'validation_error',
                ]
            );
        }

        $credentials = Arr::only($args, ['email', 'password']);
        $user = User::where('email', $credentials['email'])->first();

        if (is_null($user)) {
            throw new GraphqlException('User with such credentials is not found', [
                'type' => 'Auth',
                'category' => 'auth',
                'reason' => 'user_not_found',
                'code' => 401,
            ]);
        }
        if (!$user->enabled) {
            throw new GraphqlException('User must be enabled before login', [
                'type' => 'Auth',
                'category' => 'auth',
                'reason' => 'user_not_enabled',
                'code' => 401,
            ]);
        }
        Auth::setUser($user);
        try {
            $authData = app()->make(CognitoClient::class)->authenticate($args['email'], $args['password']);
        } catch (CognitoIdentityProviderException $exception) {
            throw new GraphqlException($exception->getAwsErrorMessage(), [
                'type' => $exception->getAwsErrorCode(),
                'category' => 'auth',
                'reason' => 'user_authentication_failed',
                'code' => 401,
            ]);
        }

        if (!$authData) {
            throw new GraphqlException('Credentials are invalid', [
                'type' => 'Auth',
                'category' => 'auth',
                'reason' => 'Unauthorized error',
                'code' => 401,
            ]);
        }

        //Cookie::queue('a-token', $token, 60 * 60 * 24, null, config('wug.app_domain'));
        return $this->respondWithToken($authData['AuthenticationResult']);

    }
}
