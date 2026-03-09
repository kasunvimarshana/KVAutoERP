<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Contracts\MessageBroker\MessageBrokerInterface;
use App\Infrastructure\MessageBroker\Adapters\DatabaseBroker;
use App\Infrastructure\MessageBroker\Adapters\KafkaBroker;
use App\Infrastructure\MessageBroker\Adapters\RabbitMqBroker;
use Illuminate\Support\ServiceProvider;

/**
 * MessageBrokerServiceProvider
 *
 * Binds the MessageBrokerInterface to the configured adapter.
 * Switch adapters by setting MESSAGE_BROKER_DRIVER in .env:
 *   database (default), kafka, rabbitmq
 */
class MessageBrokerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MessageBrokerInterface::class, function ($app): MessageBrokerInterface {
            $driver = config('saas.message_broker.driver', 'database');

            return match ($driver) {
                'kafka'    => new KafkaBroker(
                    brokers: config('saas.message_broker.kafka.brokers', 'localhost:9092'),
                    groupId: config('saas.message_broker.kafka.group_id', 'saas-inventory')
                ),
                'rabbitmq' => new RabbitMqBroker(
                    host:     config('saas.message_broker.rabbitmq.host', 'localhost'),
                    port:     (int) config('saas.message_broker.rabbitmq.port', 5672),
                    user:     config('saas.message_broker.rabbitmq.user', 'guest'),
                    password: config('saas.message_broker.rabbitmq.password', 'guest'),
                    vhost:    config('saas.message_broker.rabbitmq.vhost', '/')
                ),
                default    => new DatabaseBroker(),
            };
        });
    }

    public function boot(): void {}
}
