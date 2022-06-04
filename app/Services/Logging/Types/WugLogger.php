<?php
namespace App\Services\Logging\Types;

use App\Services\Logging\LoggerException;
use App\Services\Logging\LoggerInterface;
use App\Services\RabbitMQService;

class WugLogger implements LoggerInterface
{
    /** @var array */
    private $params;

    public function __construct($params)
    {
        RabbitMQService::validateLoggerCredentials($params);
        $this->params = $params;
    }


    /**
     * @param array $logData
     * @throws LoggerException
     */
    public function sendLog(array $logData)
    {
        if (!empty($logData) && is_array($logData)) {
            $connection = RabbitMQService::getAMQPStreamConnection($this->params);
            $channel = $connection->channel();
            $exchangeName = 'api_services';
            $channel->exchange_declare($exchangeName, 'topic', false, true, false);
            $routing_key = 'service.logging.log';

            //Data to be sent
            $data = [];
            $log_time = date("Y-m-d h:i:s");
            $service = config('app.service_name');

            $data['service'] = $service;
            $data['log_data'] = [
                "service_name" => $service,
                "log_time" => $log_time,
            ];
            $data['log_time'] = $log_time;
            //ADD APP LOGGING DATA BEFORE SEND TO SERVICE
            $data['log_data'] = array_merge($data['log_data'], $logData);

            $msg = RabbitMQService::getAMQPMessage($data);
            $channel->basic_publish($msg, $exchangeName, $routing_key);
            $channel->close();
            $connection->close();
        } else {
            throw new LoggerException("logging_service_data_must_be_non_empty_array");
        }
    }
}
