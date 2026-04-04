<?php
declare(strict_types=1);
namespace Modules\StockMovement\Infrastructure\Providers;
use Illuminate\Support\ServiceProvider;
use Modules\StockMovement\Domain\RepositoryInterfaces\StockMovementRepositoryInterface;
use Modules\StockMovement\Infrastructure\Persistence\Eloquent\Models\StockMovementModel;
use Modules\StockMovement\Infrastructure\Persistence\Eloquent\Repositories\EloquentStockMovementRepository;
class StockMovementServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(StockMovementRepositoryInterface::class, fn($app) => new EloquentStockMovementRepository($app->make(StockMovementModel::class)));
    }
    public function boot(): void {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
    }
}
