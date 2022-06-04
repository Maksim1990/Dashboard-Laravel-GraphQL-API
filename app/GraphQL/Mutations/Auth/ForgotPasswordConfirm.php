<?php

namespace App\GraphQL\Mutations\Auth;

use App\Exceptions\GraphQL\GraphqlException;
use App\Services\AWS\Cognito\CognitoClient;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordConfirm extends BaseAuthMutator
{
    public function resolve($root, array $args): ?array
    {
        $validator = Validator::make($args, [
            'code' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed',
        ]);

        if ($validator->fails()) {
            throw new GraphqlException(
                implode(',',$validator->messages()->all()),
                [
                    'type' => 'Forget password confirmation error',
                    'category' => 'forget_password',
                    'reason' => 'forget_password_error',
                ]
            );
        }

        if (!$this->processAuthUser($args['email'])) {
            return [
                'confirmed' => false,
                'message' => 'user_not_found',
            ];
        }

        app()->make(CognitoClient::class)->confirmForgotPassword(
            $this->getUser()->cognito_client_id,
            $args['code'],
            $args['email'],
            $args['password']
        );

        return [
            'confirmed' => true,
            'message' => 'confirmation_forgot_password_successful',
        ];
    }
}
