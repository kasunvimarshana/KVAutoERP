<?php

declare(strict_types=1);

namespace Saas\Contracts\Messaging;

/**
 * Pluggable message broker contract.
 *
 * Implementations may target RabbitMQ, Amazon SQS/SNS, Google Pub/Sub, Apache
 * Kafka, or any other transport.  Services depend only on this interface so
 * that the underlying broker can be swapped or mocked in tests without changing
 * application code.
 */
interface MessageBrokerInterface
{
    /**
     * Publishes a message to the specified exchange.
     *
     * @param string               $exchange   Exchange (or topic) name to publish to.
     * @param string               $routingKey Routing / subject key used by the broker to
     *                                         dispatch the message to bound queues.
     * @param array<string, mixed> $message    Message body; will be serialised by the implementation.
     * @param array<string, mixed> $options    Driver-specific publish options (e.g. `persistent`,
     *                                         `priority`, `expiration`, `headers`).
     *
     * @return bool `true` when the message was accepted by the broker.
     */
    public function publish(string $exchange, string $routingKey, array $message, array $options = []): bool;

    /**
     * Registers a handler that is invoked for every message delivered to the queue.
     *
     * This method MUST block (run the consumer loop) until the process is
     * signalled to stop, or until the implementation's internal logic decides
     * to stop consuming.
     *
     * @param string               $queue   Queue name to consume from.
     * @param callable             $handler Callback receiving the raw broker message as its sole argument.
     * @param array<string, mixed> $options Driver-specific consumer options (e.g. `prefetchCount`, `exclusive`).
     */
    public function subscribe(string $queue, callable $handler, array $options = []): void;

    /**
     * Positively acknowledges a message, removing it from the queue.
     *
     * @param mixed $message The raw broker message object returned to the consumer handler.
     */
    public function acknowledge(mixed $message): void;

    /**
     * Negatively acknowledges a message, optionally re-queuing it.
     *
     * @param mixed $message The raw broker message object returned to the consumer handler.
     * @param bool  $requeue When `true` the broker will re-deliver the message; when `false`
     *                       it is discarded (or dead-lettered, depending on broker configuration).
     */
    public function reject(mixed $message, bool $requeue = false): void;

    /**
     * Declares a queue, creating it if it does not already exist.
     *
     * @param string               $queue   Queue name.
     * @param array<string, mixed> $options Driver-specific queue arguments (e.g. `durable`, `ttl`,
     *                                      `deadLetterExchange`, `maxLength`).
     */
    public function createQueue(string $queue, array $options = []): void;

    /**
     * Declares an exchange, creating it if it does not already exist.
     *
     * @param string               $exchange Exchange name.
     * @param string               $type     Exchange type: `direct`, `fanout`, `topic`, or `headers`.
     * @param array<string, mixed> $options  Driver-specific exchange arguments (e.g. `durable`, `autoDelete`).
     */
    public function createExchange(string $exchange, string $type = 'topic', array $options = []): void;
}
