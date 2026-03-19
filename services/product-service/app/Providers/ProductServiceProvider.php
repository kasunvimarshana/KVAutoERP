<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Product domain service provider.
 *
 * Loads product-service configuration and registers any additional
 * domain-specific bindings that extend AppServiceProvider.
 */
final class ProductServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/product_service.php',
            'product_service',
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
            __DIR__ . '/../../config/product_service.php' => config_path('product_service.php'),
        ], 'product-service-config');
    }
}
