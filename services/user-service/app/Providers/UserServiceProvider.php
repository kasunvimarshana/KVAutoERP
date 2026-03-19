<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * User domain service provider.
 *
 * Loads user-service configuration and registers any additional
 * domain-specific bindings that extend AppServiceProvider.
 * Acts as a hook point for future extensions and module discovery.
 */
final class UserServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/user_service.php',
            'user_service',
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
            __DIR__ . '/../../config/user_service.php' => config_path('user_service.php'),
        ], 'user-service-config');
    }
}
