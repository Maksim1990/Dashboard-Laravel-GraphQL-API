<?php declare(strict_types=1);

namespace App\Exceptions;

use Throwable;
use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Exceptions\Handler;
use App\Exceptions\Formatter\ExceptionFormatter;

class ExceptionHandler extends Handler
{
    public function __construct(Container $container, private ExceptionFormatter $errorFormatter)
    {
        parent::__construct($container);
    }

    public function render($request, Throwable $e)
    {
        return $this->errorFormatter->getResponse($e);
    }
}
