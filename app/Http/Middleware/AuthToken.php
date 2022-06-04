<?php

namespace App\Http\Middleware;

use App\Exceptions\AuthenticationException;
use App\Models\User;
use App\Services\Auth\AuthManager;
use App\Services\AWS\Cognito\CognitoClient;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthToken
{

    //@TODO Refactor this approach to exclude routes from the auth middleware
    private const AUTH_EXCLUDED_ROUTES = [
        'graphql',
        'register',
        'getMarkdownPageContent',
        'getFaqQuestions',
        'getVersion',
        'getImage',
        'posts',
        'post',
        'users',
        'user',
        'postSearch',
        'login',
        'resendEmail',
        'confirmRegistration',
        'resendConfirmationCode',
        'forgetPassword',
        'forgetPasswordConfirm',
    ];

    private const INTROSPECTION_QUERY_NAME = 'IntrospectionQuery';

    public function __construct(private AuthManager $authManager)
    {

    }

    public function handle(Request $request, Closure $next)
    {
        //Check if necessary to omit authorization for AUTH_EXCLUDED_ROUTES routes
        if ($this->checkIfAuthShouldBeOmitted($request->request->get('query'))) {
            return $next($request);
        }
        $this->processUserAuthentication($request);

        return $next($request);
    }

    private function checkIfAuthShouldBeOmitted(?string $graphQlRequest): bool
    {
        if($graphQlRequest === null) {
            return true;
        }

        return !empty(array_filter(self::AUTH_EXCLUDED_ROUTES, function ($route) use ($graphQlRequest) {
            return str_contains($graphQlRequest, sprintf('%s(', $route)) ||
                str_contains($graphQlRequest, sprintf('%s{', $route)) ||
                str_contains($graphQlRequest, sprintf('%s', $route)) ||
                str_contains($graphQlRequest, self::INTROSPECTION_QUERY_NAME);
        }));
    }

    private function throwAuthException(string $message)
    {
        throw new AuthenticationException($message, Response::HTTP_UNAUTHORIZED);
    }

    private function processUserAuthentication(Request $request): void
    {
        $token = $request->bearerToken();

        //Verify whether token still valid (was not revoked)
        app()->make(CognitoClient::class)->getAuthUser($token);

        if (is_null($token)) {
            $this->throwAuthException('Authorization token must be provided');
        }

        if (!($user = User::find($this->authManager->processAuthToken($token)))) {
            $this->throwAuthException('Can\'t authenticate user');
        }


        Auth::setUser($user);
    }
}
