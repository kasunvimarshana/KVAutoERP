<?php

namespace App\Providers;

use App\Core\Authorization\PolicyManager;
use App\Core\MessageBroker\KafkaMessageBroker;
use App\Core\MessageBroker\MessageBrokerInterface;
use App\Core\MessageBroker\NullMessageBroker;
use App\Core\MessageBroker\RabbitMQMessageBroker;
use App\Core\Tenant\TenantManager;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantManager::class);
        $this->app->singleton(PolicyManager::class);

        $this->app->singleton(MessageBrokerInterface::class, function () {
            return match (config('messagebroker.driver', 'null')) {
                'rabbitmq' => new RabbitMQMessageBroker(),
                'kafka'    => new KafkaMessageBroker(),
                default    => new NullMessageBroker(),
            };
        });
    }

    public function boot(): void
    {
        Passport::tokensCan([
            'sso' => 'Single Sign-On cross-service access',
        ]);
    }
}
