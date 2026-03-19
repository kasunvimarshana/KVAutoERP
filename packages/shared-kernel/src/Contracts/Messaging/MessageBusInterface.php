<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\Contracts\Messaging;

use KvEnterprise\SharedKernel\Events\DomainEvent;

/**
 * Contract for the platform message bus.
 *
 * Abstracts the underlying broker (Kafka, RabbitMQ, etc.) so that
 * producers and consumers remain decoupled from the transport layer.
 * Implementations must guarantee at-least-once delivery semantics.
 */
interface MessageBusInterface
{
    /**
     * Publish a message to the specified topic/exchange.
     *
     * @param  string                $topic    Destination topic or routing key.
     * @param  array<string, mixed>  $payload  Serialisable message body.
     * @param  array<string, mixed>  $headers  Optional broker-level headers (e.g. correlation-id).
     * @return bool                             True when the broker has accepted the message.
     */
    public function publish(string $topic, array $payload, array $headers = []): bool;

    /**
     * Register a handler callback for messages arriving on a topic.
     *
     * The callback receives the decoded message payload and headers.
     * Subscribing is idempotent – registering the same handler twice
     * for the same topic must not result in duplicate processing.
     *
     * @param  string    $topic    Topic or queue name to subscribe to.
     * @param  callable  $handler  Handler invoked as handler(array $payload, array $headers): void.
     * @return void
     */
    public function subscribe(string $topic, callable $handler): void;

    /**
     * Publish a message after a specific delay (scheduled messaging).
     *
     * @param  string                $topic    Destination topic or routing key.
     * @param  array<string, mixed>  $payload  Serialisable message body.
     * @param  int                   $delay    Delay in seconds before the message is delivered.
     * @param  array<string, mixed>  $headers  Optional broker-level headers.
     * @return bool                             True when the broker has accepted the message.
     */
    public function publishDelayed(string $topic, array $payload, int $delay, array $headers = []): bool;

    /**
     * Acknowledge successful processing of a message.
     *
     * Must be called after a subscribed handler completes successfully
     * to prevent the broker from redelivering the message.
     *
     * @param  string  $messageId  The broker-assigned message identifier.
     * @return void
     */
    public function acknowledge(string $messageId): void;

    /**
     * Negatively acknowledge a message, triggering redelivery or dead-lettering.
     *
     * @param  string  $messageId  The broker-assigned message identifier.
     * @param  bool    $requeue    Whether to requeue the message for retry.
     * @return void
     */
    public function reject(string $messageId, bool $requeue = true): void;
}
