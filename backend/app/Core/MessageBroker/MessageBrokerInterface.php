<?php

namespace App\Core\MessageBroker;

interface MessageBrokerInterface
{
    public function publish(string $topic, array $message): void;

    public function subscribe(string $topic, callable $handler): void;

    public function acknowledge(string $messageId): void;
}
