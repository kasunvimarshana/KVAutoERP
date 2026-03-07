<?php

namespace App\Providers;

use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Events\ProductDeleted;
use App\Modules\Product\Events\ProductUpdated;
use App\Modules\Product\Listeners\HandleProductDeletedEvent;
use App\Modules\Product\Listeners\SendProductCreatedNotification;
use App\Modules\Product\Repositories\Interfaces\ProductRepositoryInterface;
use App\Modules\Product\Repositories\ProductRepository;
use App\Services\MessageBrokerService;
use App\Services\WebhookService;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ProductCreated::class => [
            SendProductCreatedNotification::class,
        ],
        ProductDeleted::class => [
            HandleProductDeletedEvent::class,
        ],
    ];

    public function register(): void
    {
        parent::register();

        // Bind repository interface to implementation
        $this->app->bind(
            ProductRepositoryInterface::class,
            ProductRepository::class
        );

        // Singleton services
        $this->app->singleton(MessageBrokerService::class);
        $this->app->singleton(WebhookService::class);
    }

    public function boot(): void
    {
        parent::boot();
    }
}
