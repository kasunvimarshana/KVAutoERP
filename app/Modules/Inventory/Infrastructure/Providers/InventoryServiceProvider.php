<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Inventory\Application\Contracts\AddValuationLayerServiceInterface;
use Modules\Inventory\Application\Contracts\AllocateStockServiceInterface;
use Modules\Inventory\Application\Contracts\BatchServiceInterface;
use Modules\Inventory\Application\Contracts\ConsumeValuationLayersServiceInterface;
use Modules\Inventory\Application\Contracts\CreateCycleCountServiceInterface;
use Modules\Inventory\Application\Contracts\InventoryManagerServiceInterface;
use Modules\Inventory\Application\Contracts\ReconcileInventoryServiceInterface;
use Modules\Inventory\Application\Contracts\ReservationServiceInterface;
use Modules\Inventory\Application\Contracts\StockMovementServiceInterface;
use Modules\Inventory\Application\Contracts\StockServiceInterface;
use Modules\Inventory\Application\Services\AddValuationLayerService;
use Modules\Inventory\Application\Services\AllocateStockService;
use Modules\Inventory\Application\Services\BatchService;
use Modules\Inventory\Application\Services\ConsumeValuationLayersService;
use Modules\Inventory\Application\Services\CreateCycleCountService;
use Modules\Inventory\Application\Services\InventoryManagerService;
use Modules\Inventory\Application\Services\ReconcileInventoryService;
use Modules\Inventory\Application\Services\ReservationService;
use Modules\Inventory\Application\Services\StockMovementService;
use Modules\Inventory\Application\Services\StockService;
use Modules\Inventory\Domain\RepositoryInterfaces\BatchRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\CycleCountLineRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\CycleCountRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\InventoryAdjustmentRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockItemRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockMovementRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockReservationRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\ValuationLayerRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\BatchModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\CycleCountLineModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\CycleCountModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryAdjustmentModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockItemModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockMovementModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockReservationModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\ValuationLayerModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentBatchRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentCycleCountLineRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentCycleCountRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentInventoryAdjustmentRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentStockItemRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentStockMovementRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentStockReservationRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentValuationLayerRepository;

class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories
        $this->app->bind(StockItemRepositoryInterface::class, function ($app) {
            return new EloquentStockItemRepository($app->make(StockItemModel::class));
        });

        $this->app->bind(StockMovementRepositoryInterface::class, function ($app) {
            return new EloquentStockMovementRepository($app->make(StockMovementModel::class));
        });

        $this->app->bind(BatchRepositoryInterface::class, function ($app) {
            return new EloquentBatchRepository($app->make(BatchModel::class));
        });

        $this->app->bind(StockReservationRepositoryInterface::class, function ($app) {
            return new EloquentStockReservationRepository($app->make(StockReservationModel::class));
        });

        $this->app->bind(ValuationLayerRepositoryInterface::class, function ($app) {
            return new EloquentValuationLayerRepository($app->make(ValuationLayerModel::class));
        });

        $this->app->bind(CycleCountRepositoryInterface::class, function ($app) {
            return new EloquentCycleCountRepository($app->make(CycleCountModel::class));
        });

        $this->app->bind(CycleCountLineRepositoryInterface::class, function ($app) {
            return new EloquentCycleCountLineRepository($app->make(CycleCountLineModel::class));
        });

        $this->app->bind(InventoryAdjustmentRepositoryInterface::class, function ($app) {
            return new EloquentInventoryAdjustmentRepository($app->make(InventoryAdjustmentModel::class));
        });

        // Services
        $this->app->bind(StockServiceInterface::class, function ($app) {
            return new StockService($app->make(StockItemRepositoryInterface::class));
        });

        $this->app->bind(AddValuationLayerServiceInterface::class, function ($app) {
            return new AddValuationLayerService($app->make(ValuationLayerRepositoryInterface::class));
        });

        $this->app->bind(ConsumeValuationLayersServiceInterface::class, function ($app) {
            return new ConsumeValuationLayersService($app->make(ValuationLayerRepositoryInterface::class));
        });

        $this->app->bind(StockMovementServiceInterface::class, function ($app) {
            return new StockMovementService(
                $app->make(StockMovementRepositoryInterface::class),
                $app->make(StockItemRepositoryInterface::class),
                $app->make(AddValuationLayerServiceInterface::class),
            );
        });

        $this->app->bind(BatchServiceInterface::class, function ($app) {
            return new BatchService($app->make(BatchRepositoryInterface::class));
        });

        $this->app->bind(ReservationServiceInterface::class, function ($app) {
            return new ReservationService(
                $app->make(StockReservationRepositoryInterface::class),
                $app->make(StockItemRepositoryInterface::class),
            );
        });

        $this->app->bind(AllocateStockServiceInterface::class, function ($app) {
            return new AllocateStockService($app->make(BatchRepositoryInterface::class));
        });

        $this->app->bind(ReconcileInventoryServiceInterface::class, function ($app) {
            return new ReconcileInventoryService(
                $app->make(CycleCountRepositoryInterface::class),
                $app->make(CycleCountLineRepositoryInterface::class),
                $app->make(StockMovementRepositoryInterface::class),
                $app->make(StockItemRepositoryInterface::class),
            );
        });

        $this->app->bind(CreateCycleCountServiceInterface::class, function ($app) {
            return new CreateCycleCountService(
                $app->make(CycleCountRepositoryInterface::class),
                $app->make(CycleCountLineRepositoryInterface::class),
                $app->make(StockItemRepositoryInterface::class),
            );
        });

        $this->app->bind(InventoryManagerServiceInterface::class, function ($app) {
            return new InventoryManagerService(
                $app->make(StockMovementServiceInterface::class),
                $app->make(AddValuationLayerServiceInterface::class),
                $app->make(AllocateStockServiceInterface::class),
            );
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
