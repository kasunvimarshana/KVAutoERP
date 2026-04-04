<?php

declare(strict_types=1);

namespace Modules\Warehouse\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Warehouse\Application\Contracts\CreateLocationServiceInterface;
use Modules\Warehouse\Application\Contracts\CreateWarehouseServiceInterface;
use Modules\Warehouse\Application\Contracts\DeleteLocationServiceInterface;
use Modules\Warehouse\Application\Contracts\DeleteWarehouseServiceInterface;
use Modules\Warehouse\Application\Contracts\GetLocationTreeServiceInterface;
use Modules\Warehouse\Application\Contracts\GetWarehouseServiceInterface;
use Modules\Warehouse\Application\Contracts\ListWarehousesServiceInterface;
use Modules\Warehouse\Application\Contracts\MoveLocationServiceInterface;
use Modules\Warehouse\Application\Contracts\UpdateLocationServiceInterface;
use Modules\Warehouse\Application\Contracts\UpdateWarehouseServiceInterface;
use Modules\Warehouse\Application\Services\CreateLocationService;
use Modules\Warehouse\Application\Services\CreateWarehouseService;
use Modules\Warehouse\Application\Services\DeleteLocationService;
use Modules\Warehouse\Application\Services\DeleteWarehouseService;
use Modules\Warehouse\Application\Services\GetLocationTreeService;
use Modules\Warehouse\Application\Services\GetWarehouseService;
use Modules\Warehouse\Application\Services\ListWarehousesService;
use Modules\Warehouse\Application\Services\MoveLocationService;
use Modules\Warehouse\Application\Services\UpdateLocationService;
use Modules\Warehouse\Application\Services\UpdateWarehouseService;
use Modules\Warehouse\Domain\Repositories\WarehouseLocationRepositoryInterface;
use Modules\Warehouse\Domain\Repositories\WarehouseRepositoryInterface;
use Modules\Warehouse\Infrastructure\Persistence\Models\WarehouseLocationClosureModel;
use Modules\Warehouse\Infrastructure\Persistence\Models\WarehouseLocationModel;
use Modules\Warehouse\Infrastructure\Persistence\Models\WarehouseModel;
use Modules\Warehouse\Infrastructure\Persistence\Repositories\EloquentWarehouseLocationRepository;
use Modules\Warehouse\Infrastructure\Persistence\Repositories\EloquentWarehouseRepository;

class WarehouseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(WarehouseRepositoryInterface::class, function ($app) {
            return new EloquentWarehouseRepository($app->make(WarehouseModel::class));
        });

        $this->app->bind(WarehouseLocationRepositoryInterface::class, function ($app) {
            return new EloquentWarehouseLocationRepository(
                $app->make(WarehouseLocationModel::class),
                $app->make(WarehouseLocationClosureModel::class),
            );
        });

        $this->app->bind(CreateWarehouseServiceInterface::class, function ($app) {
            return new CreateWarehouseService($app->make(WarehouseRepositoryInterface::class));
        });

        $this->app->bind(UpdateWarehouseServiceInterface::class, function ($app) {
            return new UpdateWarehouseService($app->make(WarehouseRepositoryInterface::class));
        });

        $this->app->bind(DeleteWarehouseServiceInterface::class, function ($app) {
            return new DeleteWarehouseService($app->make(WarehouseRepositoryInterface::class));
        });

        $this->app->bind(GetWarehouseServiceInterface::class, function ($app) {
            return new GetWarehouseService($app->make(WarehouseRepositoryInterface::class));
        });

        $this->app->bind(ListWarehousesServiceInterface::class, function ($app) {
            return new ListWarehousesService($app->make(WarehouseRepositoryInterface::class));
        });

        $this->app->bind(CreateLocationServiceInterface::class, function ($app) {
            return new CreateLocationService($app->make(WarehouseLocationRepositoryInterface::class));
        });

        $this->app->bind(UpdateLocationServiceInterface::class, function ($app) {
            return new UpdateLocationService($app->make(WarehouseLocationRepositoryInterface::class));
        });

        $this->app->bind(DeleteLocationServiceInterface::class, function ($app) {
            return new DeleteLocationService($app->make(WarehouseLocationRepositoryInterface::class));
        });

        $this->app->bind(MoveLocationServiceInterface::class, function ($app) {
            return new MoveLocationService($app->make(WarehouseLocationRepositoryInterface::class));
        });

        $this->app->bind(GetLocationTreeServiceInterface::class, function ($app) {
            return new GetLocationTreeService($app->make(WarehouseLocationRepositoryInterface::class));
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        Route::group([], function () {
            $routeFile = __DIR__.'/../../routes/api.php';
            if (file_exists($routeFile)) {
                require $routeFile;
            }
        });
    }
}
