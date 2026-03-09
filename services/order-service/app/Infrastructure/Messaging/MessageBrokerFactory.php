<?php

declare(strict_types=1);

namespace App\Infrastructure\Messaging;

use Illuminate\Support\Facades\Log;

/**
 * Message Broker Factory.
 *
 * Creates and returns the configured message broker implementation.
 * Supports runtime driver switching based on tenant configuration.
 */
class MessageBrokerFactory
{
    /**
     * Create a message broker instance for the given driver.
     *
     * @param  string|null $driver 'rabbitmq' | 'kafka' | null (uses configured default)
     * @return MessageBrokerInterface
     * @throws \InvalidArgumentException For unsupported drivers
     */
    public static function create(?string $driver = null): MessageBrokerInterface
    {
        $driver = $driver ?? config('services.message_broker.driver', 'rabbitmq');

        return match ($driver) {
            'rabbitmq' => new RabbitMQBroker(
                host: config('services.rabbitmq.host', 'rabbitmq'),
                port: (int) config('services.rabbitmq.port', 5672),
                user: config('services.rabbitmq.user', 'guest'),
                password: config('services.rabbitmq.password', 'guest'),
                vhost: config('services.rabbitmq.vhost', 'ims'),
            ),
            'kafka' => new KafkaBroker(
                brokers: config('services.kafka.brokers', 'kafka:9092'),
                groupId: config('services.kafka.group_id', 'ims-consumers'),
            ),
            default => throw new \InvalidArgumentException("Unsupported message broker driver: [{$driver}]"),
        };
    }
}
