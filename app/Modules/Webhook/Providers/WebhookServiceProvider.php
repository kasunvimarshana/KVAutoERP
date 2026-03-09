<?php

declare(strict_types=1);

namespace App\Modules\Webhook\Providers;

use App\Modules\Webhook\Application\Services\WebhookService;
use App\Modules\Webhook\Infrastructure\Repositories\WebhookRepository;
use Illuminate\Support\ServiceProvider;

class WebhookServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WebhookRepository::class);
        $this->app->singleton(WebhookService::class);
    }

    public function boot(): void {}
}
