<?php

namespace App\Exceptions\GraphQL;

use Exception;
use GraphQL\Error\Error;
use GraphQL\Error\FormattedError;
use GraphQL\Utils\Utils;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class GraphqlFormattedError extends FormattedError
{
    const UNAUTHENTICATED_VARIABLE = 'unauthenticated';
    const UNAUTHENTICATED_CATEGORY_VARIABLE = 'authentication';

    public static function createException(GraphqlError $e)
    {
        Utils::invariant(
            $e instanceof Exception || $e instanceof Throwable,
            'Expected exception, got %s',
            Utils::getVariableType($e)
        );
        $extensions = $e->getExtensions();

        $formattedError = [
            'messages' => $e->getMessages(),
            'code' => $extensions['code'] ?? Response::HTTP_BAD_REQUEST,
            'extensions' => [
                'category' => self::getExtensionCategory($e),
            ],
        ];

        unset($extensions['code']);

        if (!empty($e->path)) {
            $formattedError['path'] = $e->path;
        }
        if (!empty($e->getExtensions())) {
            $formattedError['extensions'] = $extensions + $formattedError['extensions'];
        }

        return $formattedError;
    }

    private static function getExtensionCategory(GraphqlError $e): string
    {
        return (str_contains(strtolower($e->getMessage()), self::UNAUTHENTICATED_VARIABLE)) ?
            self::UNAUTHENTICATED_CATEGORY_VARIABLE : Error::CATEGORY_INTERNAL;
    }
}
