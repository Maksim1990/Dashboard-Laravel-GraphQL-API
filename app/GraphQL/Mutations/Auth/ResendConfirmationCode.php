<?php

namespace App\GraphQL\Mutations\Auth;

use App\Services\AWS\Cognito\CognitoClient;

class ResendConfirmationCode extends BaseAuthMutator
{
    public function resolve($root, array $args): ?array
    {
        $email = $args['email'];

        if (!$this->processAuthUser($email)) {
            return [
                'confirmed' => false,
                'message' => 'user_not_found',
            ];
        }

        if (($this->getCognitoUserData()['UserStatus'] ?? null) === self::USER_CONFIRMED_STATUS) {
            return [
                'confirmed' => false,
                'message' => 'user_already_confirmed',
            ];
        }

        app()->make(CognitoClient::class)->resendConfirmationCode(
            $this->getUser()->cognito_client_id,
            $email
        );

        return [
            'confirmed' => true,
            'message' => 'confirmation_code_resent',
        ];
    }
}
