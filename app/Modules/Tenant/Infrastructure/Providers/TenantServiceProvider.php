<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Tenant\Application\Contracts\TenantServiceInterface;
use Modules\Tenant\Application\Services\TenantService;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantSettingRepositoryInterface;
use Modules\Tenant\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use Modules\Tenant\Infrastructure\Persistence\Eloquent\Models\TenantSettingModel;
use Modules\Tenant\Infrastructure\Persistence\Eloquent\Repositories\EloquentTenantRepository;
use Modules\Tenant\Infrastructure\Persistence\Eloquent\Repositories\EloquentTenantSettingRepository;

class TenantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TenantRepositoryInterface::class, function ($app) {
            return new EloquentTenantRepository($app->make(TenantModel::class));
        });

        $this->app->bind(TenantSettingRepositoryInterface::class, function ($app) {
            return new EloquentTenantSettingRepository($app->make(TenantSettingModel::class));
        });

        $this->app->bind(TenantServiceInterface::class, function ($app) {
            return new TenantService(
                $app->make(TenantRepositoryInterface::class),
                $app->make(TenantSettingRepositoryInterface::class),
            );
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
