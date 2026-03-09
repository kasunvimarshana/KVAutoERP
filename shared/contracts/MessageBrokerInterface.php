<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

/**
 * Message Broker Contract.
 *
 * Abstracts both RabbitMQ and Kafka so that application code remains
 * transport-agnostic.  Implementations live in App\Shared\Messaging.
 */
interface MessageBrokerInterface
{
    /**
     * Publish a single message to the given topic / exchange / queue.
     *
     * @param  string              $topic    Exchange name (AMQP) or topic name (Kafka).
     * @param  array<string,mixed> $message  Message payload (will be JSON-encoded internally).
     * @param  array<string,mixed> $options  Driver-specific options:
     *                                        - routing_key (AMQP)
     *                                        - headers     (AMQP / Kafka)
     *                                        - priority    (AMQP)
     *                                        - delay_ms    (delayed exchange)
     *                                        - partition   (Kafka)
     *                                        - key         (Kafka message key)
     * @return bool                          True when the broker confirmed receipt.
     */
    public function publish(string $topic, array $message, array $options = []): bool;

    /**
     * Subscribe to a topic/queue and process messages with the given handler.
     *
     * The method blocks (or registers a callback depending on driver) and
     * calls $handler for every inbound message.
     *
     * @param  string              $topic    Queue name (AMQP) or topic (Kafka).
     * @param  callable            $handler  fn(mixed $rawMessage, array $decoded): void
     * @param  array<string,mixed> $options  Driver-specific options:
     *                                        - consumer_tag  (AMQP)
     *                                        - no_ack        (AMQP, default false)
     *                                        - exclusive     (AMQP)
     *                                        - timeout_ms    (Kafka)
     *                                        - max_messages  (stop after N)
     * @return void
     */
    public function subscribe(string $topic, callable $handler, array $options = []): void;

    /**
     * Atomically publish multiple messages to the same topic.
     *
     * @param  string                    $topic     Target topic / exchange.
     * @param  array<int, array<string,mixed>> $messages  List of payloads.
     * @return bool                                True when all messages were accepted.
     */
    public function publishBatch(string $topic, array $messages): bool;

    /**
     * Acknowledge successful processing of a message.
     *
     * @param  mixed  $message  The raw message object returned by the driver.
     * @return void
     */
    public function acknowledge(mixed $message): void;

    /**
     * Reject / nack a message, optionally re-queueing it.
     *
     * @param  mixed  $message  Raw broker message object.
     * @param  bool   $requeue  Whether to place the message back on the queue.
     * @return void
     */
    public function reject(mixed $message, bool $requeue = false): void;

    /**
     * Return a snapshot of the current connection health.
     *
     * @return array{
     *     connected: bool,
     *     driver: string,
     *     host: string,
     *     latency_ms: float,
     *     details: array<string,mixed>
     * }
     */
    public function getConnectionStatus(): array;
}
