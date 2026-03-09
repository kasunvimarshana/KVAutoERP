<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Infrastructure\Messaging\InventoryEventConsumer;
use Illuminate\Console\Command;

class ConsumeInventoryEvents extends Command
{
    protected $signature   = 'inventory:consume-events {--queue=order.events : Queue/topic to consume}';
    protected $description = 'Start the inventory event consumer loop';

    private bool $shouldStop = false;

    public function handle(InventoryEventConsumer $consumer): int
    {
        $queue = $this->option('queue');
        $this->info("[inventory:consume-events] Starting on queue: {$queue}");

        // Register SIGTERM/SIGINT handlers for graceful shutdown.
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, function () use ($consumer): void {
                $this->info('[inventory:consume-events] SIGTERM received — shutting down.');
                $consumer->stop();
                $this->shouldStop = true;
            });
            pcntl_signal(SIGINT, function () use ($consumer): void {
                $this->info('[inventory:consume-events] SIGINT received — shutting down.');
                $consumer->stop();
                $this->shouldStop = true;
            });
        }

        try {
            $consumer->consume($queue);
        } catch (\Throwable $e) {
            $this->error('[inventory:consume-events] Fatal error: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info('[inventory:consume-events] Consumer stopped.');
        return self::SUCCESS;
    }
}
