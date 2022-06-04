<?php declare(strict_types=1);

namespace App\Exceptions\Formatter\Model;

class Error implements ErrorInterface
{
    public function __construct(
        private int $code,
        private string $message
    )
    {
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
