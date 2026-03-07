<?php

namespace App\Providers;

use App\Modules\Inventory\Repositories\InventoryRepository;
use App\Modules\Inventory\Repositories\InventoryRepositoryInterface;
use App\Modules\Order\Repositories\OrderRepository;
use App\Modules\Order\Repositories\OrderRepositoryInterface;
use App\Modules\Product\Repositories\ProductRepository;
use App\Modules\Product\Repositories\ProductRepositoryInterface;
use App\Modules\User\Repositories\UserRepository;
use App\Modules\User\Repositories\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(InventoryRepositoryInterface::class, InventoryRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
