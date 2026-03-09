<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Inventory\Repositories\CategoryRepositoryInterface;
use App\Domain\Inventory\Repositories\ProductRepositoryInterface;
use App\Domain\Inventory\Repositories\StockMovementRepositoryInterface;
use App\Domain\Inventory\Repositories\WarehouseRepositoryInterface;
use App\Domain\Inventory\Services\StockManagementService;
use App\Infrastructure\Repositories\EloquentCategoryRepository;
use App\Infrastructure\Repositories\EloquentProductRepository;
use App\Infrastructure\Repositories\EloquentStockMovementRepository;
use App\Infrastructure\Repositories\EloquentWarehouseRepository;
use App\Shared\Contracts\MessageBrokerInterface;
use App\Shared\Messaging\MessageBrokerFactory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->bind(StockMovementRepositoryInterface::class, EloquentStockMovementRepository::class);
        $this->app->bind(WarehouseRepositoryInterface::class, EloquentWarehouseRepository::class);

        // Message broker (singleton)
        $this->app->singleton(MessageBrokerInterface::class, function ($app) {
            return MessageBrokerFactory::create(
                driver: config('queue.default', 'rabbitmq'),
                config: config('queue.connections.' . config('queue.default'), []),
            );
        });

        // Domain service
        $this->app->bind(StockManagementService::class, function ($app) {
            return new StockManagementService(
                productRepository: $app->make(ProductRepositoryInterface::class),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
