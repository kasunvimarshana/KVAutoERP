<?php declare(strict_types=1);
namespace Modules\Inventory\Infrastructure\Providers;
use Illuminate\Support\ServiceProvider;
use Modules\Inventory\Application\Contracts\CreateCycleCountServiceInterface;
use Modules\Inventory\Application\Contracts\InventoryManagerServiceInterface;
use Modules\Inventory\Application\Services\AddValuationLayerService;
use Modules\Inventory\Application\Services\AllocateStockService;
use Modules\Inventory\Application\Services\ConsumeValuationLayersService;
use Modules\Inventory\Application\Services\CreateCycleCountService;
use Modules\Inventory\Application\Services\InventoryManagerService;
use Modules\Inventory\Application\Services\ReconcileInventoryService;
use Modules\Inventory\Domain\RepositoryInterfaces\CycleCountRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockItemRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockMovementRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\ValuationLayerRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\CycleCountLineModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\CycleCountModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockItemModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\StockMovementModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\ValuationLayerModel;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentCycleCountRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentStockItemRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentStockMovementRepository;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentValuationLayerRepository;
class InventoryServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(StockItemRepositoryInterface::class, fn($app)=>new EloquentStockItemRepository($app->make(StockItemModel::class)));
        $this->app->bind(StockMovementRepositoryInterface::class, fn($app)=>new EloquentStockMovementRepository($app->make(StockMovementModel::class)));
        $this->app->bind(ValuationLayerRepositoryInterface::class, fn($app)=>new EloquentValuationLayerRepository($app->make(ValuationLayerModel::class)));
        $this->app->bind(CycleCountRepositoryInterface::class, fn($app)=>new EloquentCycleCountRepository($app->make(CycleCountModel::class),$app->make(CycleCountLineModel::class)));
        $this->app->bind(AddValuationLayerService::class, fn($app)=>new AddValuationLayerService($app->make(ValuationLayerRepositoryInterface::class)));
        $this->app->bind(ConsumeValuationLayersService::class, fn($app)=>new ConsumeValuationLayersService($app->make(ValuationLayerRepositoryInterface::class)));
        $this->app->bind(AllocateStockService::class, fn($app)=>new AllocateStockService($app->make(ValuationLayerRepositoryInterface::class)));
        $this->app->bind(ReconcileInventoryService::class, fn($app)=>new ReconcileInventoryService($app->make(CycleCountRepositoryInterface::class),$app->make(StockMovementRepositoryInterface::class)));
        $this->app->bind(CreateCycleCountServiceInterface::class, fn($app)=>new CreateCycleCountService($app->make(CycleCountRepositoryInterface::class),$app->make(StockItemRepositoryInterface::class)));
        $this->app->bind(InventoryManagerServiceInterface::class, fn($app)=>new InventoryManagerService($app->make(StockItemRepositoryInterface::class),$app->make(StockMovementRepositoryInterface::class),$app->make(AddValuationLayerService::class),$app->make(ConsumeValuationLayersService::class)));
    }
    public function boot(): void { $this->loadMigrationsFrom(__DIR__.'/../../database/migrations'); }
}
