<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * MessageBrokerService handles publishing and consuming messages via RabbitMQ.
 * This enables event-driven communication between microservices.
 */
class MessageBrokerService
{
    private ?AMQPStreamConnection $connection = null;

    public function publish(string $exchange, string $routingKey, array $message): void
    {
        try {
            $connection = $this->getConnection();
            $channel    = $connection->channel();

            $channel->exchange_declare(
                $exchange,
                'topic',
                false,  // passive
                true,   // durable
                false   // auto-delete
            );

            $body = json_encode($message);
            $msg  = new AMQPMessage($body, [
                'content_type'  => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'timestamp'     => time(),
                'message_id'    => uniqid('msg_', true),
            ]);

            $channel->basic_publish($msg, $exchange, $routingKey);

            Log::info('Message published', [
                'exchange'    => $exchange,
                'routing_key' => $routingKey,
                'event'       => $message['event'] ?? 'unknown',
            ]);

            $channel->close();

        } catch (\Exception $e) {
            Log::error('Failed to publish message to broker', [
                'exchange'    => $exchange,
                'routing_key' => $routingKey,
                'error'       => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function consume(string $queue, string $exchange, string $routingKey, callable $callback): void
    {
        $connection = $this->getConnection();
        $channel    = $connection->channel();

        $channel->exchange_declare($exchange, 'topic', false, true, false);
        $channel->queue_declare($queue, false, true, false, false);
        $channel->queue_bind($queue, $exchange, $routingKey);

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume(
            $queue,
            '',
            false, // no-local
            false, // no-ack
            false, // exclusive
            false, // nowait
            function (AMQPMessage $msg) use ($callback, $channel) {
                try {
                    $data = json_decode($msg->body, true);
                    $callback($data);
                    $channel->basic_ack($msg->delivery_info['delivery_tag']);
                } catch (\Exception $e) {
                    Log::error('Message processing failed', ['error' => $e->getMessage()]);
                    $channel->basic_nack($msg->delivery_info['delivery_tag'], false, true);
                }
            }
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }

    private function getConnection(): AMQPStreamConnection
    {
        if ($this->connection === null || !$this->connection->isConnected()) {
            $this->connection = new AMQPStreamConnection(
                config('rabbitmq.host', 'rabbitmq'),
                config('rabbitmq.port', 5672),
                config('rabbitmq.username', 'guest'),
                config('rabbitmq.password', 'guest'),
                config('rabbitmq.vhost', '/')
            );
        }

        return $this->connection;
    }

    public function __destruct()
    {
        if ($this->connection && $this->connection->isConnected()) {
            $this->connection->close();
        }
    }
}
