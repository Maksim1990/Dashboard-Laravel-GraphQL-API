<?php declare(strict_types=1);

namespace App\Exceptions\Formatter\Model;

interface ErrorInterface
{
    public function getCode(): int;
    public function getMessage(): string;
}
