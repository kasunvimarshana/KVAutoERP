<?php declare(strict_types=1);
namespace Modules\Order\Infrastructure\Providers;
use Illuminate\Support\ServiceProvider;
use Modules\Order\Application\Services\CreateOrderService;
use Modules\Order\Domain\RepositoryInterfaces\OrderRepositoryInterface;
use Modules\Order\Infrastructure\Persistence\Eloquent\Models\OrderLineModel;
use Modules\Order\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use Modules\Order\Infrastructure\Persistence\Eloquent\Repositories\EloquentOrderRepository;
class OrderServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(OrderRepositoryInterface::class, fn($app)=>new EloquentOrderRepository($app->make(OrderModel::class),$app->make(OrderLineModel::class)));
        $this->app->bind(CreateOrderService::class, fn($app)=>new CreateOrderService($app->make(OrderRepositoryInterface::class)));
    }
    public function boot(): void { $this->loadMigrationsFrom(__DIR__.'/../../database/migrations'); }
}
