<?php

declare(strict_types=1);

namespace App\Infrastructure\Messaging;

/**
 * Message Broker Interface.
 *
 * Pluggable abstraction for message broker implementations.
 * Supports RabbitMQ, Kafka, and any future broker.
 */
interface MessageBrokerInterface
{
    /**
     * Publish a message to a topic/exchange.
     *
     * @param  string               $topic
     * @param  array<string, mixed> $message
     * @param  array<string, mixed> $options
     * @return bool
     */
    public function publish(string $topic, array $message, array $options = []): bool;

    /**
     * Subscribe to a topic and process messages.
     *
     * @param  string              $topic
     * @param  callable            $handler  function(array $message, mixed $rawMessage): void
     * @param  array<string, mixed> $options
     * @return void
     */
    public function subscribe(string $topic, callable $handler, array $options = []): void;

    /**
     * Acknowledge a message.
     *
     * @param  mixed $message
     * @return void
     */
    public function acknowledge(mixed $message): void;

    /**
     * Reject/nack a message.
     *
     * @param  mixed $message
     * @param  bool  $requeue
     * @return void
     */
    public function reject(mixed $message, bool $requeue = false): void;

    /**
     * Check connection health.
     *
     * @return bool
     */
    public function isConnected(): bool;

    /**
     * Get driver name.
     *
     * @return string
     */
    public function getDriver(): string;
}
