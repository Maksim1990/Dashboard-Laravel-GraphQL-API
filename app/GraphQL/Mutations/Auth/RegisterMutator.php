<?php

namespace App\GraphQL\Mutations\Auth;

use App\Exceptions\GraphQL\GraphqlException;
use App\Models\User;
use App\Services\AWS\Cognito\CognitoClient;
use Illuminate\Support\Facades\Validator;

class RegisterMutator extends BaseAuthMutator
{
    public const REGISTER_STATUS = 'REGISTERED';

    public function resolve($rootValue, array $args)
    {
        $validator = Validator::make($args, [
            'email' => 'required|email|unique:users',
            'name' => 'required|unique:users|string|max:50',
            'password' => 'required|confirmed',
        ]);
        if ($validator->fails()) {
            throw new GraphqlException(
                implode(',',$validator->messages()->all()),
                [
                    'type' => 'Registration error',
                    'category' => 'registration',
                    'reason' => 'validation_error',
                ]
            );
        }
        //Add new user into Cognito pool
        $cognitoUserData = app()
            ->make(CognitoClient::class)
            ->register($args['email'], $args['password'], []);

        if (!empty($cognitoUserData)) {
            User::create([
                'name' => $args['name'],
                'email' => $args['email'],
                'enabled' => false,
                'role' => $args['role'] ?? 'user',
                '_id' => $cognitoUserData['user_id'],
                'cognito_client_id' => $cognitoUserData['cognito_client_id']
            ]);

            return [
                'status' => self::REGISTER_STATUS,
                'message' => 'Successfully registered',
            ];
        } else {
            throw new GraphqlException('User can not be registered into Cognito pool', [
                'type' => 'Auth',
                'category' => 'auth',
                'reason' => 'cognito_user_registration_error',
                'code' => 401,
            ]);
        }

    }
}
