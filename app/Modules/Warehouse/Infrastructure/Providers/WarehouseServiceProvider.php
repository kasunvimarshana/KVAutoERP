<?php declare(strict_types=1);
namespace Modules\Warehouse\Infrastructure\Providers;
use Illuminate\Support\ServiceProvider;
use Modules\Warehouse\Domain\RepositoryInterfaces\WarehouseLocationRepositoryInterface;
use Modules\Warehouse\Domain\RepositoryInterfaces\WarehouseRepositoryInterface;
use Modules\Warehouse\Infrastructure\Persistence\Eloquent\Models\WarehouseLocationModel;
use Modules\Warehouse\Infrastructure\Persistence\Eloquent\Models\WarehouseModel;
use Modules\Warehouse\Infrastructure\Persistence\Eloquent\Repositories\EloquentWarehouseLocationRepository;
use Modules\Warehouse\Infrastructure\Persistence\Eloquent\Repositories\EloquentWarehouseRepository;
class WarehouseServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(WarehouseRepositoryInterface::class, fn($app)=>new EloquentWarehouseRepository($app->make(WarehouseModel::class)));
        $this->app->bind(WarehouseLocationRepositoryInterface::class, fn($app)=>new EloquentWarehouseLocationRepository($app->make(WarehouseLocationModel::class)));
    }
    public function boot(): void { $this->loadMigrationsFrom(__DIR__.'/../../database/migrations'); }
}
