<?php

namespace App\Shared\MessageBroker;

use App\Shared\MessageBroker\Contracts\MessageBrokerInterface;
use Illuminate\Support\ServiceProvider;

class MessageBrokerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/message_broker.php', 'message_broker');

        $this->app->singleton(MessageBrokerInterface::class, function () {
            return MessageBrokerFactory::make(config('message_broker.driver'));
        });

        // Alias for convenient resolution
        $this->app->alias(MessageBrokerInterface::class, 'message-broker');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/message_broker.php' => config_path('message_broker.php'),
            ], 'message-broker-config');
        }
    }
}
