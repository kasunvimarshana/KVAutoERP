<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Inventory domain service provider.
 *
 * Loads inventory-service configuration files.
 */
final class InventoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/inventory_service.php',
            'inventory_service',
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/inventory_service.php' => config_path('inventory_service.php'),
        ], 'inventory-service-config');
    }
}
