<?php

declare(strict_types=1);

namespace App\Exceptions\GraphQL;


use GraphQL\Error\Error;


class GraphqlError extends Error
{
    private array $messages;

    public function __construct(string $messages, array $extensions, $path)
    {
        $this->messages = json_decode($messages) ?? [$messages];

        parent::__construct(
            $messages,
            null,
            null,
            null,
            $path,
            null,
            $extensions,
        );
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
