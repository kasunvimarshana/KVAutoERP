<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Inventory\Repositories\Interfaces\InventoryRepositoryInterface;
use App\Infrastructure\Repositories\InventoryRepository;
use App\Infrastructure\Webhooks\WebhookDispatcher;
use Illuminate\Support\ServiceProvider;

/**
 * Repository Service Provider for Inventory Service.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    public array $bindings = [
        InventoryRepositoryInterface::class => InventoryRepository::class,
    ];

    public function register(): void
    {
        foreach ($this->bindings as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }

        // Webhook dispatcher as a singleton
        $this->app->singleton(WebhookDispatcher::class);
    }
}
