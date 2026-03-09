<?php

declare(strict_types=1);

namespace Shared\Contracts\Messaging;

/**
 * Message Broker Interface.
 *
 * Pluggable interface allowing different message broker implementations
 * (RabbitMQ, Kafka, etc.) without changing consumer code.
 */
interface MessageBrokerInterface
{
    /**
     * Publish a message to a topic/exchange/queue.
     *
     * @param string               $topic   Topic, exchange, or queue name
     * @param array<string, mixed> $message Message payload
     * @param array<string, mixed> $options Broker-specific options (routing key, headers, etc.)
     * @return bool
     */
    public function publish(string $topic, array $message, array $options = []): bool;

    /**
     * Subscribe to a topic/queue and process messages with the given handler.
     *
     * @param string   $topic   Topic, exchange, or queue name
     * @param callable $handler Callback to process each message: function(array $message): void
     * @param array<string, mixed> $options Broker-specific subscription options
     * @return void
     */
    public function subscribe(string $topic, callable $handler, array $options = []): void;

    /**
     * Acknowledge successful message processing.
     *
     * @param mixed $message Raw message object from the broker
     * @return void
     */
    public function acknowledge(mixed $message): void;

    /**
     * Reject/nack a message (will be requeued or dead-lettered).
     *
     * @param mixed $message  Raw message object from the broker
     * @param bool  $requeue  Whether to requeue the message
     * @return void
     */
    public function reject(mixed $message, bool $requeue = false): void;

    /**
     * Check if the broker connection is healthy.
     *
     * @return bool
     */
    public function isConnected(): bool;

    /**
     * Get broker driver name (e.g., 'rabbitmq', 'kafka').
     *
     * @return string
     */
    public function getDriver(): string;
}
