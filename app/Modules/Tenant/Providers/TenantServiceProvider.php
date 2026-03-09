<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Providers;

use App\Infrastructure\Config\RuntimeConfigurationService;
use App\Modules\Tenant\Application\Services\TenantService;
use App\Modules\Tenant\Infrastructure\Repositories\TenantRepository;
use Illuminate\Support\ServiceProvider;

class TenantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RuntimeConfigurationService::class);
        $this->app->singleton(TenantRepository::class);
        $this->app->singleton(TenantService::class);
    }

    public function boot(): void {}
}
