<?php

namespace App\GraphQL\Mutations\Auth;

use App\Exceptions\GraphQL\GraphqlException;
use App\Services\AWS\Cognito\CognitoClient;
use Illuminate\Support\Facades\Validator;

class ForgotPassword extends BaseAuthMutator
{
    public function resolve($root, array $args): ?array
    {
        $validator = Validator::make($args, ['email' => 'required|email']);

        if ($validator->fails()) {
            throw new GraphqlException(
                implode(',',$validator->messages()->all()),
                [
                    'type' => 'Forgot password send code error',
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

        app()->make(CognitoClient::class)->forgotPassword(
            $this->getUser()->cognito_client_id,
            $args['email']
        );

        return [
            'confirmed' => true,
            'message' => 'forgot_password_requested',
        ];
    }
}
