<?php

namespace App\Shared\MessageBroker;

use App\Shared\MessageBroker\Contracts\MessageBrokerInterface;
use App\Shared\MessageBroker\Drivers\DatabaseBroker;
use App\Shared\MessageBroker\Drivers\KafkaBroker;
use App\Shared\MessageBroker\Drivers\RabbitMQBroker;
use InvalidArgumentException;

class MessageBrokerFactory
{
    /**
     * Create and return the appropriate MessageBroker implementation.
     *
     * @param string|null $driver  'rabbitmq' | 'kafka' | 'database'
     * @return MessageBrokerInterface
     */
    public static function make(string $driver = null): MessageBrokerInterface
    {
        $driver = $driver ?? config('message_broker.driver', 'database');

        return match ($driver) {
            'rabbitmq' => new RabbitMQBroker(config('message_broker.connections.rabbitmq')),
            'kafka'    => new KafkaBroker(config('message_broker.connections.kafka')),
            'database' => new DatabaseBroker(),
            default    => throw new InvalidArgumentException("Unsupported message broker driver: {$driver}"),
        };
    }
}
