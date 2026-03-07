<?php

namespace App\Infrastructure\Messaging\Contracts;

interface MessageBrokerInterface
{
    /**
     * Publish a message to a topic / exchange / queue.
     *
     * @param  string  $topic    Exchange name (RabbitMQ) or topic name (Kafka)
     * @param  array   $message  Message payload (will be JSON-encoded)
     * @param  array   $options  Driver-specific options (routing_key, headers, …)
     */
    public function publish(string $topic, array $message, array $options = []): void;

    /**
     * Subscribe to a topic / queue and process messages with $handler.
     *
     * @param  string    $topic    Queue name (RabbitMQ) or topic name (Kafka)
     * @param  callable  $handler  Receives the decoded payload array as first argument
     *                             and the raw message object as second argument
     * @param  array     $options  Driver-specific options (prefetch_count, …)
     */
    public function subscribe(string $topic, callable $handler, array $options = []): void;

    /**
     * Acknowledge successful processing of a message.
     */
    public function acknowledge(mixed $message): void;

    /**
     * Reject (and optionally re-queue) a message.
     */
    public function reject(mixed $message, bool $requeue = false): void;

    /**
     * Close the broker connection / channel.
     */
    public function disconnect(): void;

    /**
     * Return whether the broker connection is currently open.
     */
    public function isConnected(): bool;
}
