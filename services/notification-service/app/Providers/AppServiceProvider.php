<?php

namespace App\Providers;

use App\Application\Services\NotificationService;
use App\Application\Services\WebhookService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WebhookService::class, fn () => new WebhookService());

        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService($app->make(WebhookService::class));
        });
    }

    public function boot(): void
    {
        //
    }
}
