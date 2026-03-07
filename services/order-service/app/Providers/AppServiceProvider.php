<?php

namespace App\Providers;

use App\Domain\Order\Repositories\EloquentOrderRepository;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repository interface to Eloquent implementation
        $this->app->bind(
            OrderRepositoryInterface::class,
            EloquentOrderRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
