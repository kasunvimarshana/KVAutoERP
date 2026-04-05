<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Inventory\Application\Contracts\AddValuationLayerServiceInterface;
use Modules\Inventory\Application\Contracts\AllocateStockServiceInterface;
use Modules\Inventory\Application\Contracts\BatchLotServiceInterface;
use Modules\Inventory\Application\Contracts\ConsumeValuationLayersServiceInterface;
use Modules\Inventory\Application\Contracts\CreateCycleCountServiceInterface;
use Modules\Inventory\Application\Contracts\InventoryManagerServiceInterface;
use Modules\Inventory\Application\Contracts\ReconcileInventoryServiceInterface;
use Modules\Inventory\Application\Contracts\StockMovementServiceInterface;
use Modules\Inventory\Application\Contracts\StockServiceInterface;
use Modules\Inventory\Application\Services\AddValuationLayerService;
use Modules\Inventory\Application\Services\AllocateStockService;
use Modules\Inventory\Application\Services\BatchLotService;
use Modules\Inventory\Application\Services\ConsumeValuationLayersService;
use Modules\Inventory\Application\Services\CreateCycleCountService;
use Modules\Inventory\Application\Services\InventoryManagerService;
use Modules\Inventory\Application\Services\ReconcileInventoryService;
use Modules\Inventory\Application\Services\StockMovementService;
use Modules\Inventory\Application\Services\StockService;
use Modules\Inventory\Domain\RepositoryInterfaces\BatchLotRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\CycleCountRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\InventoryAdjustmentRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockLocationRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockMovementRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockReservationRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\ValuationLayerRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\BatchLotModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\CycleCountLineModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\CycleCountModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryAdjustmentLineModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryAdjustmentModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockLocationModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockMovementModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockReservationModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\ValuationLayerModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentBatchLotRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentCycleCountRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentInventoryAdjustmentRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentStockLocationRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentStockMovementRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentStockRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentStockReservationRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentValuationLayerRepository;

class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories
        $this->app->bind(StockLocationRepositoryInterface::class, fn ($app) => new EloquentStockLocationRepository($app->make(StockLocationModel::class)));
        $this->app->bind(StockRepositoryInterface::class, fn ($app) => new EloquentStockRepository($app->make(StockModel::class)));
        $this->app->bind(StockMovementRepositoryInterface::class, fn ($app) => new EloquentStockMovementRepository($app->make(StockMovementModel::class)));
        $this->app->bind(BatchLotRepositoryInterface::class, fn ($app) => new EloquentBatchLotRepository($app->make(BatchLotModel::class)));
        $this->app->bind(ValuationLayerRepositoryInterface::class, fn ($app) => new EloquentValuationLayerRepository($app->make(ValuationLayerModel::class)));
        $this->app->bind(StockReservationRepositoryInterface::class, fn ($app) => new EloquentStockReservationRepository($app->make(StockReservationModel::class)));
        $this->app->bind(InventoryAdjustmentRepositoryInterface::class, fn ($app) => new EloquentInventoryAdjustmentRepository(
            $app->make(InventoryAdjustmentModel::class),
            $app->make(InventoryAdjustmentLineModel::class),
        ));
        $this->app->bind(CycleCountRepositoryInterface::class, fn ($app) => new EloquentCycleCountRepository(
            $app->make(CycleCountModel::class),
            $app->make(CycleCountLineModel::class),
        ));

        // Services
        $this->app->bind(AddValuationLayerServiceInterface::class, fn ($app) => new AddValuationLayerService($app->make(ValuationLayerRepositoryInterface::class)));
        $this->app->bind(ConsumeValuationLayersServiceInterface::class, fn ($app) => new ConsumeValuationLayersService($app->make(ValuationLayerRepositoryInterface::class)));
        $this->app->bind(AllocateStockServiceInterface::class, fn ($app) => new AllocateStockService($app->make(BatchLotRepositoryInterface::class)));
        $this->app->bind(StockMovementServiceInterface::class, fn ($app) => new StockMovementService($app->make(StockMovementRepositoryInterface::class)));
        $this->app->bind(BatchLotServiceInterface::class, fn ($app) => new BatchLotService($app->make(BatchLotRepositoryInterface::class)));
        $this->app->bind(StockServiceInterface::class, fn ($app) => new StockService(
            $app->make(StockRepositoryInterface::class),
            $app->make(StockReservationRepositoryInterface::class),
        ));
        $this->app->bind(ReconcileInventoryServiceInterface::class, fn ($app) => new ReconcileInventoryService(
            $app->make(InventoryAdjustmentRepositoryInterface::class),
            $app->make(StockMovementRepositoryInterface::class),
            $app->make(StockRepositoryInterface::class),
        ));
        $this->app->bind(CreateCycleCountServiceInterface::class, fn ($app) => new CreateCycleCountService(
            $app->make(CycleCountRepositoryInterface::class),
            $app->make(StockRepositoryInterface::class),
        ));
        $this->app->bind(InventoryManagerServiceInterface::class, fn ($app) => new InventoryManagerService(
            $app->make(StockMovementServiceInterface::class),
            $app->make(AddValuationLayerServiceInterface::class),
            $app->make(StockServiceInterface::class),
        ));
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../../../routes/api.php');
    }
}
