<?php

declare(strict_types=1);

namespace App\Modules\Order\Providers;

use App\Modules\Order\Application\Saga\Orchestrators\CreateOrderSagaOrchestrator;
use App\Modules\Order\Application\Saga\Steps\ConfirmOrderStep;
use App\Modules\Order\Application\Saga\Steps\CreateOrderStep;
use App\Modules\Order\Application\Saga\Steps\ProcessPaymentStep;
use App\Modules\Order\Application\Saga\Steps\ReserveInventoryStep;
use App\Modules\Order\Application\Services\OrderService;
use App\Modules\Order\Infrastructure\Repositories\OrderRepository;
use Illuminate\Support\ServiceProvider;

class OrderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OrderRepository::class);
        $this->app->singleton(CreateOrderStep::class);
        $this->app->singleton(ReserveInventoryStep::class);
        $this->app->singleton(ProcessPaymentStep::class);
        $this->app->singleton(ConfirmOrderStep::class);
        $this->app->singleton(CreateOrderSagaOrchestrator::class);
        $this->app->singleton(OrderService::class);
    }

    public function boot(): void {}
}
