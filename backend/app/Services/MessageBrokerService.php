<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Facades\Log;

class MessageBrokerService
{
    private ?AMQPStreamConnection $connection = null;
    private ?\PhpAmqpLib\Channel\AMQPChannel $channel = null;

    private function connect(): void
    {
        if ($this->connection && $this->connection->isConnected()) {
            return;
        }

        $this->connection = new AMQPStreamConnection(
            config('rabbitmq.host'),
            config('rabbitmq.port'),
            config('rabbitmq.user'),
            config('rabbitmq.password'),
            config('rabbitmq.vhost')
        );

        $this->channel = $this->connection->channel();

        $this->channel->exchange_declare(
            config('rabbitmq.exchange'),
            'topic',
            false,
            true,
            false
        );
    }

    public function publish(string $routingKey, array $payload): void
    {
        try {
            $this->connect();

            $message = new AMQPMessage(
                json_encode($payload),
                [
                    'content_type'  => 'application/json',
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                ]
            );

            $this->channel->basic_publish(
                $message,
                config('rabbitmq.exchange'),
                $routingKey
            );
        } catch (\Exception $e) {
            Log::error('MessageBroker publish error', [
                'routing_key' => $routingKey,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    public function subscribe(string $queueName, string $routingKey, callable $callback): void
    {
        $this->connect();

        $this->channel->queue_declare($queueName, false, true, false, false);
        $this->channel->queue_bind($queueName, config('rabbitmq.exchange'), $routingKey);

        $this->channel->basic_consume(
            $queueName,
            '',
            false,
            false,
            false,
            false,
            function ($msg) use ($callback) {
                $payload = json_decode($msg->body, true);
                $callback($payload);
                $msg->ack();
            }
        );

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    public function close(): void
    {
        if ($this->channel) {
            $this->channel->close();
        }

        if ($this->connection && $this->connection->isConnected()) {
            $this->connection->close();
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}
