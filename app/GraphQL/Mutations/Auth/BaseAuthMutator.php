<?php
namespace App\GraphQL\Mutations\Auth;

use App\GraphQL\Mutations\BaseMutator;
use App\Models\User;
use App\Services\AWS\Cognito\CognitoClient;
use Aws\Result;

abstract class BaseAuthMutator extends BaseMutator
{
    private Result|bool $cognitoUserData;
    private ?User $user = null;
    protected const USER_CONFIRMED_STATUS = 'CONFIRMED';

    protected function respondWithToken(array $token)
    {
        return [
            'access_token' => $token['AccessToken'],
            'expires_in' => $token['ExpiresIn'],
            'refresh_token' => $token['RefreshToken'],
            'id_token' => $token['IdToken'],
            'token_type' => $token['TokenType'],
            'user' => auth()->user(),
        ];
    }

    protected function processAuthUser(string $email): bool
    {
        $this->user = User::where('email', $email)->first();
        $this->cognitoUserData = $this->getCognitoUserByName($email);
        return $this->cognitoUserData && $this->user !== null;
    }

    private function getCognitoUserByName(string $email): Result|bool
    {
        return app()->make(CognitoClient::class)->getUser($email);
    }

    protected function getCognitoUserData(): ?Result
    {
        return $this->cognitoUserData;
    }

    protected function getUser(): ?User
    {
        return $this->user;
    }
}
