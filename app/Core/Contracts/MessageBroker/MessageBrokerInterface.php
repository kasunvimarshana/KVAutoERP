<?php

declare(strict_types=1);

namespace App\Core\Contracts\MessageBroker;

/**
 * Message Broker Contract.
 *
 * Provides a pluggable interface for different message broker implementations
 * (Kafka, RabbitMQ, Redis Streams, SQS, etc.) without coupling business logic
 * to a specific technology.
 */
interface MessageBrokerInterface
{
    /**
     * Publish a message to the given topic/exchange.
     *
     * @param  string              $topic
     * @param  array<string,mixed> $payload
     * @param  array<string,mixed> $options  Broker-specific options (headers, partition, etc.)
     * @return bool
     */
    public function publish(string $topic, array $payload, array $options = []): bool;

    /**
     * Subscribe to a topic and process messages via the provided callback.
     *
     * @param  string             $topic
     * @param  callable           $callback  fn(array $message): void
     * @param  array<string,mixed> $options
     * @return void
     */
    public function subscribe(string $topic, callable $callback, array $options = []): void;

    /**
     * Acknowledge a message to prevent redelivery.
     *
     * @param  mixed $messageId
     * @return void
     */
    public function acknowledge(mixed $messageId): void;

    /**
     * Negatively acknowledge a message (NACK), optionally re-queuing it.
     *
     * @param  mixed $messageId
     * @param  bool  $requeue
     * @return void
     */
    public function reject(mixed $messageId, bool $requeue = false): void;

    /**
     * Check the health/connectivity of the broker.
     *
     * @return bool
     */
    public function isHealthy(): bool;
}
