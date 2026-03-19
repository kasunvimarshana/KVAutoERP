<?php

declare(strict_types=1);

namespace App\Console;

use App\Jobs\CleanupExpiredAuthData;
use App\Jobs\ProcessOutboxEvents;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Process outbox events every minute via the scheduler.
        // For near-real-time delivery, Horizon/queue workers process jobs immediately
        // when dispatched. This scheduler entry provides a reliable fallback.
        $schedule->job(ProcessOutboxEvents::class)->everyMinute();

        // Clean up expired sessions and token revocations daily at 3 AM
        $schedule->job(CleanupExpiredAuthData::class)->dailyAt('03:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}
