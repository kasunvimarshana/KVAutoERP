<?php

declare(strict_types=1);

namespace App\Modules\Health\Providers;

use App\Modules\Health\Http\Controllers\HealthController;
use Illuminate\Support\ServiceProvider;

class HealthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(HealthController::class);
    }

    public function boot(): void {}
}
