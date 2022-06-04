<?php

namespace App\GraphQL\Mutations\Auth;

use App\Exceptions\AuthenticationException;
use App\Exceptions\GraphQL\GraphqlException;
use App\Models\User;
use App\Services\Auth\AuthManager;
use App\Services\AWS\Cognito\CognitoClient;
use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ChangePassword
{
    public function __construct(private AuthManager $authManager)
    {
    }

    public function resolve($root, array $args, GraphQLContext $context): ?array
    {
        $email = $args['email'];
        $previousPassword = $args['previousPassword'];
        $newPassword = $args['newPassword'];
        $newPasswordConfirm = $args['newPasswordConfirm'];

        $accessToken = $this->authManager->getAuthToken($context->request->bearerToken() ?? null);

        $confirmed = false;

        if (User::where('email', $email)->first() === null) {
            return [
                'confirmed' => $confirmed,
                'message' => "user_not_found",
            ];
        }

        if ($newPassword !== $newPasswordConfirm) {
            return [
                'confirmed' => $confirmed,
                'message' => "new_password_not_confirmed",
            ];
        }

        try {
            app()->make(CognitoClient::class)->changePassword(
                $accessToken,
                $previousPassword,
                $newPassword
            );

            return [
                'confirmed' => true,
                'message' => 'password_updated',
            ];

        } catch (CognitoIdentityProviderException $exception) {
            throw new GraphqlException($exception->getAwsErrorMessage(), [
                'type' => $exception->getAwsErrorCode(),
                'category' => 'auth',
                'reason' => 'forget_password_request_failed',
                'code' => 401,
            ]);
        }
    }
}
