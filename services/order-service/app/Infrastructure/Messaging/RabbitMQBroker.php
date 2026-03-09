<?php

declare(strict_types=1);

namespace App\Infrastructure\Messaging;

use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * RabbitMQ Message Broker Implementation.
 *
 * Implements the pluggable MessageBrokerInterface for RabbitMQ.
 * Supports direct exchanges, topic exchanges, and dead-letter queues.
 */
class RabbitMQBroker implements MessageBrokerInterface
{
    private ?AMQPStreamConnection $connection = null;

    private ?AMQPChannel $channel = null;

    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $user,
        private readonly string $password,
        private readonly string $vhost,
    ) {}

    /**
     * Publish a message to a RabbitMQ exchange.
     *
     * @param  string               $topic   Exchange name or routing key
     * @param  array<string, mixed> $message
     * @param  array<string, mixed> $options exchange, routing_key, durable, persistent
     * @return bool
     */
    public function publish(string $topic, array $message, array $options = []): bool
    {
        try {
            $channel      = $this->getChannel();
            $exchangeName = $options['exchange'] ?? 'ims.events';
            $routingKey   = $options['routing_key'] ?? $topic;
            $exchangeType = $options['exchange_type'] ?? 'topic';

            // Declare durable exchange
            $channel->exchange_declare(
                $exchangeName,
                $exchangeType,
                false,  // passive
                true,   // durable
                false,  // auto-delete
            );

            $body = json_encode(array_merge($message, [
                '__topic'     => $topic,
                '__timestamp' => now()->toISOString(),
                '__id'        => (string) \Illuminate\Support\Str::uuid(),
            ]), JSON_THROW_ON_ERROR);

            $amqpMessage = new AMQPMessage($body, [
                'content_type'  => 'application/json',
                'delivery_mode' => $options['persistent'] ?? AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ]);

            $channel->basic_publish($amqpMessage, $exchangeName, $routingKey);

            return true;
        } catch (\Throwable $e) {
            Log::error('RabbitMQ publish failed', [
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Subscribe to a RabbitMQ queue.
     *
     * @param  string              $topic   Queue name
     * @param  callable            $handler function(array $message, AMQPMessage $raw): void
     * @param  array<string, mixed> $options
     * @return void
     */
    public function subscribe(string $topic, callable $handler, array $options = []): void
    {
        $channel   = $this->getChannel();
        $queueName = $options['queue'] ?? $topic;

        $channel->queue_declare($queueName, false, true, false, false);

        $channel->basic_consume(
            $queueName,
            '',
            false, // no-local
            false, // no-ack (manual ack required)
            false, // exclusive
            false, // no-wait
            function (AMQPMessage $raw) use ($handler, $topic): void {
                try {
                    $data = json_decode($raw->body, true, 512, JSON_THROW_ON_ERROR);
                    $handler($data, $raw);
                } catch (\Throwable $e) {
                    Log::error("Error processing message from [{$topic}]", ['error' => $e->getMessage()]);
                    $this->reject($raw, false);
                }
            },
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }

    public function acknowledge(mixed $message): void
    {
        if ($message instanceof AMQPMessage) {
            $message->ack();
        }
    }

    public function reject(mixed $message, bool $requeue = false): void
    {
        if ($message instanceof AMQPMessage) {
            $message->nack($requeue);
        }
    }

    public function isConnected(): bool
    {
        return $this->connection?->isConnected() ?? false;
    }

    public function getDriver(): string
    {
        return 'rabbitmq';
    }

    /**
     * Get or create a channel connection.
     *
     * @return AMQPChannel
     */
    private function getChannel(): AMQPChannel
    {
        if ($this->connection === null || !$this->connection->isConnected()) {
            $this->connection = new AMQPStreamConnection(
                $this->host,
                $this->port,
                $this->user,
                $this->password,
                $this->vhost,
            );
            $this->channel = $this->connection->channel();
        }

        return $this->channel;
    }

    public function __destruct()
    {
        $this->channel?->close();
        $this->connection?->close();
    }
}
