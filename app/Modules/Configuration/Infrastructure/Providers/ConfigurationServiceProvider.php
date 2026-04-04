<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Configuration\Application\Contracts\CreateOrgUnitServiceInterface;
use Modules\Configuration\Application\Contracts\DeleteOrgUnitServiceInterface;
use Modules\Configuration\Application\Contracts\GetSystemConfigServiceInterface;
use Modules\Configuration\Application\Contracts\MoveOrgUnitServiceInterface;
use Modules\Configuration\Application\Contracts\OrgUnitTreeServiceInterface;
use Modules\Configuration\Application\Contracts\UpdateOrgUnitServiceInterface;
use Modules\Configuration\Application\Contracts\UpdateSystemConfigServiceInterface;
use Modules\Configuration\Application\Services\CreateOrgUnitService;
use Modules\Configuration\Application\Services\DeleteOrgUnitService;
use Modules\Configuration\Application\Services\GetSystemConfigService;
use Modules\Configuration\Application\Services\MoveOrgUnitService;
use Modules\Configuration\Application\Services\OrgUnitTreeService;
use Modules\Configuration\Application\Services\UpdateOrgUnitService;
use Modules\Configuration\Application\Services\UpdateSystemConfigService;
use Modules\Configuration\Domain\Repositories\OrgUnitRepositoryInterface;
use Modules\Configuration\Domain\Repositories\SystemConfigRepositoryInterface;
use Modules\Configuration\Infrastructure\Persistence\Models\OrgUnitClosureModel;
use Modules\Configuration\Infrastructure\Persistence\Models\OrgUnitModel;
use Modules\Configuration\Infrastructure\Persistence\Models\SystemConfigModel;
use Modules\Configuration\Infrastructure\Persistence\Repositories\EloquentOrgUnitRepository;
use Modules\Configuration\Infrastructure\Persistence\Repositories\EloquentSystemConfigRepository;

class ConfigurationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrgUnitRepositoryInterface::class, function ($app) {
            return new EloquentOrgUnitRepository(
                $app->make(OrgUnitModel::class),
                $app->make(OrgUnitClosureModel::class),
            );
        });

        $this->app->bind(SystemConfigRepositoryInterface::class, function ($app) {
            return new EloquentSystemConfigRepository($app->make(SystemConfigModel::class));
        });

        $this->app->bind(CreateOrgUnitServiceInterface::class, function ($app) {
            return new CreateOrgUnitService($app->make(OrgUnitRepositoryInterface::class));
        });

        $this->app->bind(UpdateOrgUnitServiceInterface::class, function ($app) {
            return new UpdateOrgUnitService($app->make(OrgUnitRepositoryInterface::class));
        });

        $this->app->bind(DeleteOrgUnitServiceInterface::class, function ($app) {
            return new DeleteOrgUnitService($app->make(OrgUnitRepositoryInterface::class));
        });

        $this->app->bind(MoveOrgUnitServiceInterface::class, function ($app) {
            return new MoveOrgUnitService($app->make(OrgUnitRepositoryInterface::class));
        });

        $this->app->bind(OrgUnitTreeServiceInterface::class, function ($app) {
            return new OrgUnitTreeService($app->make(OrgUnitRepositoryInterface::class));
        });

        $this->app->bind(GetSystemConfigServiceInterface::class, function ($app) {
            return new GetSystemConfigService($app->make(SystemConfigRepositoryInterface::class));
        });

        $this->app->bind(UpdateSystemConfigServiceInterface::class, function ($app) {
            return new UpdateSystemConfigService($app->make(SystemConfigRepositoryInterface::class));
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
