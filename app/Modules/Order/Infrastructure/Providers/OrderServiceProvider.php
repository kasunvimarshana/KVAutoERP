<?php

declare(strict_types=1);

namespace Modules\Order\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Order\Application\Contracts\OrderServiceInterface;
use Modules\Order\Application\Contracts\OrderTransactionServiceInterface;
use Modules\Order\Application\Contracts\ReturnServiceInterface;
use Modules\Order\Application\Services\OrderService;
use Modules\Order\Application\Services\OrderTransactionService;
use Modules\Order\Application\Services\ReturnService;
use Modules\Order\Domain\RepositoryInterfaces\OrderLineRepositoryInterface;
use Modules\Order\Domain\RepositoryInterfaces\OrderRepositoryInterface;
use Modules\Order\Domain\RepositoryInterfaces\OrderTransactionRepositoryInterface;
use Modules\Order\Domain\RepositoryInterfaces\ReturnLineRepositoryInterface;
use Modules\Order\Domain\RepositoryInterfaces\ReturnRepositoryInterface;
use Modules\Order\Infrastructure\Persistence\Eloquent\Models\OrderLineModel;
use Modules\Order\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use Modules\Order\Infrastructure\Persistence\Eloquent\Models\OrderTransactionModel;
use Modules\Order\Infrastructure\Persistence\Eloquent\Models\ReturnLineModel;
use Modules\Order\Infrastructure\Persistence\Eloquent\Models\ReturnModel;
use Modules\Order\Infrastructure\Persistence\Eloquent\Repositories\EloquentOrderLineRepository;
use Modules\Order\Infrastructure\Persistence\Eloquent\Repositories\EloquentOrderRepository;
use Modules\Order\Infrastructure\Persistence\Eloquent\Repositories\EloquentOrderTransactionRepository;
use Modules\Order\Infrastructure\Persistence\Eloquent\Repositories\EloquentReturnLineRepository;
use Modules\Order\Infrastructure\Persistence\Eloquent\Repositories\EloquentReturnRepository;

class OrderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrderRepositoryInterface::class, function ($app) {
            return new EloquentOrderRepository($app->make(OrderModel::class));
        });

        $this->app->bind(OrderLineRepositoryInterface::class, function ($app) {
            return new EloquentOrderLineRepository($app->make(OrderLineModel::class));
        });

        $this->app->bind(OrderTransactionRepositoryInterface::class, function ($app) {
            return new EloquentOrderTransactionRepository($app->make(OrderTransactionModel::class));
        });

        $this->app->bind(ReturnRepositoryInterface::class, function ($app) {
            return new EloquentReturnRepository($app->make(ReturnModel::class));
        });

        $this->app->bind(ReturnLineRepositoryInterface::class, function ($app) {
            return new EloquentReturnLineRepository($app->make(ReturnLineModel::class));
        });

        $this->app->bind(OrderServiceInterface::class, function ($app) {
            return new OrderService(
                $app->make(OrderRepositoryInterface::class),
                $app->make(OrderLineRepositoryInterface::class),
            );
        });

        $this->app->bind(ReturnServiceInterface::class, function ($app) {
            return new ReturnService(
                $app->make(ReturnRepositoryInterface::class),
                $app->make(ReturnLineRepositoryInterface::class),
            );
        });

        $this->app->bind(OrderTransactionServiceInterface::class, function ($app) {
            return new OrderTransactionService(
                $app->make(OrderTransactionRepositoryInterface::class),
                $app->make(OrderRepositoryInterface::class),
            );
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
