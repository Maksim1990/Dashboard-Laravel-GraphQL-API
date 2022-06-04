<?php

namespace App\Providers;

use App\Services\Auth\AuthManager;
use App\Services\Logging\LoggerService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //LOGGER MICROSERVICE ADAPTER INITIALIZATION
        $this->app->bind(LoggerService::class, function ($app) {

            $arrLogger = $this->getAMQPConnection($app);
            $arrLogger["type"] = $app->config['logging.type'] ?? "";
            return new LoggerService($arrLogger);
        });

        //AUTHORIZATION TOKEN MANAGER INITIALIZATION
        $this->app->bind(
            AuthManager::class, function ($app) {
            $arrParams = [];
            $arrParams['env'] = $app->config['app.env'] ?? 'local';
            return new AuthManager($arrParams);
        }
        );
    }

    private function getAMQPConnection($app): array
    {
        $rabbitMQHosts = current($app->config['queue.connections.rabbitmq.hosts']);
        return [
            'host' => $rabbitMQHosts['host'] ?? "",
            'port' => $rabbitMQHosts['port'] ?? "",
            'user' => $rabbitMQHosts['user'] ?? "",
            'password' => $rabbitMQHosts['password'] ?? "",
            'vhost' => $rabbitMQHosts['vhost'] ?? "",
        ];
    }

    public function boot()
    {

    }
}
