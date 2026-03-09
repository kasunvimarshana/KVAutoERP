<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Providers;

use App\Modules\Inventory\Application\Services\InventoryService;
use App\Modules\Inventory\Infrastructure\Repositories\ProductRepository;
use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ProductRepository::class);
        $this->app->singleton(InventoryService::class);
    }

    public function boot(): void {}
}
