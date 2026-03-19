<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\StockItemRepositoryInterface;
use App\Contracts\Repositories\StockLedgerRepositoryInterface;
use App\Contracts\Repositories\WarehouseRepositoryInterface;
use App\Contracts\Services\StockServiceInterface;
use App\Contracts\Services\WarehouseServiceInterface;
use App\Repositories\StockItemRepository;
use App\Repositories\StockLedgerRepository;
use App\Repositories\WarehouseRepository;
use App\Services\StockService;
use App\Services\WarehouseService;
use Illuminate\Support\ServiceProvider;

/**
 * Core application service provider.
 *
 * Binds all inventory-domain interfaces to their concrete implementations.
 */
final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Repositories
        $this->app->bind(WarehouseRepositoryInterface::class, WarehouseRepository::class);
        $this->app->bind(StockItemRepositoryInterface::class, StockItemRepository::class);
        $this->app->bind(StockLedgerRepositoryInterface::class, StockLedgerRepository::class);

        // Services
        $this->app->bind(WarehouseServiceInterface::class, WarehouseService::class);
        $this->app->bind(StockServiceInterface::class, StockService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
