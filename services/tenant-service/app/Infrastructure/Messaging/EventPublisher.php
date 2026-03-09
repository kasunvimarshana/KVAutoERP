<?php

declare(strict_types=1);

namespace App\Infrastructure\Messaging;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Publishes domain events to the configured message broker.
 * Currently supports logging and an optional AMQP publish (if amqplib is installed).
 */
final class EventPublisher
{
    public function __construct(
        private readonly string $exchange  = 'tenant-service',
        private readonly string $routingKeyPrefix = 'tenant',
    ) {}

    /**
     * Publish an event with its payload to the message broker.
     */
    public function publish(string $event, array $payload, array $options = []): void
    {
        $message = [
            'event'      => $event,
            'payload'    => $payload,
            'timestamp'  => now()->toIso8601String(),
            'service'    => 'tenant-service',
            'message_id' => \Illuminate\Support\Str::uuid()->toString(),
        ];

        $routingKey = $this->routingKeyPrefix . '.' . $event;

        // Attempt AMQP publish if the library is available and configured
        if ($this->isAmqpConfigured()) {
            $this->publishToAmqp($routingKey, $message, $options);

            return;
        }

        // Fallback: log the event (useful for dev/test environments)
        Log::channel('stack')->info('Event published (log fallback)', [
            'routing_key' => $routingKey,
            'message'     => $message,
        ]);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function isAmqpConfigured(): bool
    {
        return ! empty(Config::get('services.rabbitmq.host'))
            && class_exists('PhpAmqpLib\Connection\AMQPStreamConnection');
    }

    private function publishToAmqp(string $routingKey, array $message, array $options): void
    {
        try {
            $connection = new \PhpAmqpLib\Connection\AMQPStreamConnection(
                Config::get('services.rabbitmq.host', 'rabbitmq'),
                (int) Config::get('services.rabbitmq.port', 5672),
                Config::get('services.rabbitmq.user', 'guest'),
                Config::get('services.rabbitmq.password', 'guest'),
                Config::get('services.rabbitmq.vhost', '/'),
            );

            $channel = $connection->channel();

            $channel->exchange_declare(
                $this->exchange,
                $options['exchange_type'] ?? 'topic',
                false,
                true,
                false
            );

            $body = json_encode($message, JSON_THROW_ON_ERROR);

            $amqpMessage = new \PhpAmqpLib\Message\AMQPMessage($body, [
                'content_type'  => 'application/json',
                'delivery_mode' => \PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'timestamp'     => time(),
                'message_id'    => $message['message_id'],
            ]);

            $channel->basic_publish($amqpMessage, $this->exchange, $routingKey);

            $channel->close();
            $connection->close();

            Log::debug('Event published to AMQP', [
                'exchange'    => $this->exchange,
                'routing_key' => $routingKey,
            ]);
        } catch (Throwable $e) {
            Log::error('AMQP publish failed, falling back to log', [
                'routing_key' => $routingKey,
                'error'       => $e->getMessage(),
            ]);

            Log::info('Event published (AMQP fallback)', [
                'routing_key' => $routingKey,
                'message'     => $message,
            ]);
        }
    }
}
