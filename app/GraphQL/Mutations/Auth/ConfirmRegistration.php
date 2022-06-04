<?php
namespace App\GraphQL\Mutations\Auth;

use App\Services\AWS\Cognito\CognitoClient;
use Illuminate\Support\Facades\Cache;

class ConfirmRegistration extends BaseAuthMutator
{
    public function resolve($root, array $args): ?array
    {
        $code = $args['code'];
        $email = $args['email'];

        if (!$this->processAuthUser($email)) {
            return [
                'confirmed' => false,
                'message' => 'user_not_found',
            ];
        }

        if ($this->getUser()->enabled) {
            return [
                'confirmed' => false,
                'message' => 'registration_already_confirmed',
            ];
        }

        app()->make(CognitoClient::class)->confirmSignUp(
            $this->getUser()->cognito_client_id,
            $code,
            $email
        );

        $this->getUser()->enabled = true;
        $this->getUser()->update();

        Cache::tags(['users'])->flush();

        return [
            'confirmed' => true,
            'message' => 'registration_confirmed',
        ];
    }
}
