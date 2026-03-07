<?php

namespace App\Providers;

use App\Application\Services\TenantConfigManager;
use App\Application\Services\TenantService;
use App\Domain\Tenant\Entities\Tenant;
use App\Infrastructure\Repositories\EloquentTenantRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EloquentTenantRepository::class, function ($app) {
            return new EloquentTenantRepository(new Tenant());
        });

        $this->app->singleton(TenantConfigManager::class, function ($app) {
            return new TenantConfigManager($app->make('log'));
        });

        $this->app->singleton(TenantService::class, function ($app) {
            return new TenantService(
                $app->make(EloquentTenantRepository::class),
                $app->make(TenantConfigManager::class),
                $app->make('log'),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
