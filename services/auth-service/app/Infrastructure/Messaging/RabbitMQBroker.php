<?php

declare(strict_types=1);

namespace App\Infrastructure\Messaging;

use App\Contracts\Messaging\MessageBrokerInterface;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

/**
 * RabbitMQ implementation of MessageBrokerInterface using php-amqplib.
 *
 * Note: php-amqplib is not listed in composer.json because it is an optional
 * infrastructure dependency. Add `"php-amqplib/php-amqplib": "^3.6"` if RabbitMQ
 * messaging is required.  The service gracefully degrades to log-only mode when
 * the class is unavailable (e.g. in environments that use SQS instead).
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
        private readonly string $vhost = '/',
    ) {}

    public function publish(string $exchange, string $routingKey, array $message, array $options = []): bool
    {
        try {
            $channel = $this->getChannel();

            $channel->exchange_declare(
                $exchange,
                $options['exchange_type'] ?? 'topic',
                false,
                $options['durable'] ?? true,
                false
            );

            $body = json_encode($message, JSON_THROW_ON_ERROR);

            $amqpMessage = new AMQPMessage($body, [
                'content_type'  => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'timestamp'     => time(),
                'message_id'    => $options['message_id'] ?? uniqid('msg_', true),
            ]);

            $channel->basic_publish($amqpMessage, $exchange, $routingKey);

            Log::debug('RabbitMQ message published', [
                'exchange'    => $exchange,
                'routing_key' => $routingKey,
            ]);

            return true;
        } catch (Throwable $e) {
            Log::error('RabbitMQ publish failed', [
                'exchange'    => $exchange,
                'routing_key' => $routingKey,
                'error'       => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function subscribe(string $queue, callable $handler, array $options = []): void
    {
        try {
            $channel = $this->getChannel();

            $channel->queue_declare(
                $queue,
                false,
                $options['durable'] ?? true,
                false,
                false
            );

            $channel->basic_qos(null, $options['prefetch'] ?? 1, null);

            $channel->basic_consume(
                $queue,
                '',
                false,
                false,
                false,
                false,
                function (AMQPMessage $amqpMessage) use ($handler): void {
                    $payload = json_decode($amqpMessage->body, true, 512, JSON_THROW_ON_ERROR);
                    $handler($payload);
                    $amqpMessage->ack();
                }
            );

            while ($channel->is_consuming()) {
                $channel->wait();
            }
        } catch (Throwable $e) {
            Log::error('RabbitMQ subscribe failed', ['queue' => $queue, 'error' => $e->getMessage()]);
            throw $e;
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
            $message->reject($requeue);
        }
    }

    public function isConnected(): bool
    {
        try {
            return $this->connection !== null && $this->connection->isConnected();
        } catch (Throwable) {
            return false;
        }
    }

    private function getChannel(): AMQPChannel
    {
        if ($this->channel === null || ! $this->connection?->isConnected()) {
            $this->connect();
        }

        return $this->channel;
    }

    private function connect(): void
    {
        $this->connection = new AMQPStreamConnection(
            $this->host,
            $this->port,
            $this->user,
            $this->password,
            $this->vhost,
        );

        $this->channel = $this->connection->channel();
    }

    public function __destruct()
    {
        try {
            $this->channel?->close();
            $this->connection?->close();
        } catch (Throwable) {
            // Ignore connection teardown errors
        }
    }
}
