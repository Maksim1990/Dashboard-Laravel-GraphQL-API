<?php


namespace App\Services\Logging;

use App\Services\Logging\Types\WugLogger;

class LoggerService
{
    public function __construct(private array $params)
    {

    }

    /**
     * Return logger instance
     * @return LoggerInterface
     * @throws LoggerException
     */
    function getService(): LoggerInterface
    {
        switch ($this->params['type']) {
            case "wug_logger":
                return new WugLogger($this->params);
            default:
                throw new LoggerException("logging_service_undefined");
        }
    }

}
