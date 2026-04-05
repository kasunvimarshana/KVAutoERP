<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Inventory\Application\Contracts\AddValuationLayerServiceInterface;
use Modules\Inventory\Application\Contracts\AllocateStockServiceInterface;
use Modules\Inventory\Application\Contracts\ConsumeValuationLayersServiceInterface;
use Modules\Inventory\Application\Contracts\CreateCycleCountServiceInterface;
use Modules\Inventory\Application\Contracts\InventoryManagerServiceInterface;
use Modules\Inventory\Application\Contracts\ReconcileInventoryServiceInterface;
use Modules\Inventory\Application\Contracts\StockServiceInterface;
use Modules\Inventory\Application\Contracts\WarehouseLocationServiceInterface;
use Modules\Inventory\Application\Contracts\WarehouseServiceInterface;
use Modules\Inventory\Application\Services\AddValuationLayerService;
use Modules\Inventory\Application\Services\AllocateStockService;
use Modules\Inventory\Application\Services\ConsumeValuationLayersService;
use Modules\Inventory\Application\Services\CreateCycleCountService;
use Modules\Inventory\Application\Services\InventoryManagerService;
use Modules\Inventory\Application\Services\ReconcileInventoryService;
use Modules\Inventory\Application\Services\StockService;
use Modules\Inventory\Application\Services\WarehouseLocationService;
use Modules\Inventory\Application\Services\WarehouseService;
use Modules\Inventory\Domain\RepositoryInterfaces\CycleCountRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockAdjustmentRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockItemRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockMovementRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\ValuationLayerRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\WarehouseLocationRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\WarehouseRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\CycleCountLineModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\CycleCountModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockAdjustmentLineModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockAdjustmentModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockItemModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockMovementModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\ValuationLayerModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\WarehouseLocationModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\WarehouseModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentCycleCountRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentStockAdjustmentRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentStockItemRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentStockMovementRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentValuationLayerRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentWarehouseLocationRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentWarehouseRepository;

class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(
            WarehouseRepositoryInterface::class,
            fn ($app) => new EloquentWarehouseRepository(
                $app->make(WarehouseModel::class),
            ),
        );

        $this->app->bind(
            WarehouseLocationRepositoryInterface::class,
            fn ($app) => new EloquentWarehouseLocationRepository(
                $app->make(WarehouseLocationModel::class),
            ),
        );

        $this->app->bind(
            StockItemRepositoryInterface::class,
            fn ($app) => new EloquentStockItemRepository(
                $app->make(StockItemModel::class),
            ),
        );

        $this->app->bind(
            StockMovementRepositoryInterface::class,
            fn ($app) => new EloquentStockMovementRepository(
                $app->make(StockMovementModel::class),
            ),
        );

        $this->app->bind(
            ValuationLayerRepositoryInterface::class,
            fn ($app) => new EloquentValuationLayerRepository(
                $app->make(ValuationLayerModel::class),
            ),
        );

        $this->app->bind(
            StockAdjustmentRepositoryInterface::class,
            fn ($app) => new EloquentStockAdjustmentRepository(
                $app->make(StockAdjustmentModel::class),
                $app->make(StockAdjustmentLineModel::class),
            ),
        );

        $this->app->bind(
            CycleCountRepositoryInterface::class,
            fn ($app) => new EloquentCycleCountRepository(
                $app->make(CycleCountModel::class),
                $app->make(CycleCountLineModel::class),
            ),
        );

        // Service bindings
        $this->app->bind(
            WarehouseServiceInterface::class,
            fn ($app) => new WarehouseService(
                $app->make(WarehouseRepositoryInterface::class),
            ),
        );

        $this->app->bind(
            WarehouseLocationServiceInterface::class,
            fn ($app) => new WarehouseLocationService(
                $app->make(WarehouseLocationRepositoryInterface::class),
            ),
        );

        $this->app->bind(
            StockServiceInterface::class,
            fn ($app) => new StockService(
                $app->make(StockItemRepositoryInterface::class),
                $app->make(StockMovementRepositoryInterface::class),
            ),
        );

        $this->app->bind(
            AddValuationLayerServiceInterface::class,
            fn ($app) => new AddValuationLayerService(
                $app->make(ValuationLayerRepositoryInterface::class),
            ),
        );

        $this->app->bind(
            ConsumeValuationLayersServiceInterface::class,
            fn ($app) => new ConsumeValuationLayersService(
                $app->make(ValuationLayerRepositoryInterface::class),
            ),
        );

        $this->app->bind(
            AllocateStockServiceInterface::class,
            fn ($app) => new AllocateStockService(
                $app->make(ValuationLayerRepositoryInterface::class),
            ),
        );

        $this->app->bind(
            ReconcileInventoryServiceInterface::class,
            fn ($app) => new ReconcileInventoryService(
                $app->make(StockAdjustmentRepositoryInterface::class),
                $app->make(StockItemRepositoryInterface::class),
                $app->make(StockMovementRepositoryInterface::class),
            ),
        );

        $this->app->bind(
            CreateCycleCountServiceInterface::class,
            fn ($app) => new CreateCycleCountService(
                $app->make(CycleCountRepositoryInterface::class),
                $app->make(StockItemRepositoryInterface::class),
            ),
        );

        $this->app->bind(
            InventoryManagerServiceInterface::class,
            fn ($app) => new InventoryManagerService(
                $app->make(StockItemRepositoryInterface::class),
                $app->make(StockMovementRepositoryInterface::class),
                $app->make(AddValuationLayerServiceInterface::class),
                $app->make(ConsumeValuationLayersServiceInterface::class),
            ),
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
