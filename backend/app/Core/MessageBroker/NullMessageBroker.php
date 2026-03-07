<?php

namespace App\Core\MessageBroker;

use Illuminate\Support\Facades\Log;

class NullMessageBroker implements MessageBrokerInterface
{
    public function publish(string $topic, array $message): void
    {
        Log::info("MessageBroker[NullBroker] publish to '{$topic}'", $message);
    }

    public function subscribe(string $topic, callable $handler): void
    {
        Log::info("MessageBroker[NullBroker] subscribe to '{$topic}'");
    }

    public function acknowledge(string $messageId): void
    {
        Log::info("MessageBroker[NullBroker] acknowledge '{$messageId}'");
    }
}
