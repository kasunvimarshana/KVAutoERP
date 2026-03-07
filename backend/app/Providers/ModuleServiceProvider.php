<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    private array $modules = [
        'User',
        'Product',
        'Inventory',
        'Order',
    ];

    public function register(): void {}

    public function boot(): void
    {
        foreach ($this->modules as $module) {
            $routeFile = app_path("Modules/{$module}/Routes/api.php");

            if (file_exists($routeFile)) {
                Route::middleware('api')
                    ->prefix('api/v1')
                    ->group($routeFile);
            }
        }
    }
}
