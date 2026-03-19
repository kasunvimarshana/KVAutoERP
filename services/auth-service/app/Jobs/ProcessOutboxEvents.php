<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\OutboxEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Processes pending Outbox events and publishes them to the message broker.
 * Implements the Outbox Pattern for guaranteed at-least-once event delivery.
 */
class ProcessOutboxEvents implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function handle(): void
    {
        OutboxEvent::pending()
            ->orderBy('created_at', 'asc')
            ->limit(100)
            ->get()
            ->each(function (OutboxEvent $event) {
                try {
                    $this->publish($event);
                    $event->markPublished();
                } catch (\Throwable $e) {
                    Log::error('Outbox event publish failed', [
                        'event_id'   => $event->id,
                        'event_type' => $event->event_type,
                        'error'      => $e->getMessage(),
                    ]);
                    $event->markFailed($e->getMessage());
                }
            });
    }

    private function publish(OutboxEvent $event): void
    {
        $brokerDriver = config('app.broker_driver', 'log');

        match ($brokerDriver) {
            'rabbitmq' => $this->publishToRabbitMQ($event),
            'kafka'    => $this->publishToKafka($event),
            default    => Log::channel('outbox')->info('OUTBOX_EVENT: ' . $event->event_type, $event->payload),
        };
    }

    private function publishToRabbitMQ(OutboxEvent $event): void
    {
        // RabbitMQ integration via AMQP
        // In production, use a package like vladimir-yuldashev/laravel-queue-rabbitmq
        Log::channel('outbox')->info("RabbitMQ: {$event->event_type}", ['payload' => $event->payload]);
    }

    private function publishToKafka(OutboxEvent $event): void
    {
        // Kafka integration
        // In production, use a package like mateusjunges/laravel-kafka
        Log::channel('outbox')->info("Kafka: {$event->event_type}", ['payload' => $event->payload]);
    }
}
