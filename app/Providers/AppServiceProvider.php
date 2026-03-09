<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the custom exception handler
        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \App\Core\Exceptions\Handler::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enforce JSON responses for all API requests
        \Illuminate\Http\Request::macro('expectsJson', function (): bool {
            /** @var \Illuminate\Http\Request $this */
            return $this->is('api/*') || str_contains($this->header('Accept', ''), 'application/json');
        });
    }
}

