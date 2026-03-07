<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQService
{
    private $connection;
    private $channel;

    public function __construct()
    {
        try {
            $this->connection = new AMQPStreamConnection(
                config('rabbitmq.host', 'rabbitmq'),
                config('rabbitmq.port', 5672),
                config('rabbitmq.user', 'guest'),
                config('rabbitmq.password', 'guest')
            );
            $this->channel = $this->connection->channel();
            $this->channel->exchange_declare('product_events', 'topic', false, true, false);
        } catch (\Exception $e) {
            Log::error('RabbitMQ connection failed: ' . $e->getMessage());
        }
    }

    public function publish(string $routingKey, array $data): void
    {
        try {
            if (!$this->channel) {
                return;
            }

            $message = new AMQPMessage(
                json_encode($data),
                [
                    'content_type' => 'application/json',
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                ]
            );

            $this->channel->basic_publish($message, 'product_events', $routingKey);
        } catch (\Exception $e) {
            Log::error('RabbitMQ publish failed: ' . $e->getMessage());
        }
    }

    public function __destruct()
    {
        try {
            $this->channel?->close();
            $this->connection?->close();
        } catch (\Exception $e) {
            // Silently swallow destructor exceptions
        }
    }
}
