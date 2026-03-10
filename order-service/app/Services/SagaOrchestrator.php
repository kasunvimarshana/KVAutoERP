<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * SagaOrchestrator publishes events to RabbitMQ to drive the
 * Choreography-based Saga for order processing.
 *
 * Saga flow:
 *  1. Order created → publishes order.created
 *  2. Product Service → inventory.reserved | inventory.reservation.failed
 *  3. Payment Service → payment.processed  | payment.failed
 *  4. Order Service listens (see SagaConsumer) and updates order status.
 *
 * Compensation:
 *  - payment.failed → publishes inventory.release (Product Service rollback)
 */
class SagaOrchestrator
{
    private const EXCHANGE = 'saga.events';

    private AMQPStreamConnection $connection;
    private \PhpAmqpLib\Channel\AMQPChannel $channel;

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST', 'rabbitmq'),
            (int) env('RABBITMQ_PORT', 5672),
            env('RABBITMQ_USER', 'guest'),
            env('RABBITMQ_PASS', 'guest')
        );
        $this->channel = $this->connection->channel();
        $this->channel->exchange_declare(self::EXCHANGE, 'topic', false, true, false);
    }

    /**
     * Kick off the saga by publishing order.created.
     */
    public function startOrderSaga(array $orderData): void
    {
        $this->publish('order.created', $orderData);
    }

    /**
     * Compensation: release reserved inventory.
     */
    public function compensateInventory(array $data): void
    {
        $this->publish('inventory.release', $data);
    }

    private function publish(string $routingKey, array $payload): void
    {
        $msg = new AMQPMessage(
            json_encode($payload),
            [
                'delivery_mode'   => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'content_type'    => 'application/json',
            ]
        );
        $this->channel->basic_publish($msg, self::EXCHANGE, $routingKey);
        \Log::info("[order-service] Published {$routingKey}", $payload);
    }

    public function __destruct()
    {
        try {
            $this->channel->close();
            $this->connection->close();
        } catch (\Throwable $e) {
            // Ignore cleanup errors
        }
    }
}
