<?php

namespace App\Shared\MessageBroker\Contracts;

interface MessageBrokerInterface
{
    public function publish(string $topic, array $message, array $options = []): bool;
    public function subscribe(string $topic, callable $handler, array $options = []): void;
    public function publishBatch(string $topic, array $messages): bool;
    public function ack(string $messageId): void;
    public function nack(string $messageId, bool $requeue = true): void;
    public function isConnected(): bool;
    public function disconnect(): void;
}
