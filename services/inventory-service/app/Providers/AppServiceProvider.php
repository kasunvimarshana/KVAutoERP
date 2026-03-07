<?php
namespace App\Providers;

use App\Repositories\InventoryRepository;
use App\Services\InventoryService;
use App\Services\ProductServiceClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ProductServiceClient::class);

        $this->app->singleton(InventoryRepository::class, function ($app) {
            return new InventoryRepository($app->make(\App\Models\Inventory::class));
        });

        $this->app->singleton(InventoryService::class, function ($app) {
            return new InventoryService(
                $app->make(InventoryRepository::class),
                $app->make(ProductServiceClient::class),
            );
        });
    }

    public function boot(): void {}
}
