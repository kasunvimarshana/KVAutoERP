<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('auth:inspire', function (): void {
    /** @var \Illuminate\Console\Command $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
*/

Schedule::command('auth:purge-expired-tokens')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('auth:purge-audit-logs')
    ->daily()
    ->withoutOverlapping()
    ->runInBackground();
