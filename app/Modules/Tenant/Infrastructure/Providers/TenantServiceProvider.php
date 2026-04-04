<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Tenant\Application\Contracts\ActivateTenantServiceInterface;
use Modules\Tenant\Application\Contracts\CreateTenantServiceInterface;
use Modules\Tenant\Application\Contracts\DeleteTenantServiceInterface;
use Modules\Tenant\Application\Contracts\GetTenantServiceInterface;
use Modules\Tenant\Application\Contracts\ListTenantsServiceInterface;
use Modules\Tenant\Application\Contracts\SuspendTenantServiceInterface;
use Modules\Tenant\Application\Contracts\UpdateTenantServiceInterface;
use Modules\Tenant\Application\Services\ActivateTenantService;
use Modules\Tenant\Application\Services\CreateTenantService;
use Modules\Tenant\Application\Services\DeleteTenantService;
use Modules\Tenant\Application\Services\GetTenantService;
use Modules\Tenant\Application\Services\ListTenantsService;
use Modules\Tenant\Application\Services\SuspendTenantService;
use Modules\Tenant\Application\Services\UpdateTenantService;
use Modules\Tenant\Domain\Repositories\TenantRepositoryInterface;
use Modules\Tenant\Infrastructure\Persistence\Models\TenantModel;
use Modules\Tenant\Infrastructure\Persistence\Repositories\EloquentTenantRepository;

class TenantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TenantRepositoryInterface::class, function ($app) {
            return new EloquentTenantRepository($app->make(TenantModel::class));
        });

        $this->app->bind(CreateTenantServiceInterface::class, function ($app) {
            return new CreateTenantService($app->make(TenantRepositoryInterface::class));
        });

        $this->app->bind(UpdateTenantServiceInterface::class, function ($app) {
            return new UpdateTenantService($app->make(TenantRepositoryInterface::class));
        });

        $this->app->bind(DeleteTenantServiceInterface::class, function ($app) {
            return new DeleteTenantService($app->make(TenantRepositoryInterface::class));
        });

        $this->app->bind(GetTenantServiceInterface::class, function ($app) {
            return new GetTenantService($app->make(TenantRepositoryInterface::class));
        });

        $this->app->bind(ListTenantsServiceInterface::class, function ($app) {
            return new ListTenantsService($app->make(TenantRepositoryInterface::class));
        });

        $this->app->bind(SuspendTenantServiceInterface::class, function ($app) {
            return new SuspendTenantService($app->make(TenantRepositoryInterface::class));
        });

        $this->app->bind(ActivateTenantServiceInterface::class, function ($app) {
            return new ActivateTenantService($app->make(TenantRepositoryInterface::class));
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        Route::group([], function () {
            $routeFile = __DIR__.'/../../routes/api.php';
            if (file_exists($routeFile)) {
                require $routeFile;
            }
        });
    }
}
