<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\SuspiciousActivityDetected;
use App\Events\TokenRefreshed;
use App\Events\UserLoggedIn;
use App\Events\UserLoggedOut;
use App\Listeners\PublishAuthEventToOutbox;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserLoggedIn::class => [
            PublishAuthEventToOutbox::class . '@handleUserLoggedIn',
        ],
        UserLoggedOut::class => [
            PublishAuthEventToOutbox::class . '@handleUserLoggedOut',
        ],
        TokenRefreshed::class => [
            PublishAuthEventToOutbox::class . '@handleTokenRefreshed',
        ],
        SuspiciousActivityDetected::class => [
            PublishAuthEventToOutbox::class . '@handleSuspiciousActivity',
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
