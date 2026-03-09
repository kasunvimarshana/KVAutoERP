<?php

declare(strict_types=1);

namespace App\Contracts\Messaging;

interface MessageBrokerInterface
{
    /**
     * Publish a message to a queue/exchange.
     */
    public function publish(string $exchange, string $routingKey, array $message, array $options = []): bool;

    /**
     * Subscribe to a queue and process messages.
     *
     * @param callable(array $message): void $handler
     */
    public function subscribe(string $queue, callable $handler, array $options = []): void;

    /**
     * Acknowledge a message was processed successfully.
     */
    public function acknowledge(mixed $message): void;

    /**
     * Reject and optionally requeue a message.
     */
    public function reject(mixed $message, bool $requeue = false): void;

    /**
     * Check if broker connection is healthy.
     */
    public function isConnected(): bool;
}
