<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageBroker\Adapters;

use App\Core\Contracts\MessageBroker\MessageBrokerInterface;
use Illuminate\Support\Facades\Log;

/**
 * RabbitMqBroker
 *
 * Adapter for RabbitMQ via the AMQP PHP extension.
 * Falls back gracefully when the extension is not available (test environments).
 *
 * To activate, set MESSAGE_BROKER_DRIVER=rabbitmq in your .env and configure
 * RABBITMQ_HOST, RABBITMQ_PORT, RABBITMQ_USER, RABBITMQ_PASSWORD, RABBITMQ_VHOST.
 */
class RabbitMqBroker implements MessageBrokerInterface
{
    /** @var \AMQPChannel|null */
    private mixed $channel = null;

    /** @var \AMQPConnection|null */
    private mixed $connection = null;

    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $user,
        private readonly string $password,
        private readonly string $vhost = '/'
    ) {}

    public function publish(string $topic, array $payload, array $options = []): bool
    {
        try {
            $exchange = $this->getExchange($topic);
            $message  = new \AMQPEnvelope();

            $amqpMessage = new \AMQPMessage(
                json_encode($payload),
                array_merge(['content_type' => 'application/json', 'delivery_mode' => 2], $options)
            );

            // Use low-level publish if AMQPExchange is available
            $exchange->publish(
                json_encode($payload),
                $options['routing_key'] ?? $topic
            );

            Log::debug("[MessageBroker:RabbitMQ] Published to [{$topic}]");
            return true;
        } catch (\Throwable $e) {
            Log::error("[MessageBroker:RabbitMQ] Publish failed: {$e->getMessage()}");
            return false;
        }
    }

    public function subscribe(string $topic, callable $callback, array $options = []): void
    {
        try {
            $queue = $this->getQueue($topic);
            $queue->consume(function (\AMQPEnvelope $message, \AMQPQueue $queue) use ($callback): void {
                $payload = json_decode($message->getBody(), true);
                $callback($payload);
                $queue->ack($message->getDeliveryTag());
            });
        } catch (\Throwable $e) {
            Log::error("[MessageBroker:RabbitMQ] Subscribe failed: {$e->getMessage()}");
        }
    }

    public function acknowledge(mixed $messageId): void
    {
        // Tag-based acknowledgement is handled inside the consumer callback
    }

    public function reject(mixed $messageId, bool $requeue = false): void
    {
        // Implement NACK via delivery tag when available
    }

    public function isHealthy(): bool
    {
        try {
            $conn = $this->getConnection();
            return $conn->isConnected();
        } catch (\Throwable) {
            return false;
        }
    }

    // -------------------------------------------------------------------------
    //  Private helpers
    // -------------------------------------------------------------------------

    /** @return \AMQPConnection */
    private function getConnection(): mixed
    {
        if ($this->connection === null || ! $this->connection->isConnected()) {
            $this->connection = new \AMQPConnection([
                'host'     => $this->host,
                'port'     => $this->port,
                'login'    => $this->user,
                'password' => $this->password,
                'vhost'    => $this->vhost,
            ]);
            $this->connection->connect();
        }

        return $this->connection;
    }

    /** @return \AMQPExchange */
    private function getExchange(string $name): mixed
    {
        $channel  = $this->getChannel();
        $exchange = new \AMQPExchange($channel);
        $exchange->setName($name);
        $exchange->setType(AMQP_EX_TYPE_DIRECT);
        $exchange->setFlags(AMQP_DURABLE);
        $exchange->declareExchange();

        return $exchange;
    }

    /** @return \AMQPQueue */
    private function getQueue(string $name): mixed
    {
        $channel = $this->getChannel();
        $queue   = new \AMQPQueue($channel);
        $queue->setName($name);
        $queue->setFlags(AMQP_DURABLE);
        $queue->declareQueue();

        return $queue;
    }

    /** @return \AMQPChannel */
    private function getChannel(): mixed
    {
        if ($this->channel === null) {
            $this->channel = new \AMQPChannel($this->getConnection());
        }

        return $this->channel;
    }
}
