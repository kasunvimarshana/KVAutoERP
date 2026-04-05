<?php

declare(strict_types=1);

namespace Modules\Warehouse\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Warehouse\Application\Contracts\LocationServiceInterface;
use Modules\Warehouse\Application\Contracts\WarehouseServiceInterface;
use Modules\Warehouse\Application\Services\LocationService;
use Modules\Warehouse\Application\Services\WarehouseService;
use Modules\Warehouse\Domain\RepositoryInterfaces\LocationRepositoryInterface;
use Modules\Warehouse\Domain\RepositoryInterfaces\WarehouseRepositoryInterface;
use Modules\Warehouse\Infrastructure\Persistence\Eloquent\Models\LocationModel;
use Modules\Warehouse\Infrastructure\Persistence\Eloquent\Models\WarehouseModel;
use Modules\Warehouse\Infrastructure\Persistence\Eloquent\Repositories\EloquentLocationRepository;
use Modules\Warehouse\Infrastructure\Persistence\Eloquent\Repositories\EloquentWarehouseRepository;

class WarehouseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(WarehouseRepositoryInterface::class, function ($app) {
            return new EloquentWarehouseRepository($app->make(WarehouseModel::class));
        });

        $this->app->bind(LocationRepositoryInterface::class, function ($app) {
            return new EloquentLocationRepository($app->make(LocationModel::class));
        });

        $this->app->bind(WarehouseServiceInterface::class, function ($app) {
            return new WarehouseService($app->make(WarehouseRepositoryInterface::class));
        });

        $this->app->bind(LocationServiceInterface::class, function ($app) {
            return new LocationService($app->make(LocationRepositoryInterface::class));
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
