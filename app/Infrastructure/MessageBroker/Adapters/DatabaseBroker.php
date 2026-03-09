<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageBroker\Adapters;

use App\Core\Contracts\MessageBroker\MessageBrokerInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

/**
 * DatabaseBroker (default / fallback)
 *
 * Implements the MessageBrokerInterface using Laravel's built-in
 * Queue system as the underlying transport.  This adapter ships out of
 * the box so the application is always runnable without external services.
 *
 * Swap for KafkaBroker or RabbitMqBroker at runtime via the
 * RuntimeConfigurationService (no restart required).
 */
class DatabaseBroker implements MessageBrokerInterface
{
    public function publish(string $topic, array $payload, array $options = []): bool
    {
        try {
            // Push as a generic queued event using Laravel's queue
            Queue::pushRaw(
                json_encode([
                    'topic'   => $topic,
                    'payload' => $payload,
                    'options' => $options,
                ]),
                $topic
            );

            Log::debug("[MessageBroker:Database] Published to [{$topic}]", compact('payload'));
            return true;
        } catch (\Throwable $e) {
            Log::error("[MessageBroker:Database] Publish failed: {$e->getMessage()}", compact('topic'));
            return false;
        }
    }

    public function subscribe(string $topic, callable $callback, array $options = []): void
    {
        // Subscription is handled by Laravel queue workers listening to the queue named $topic
        Log::debug("[MessageBroker:Database] Subscribe registered for [{$topic}]");
    }

    public function acknowledge(mixed $messageId): void
    {
        // Database queue jobs are auto-acknowledged on successful completion
    }

    public function reject(mixed $messageId, bool $requeue = false): void
    {
        // Rejection / dead-lettering is managed by Laravel's failed-jobs mechanism
    }

    public function isHealthy(): bool
    {
        try {
            \DB::connection()->getPdo();
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
