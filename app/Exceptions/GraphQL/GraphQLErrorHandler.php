<?php

namespace App\Exceptions\GraphQL;

use Closure;
use GraphQL\Error\Error;
use Nuwave\Lighthouse\Execution\ErrorHandler;

class GraphQLErrorHandler implements ErrorHandler
{
    public static function handle(Error $error, Closure $next): array
    {
        $error = new GraphqlError(
            $error->getMessage(),
            $error->getExtensions(),
            $error->getPath(),
        );
        return GraphqlFormattedError::createException($error);
    }

    public function __invoke(?Error $error, Closure $next): ?array
    {
        // TODO: Implement __invoke() method.
    }
}
