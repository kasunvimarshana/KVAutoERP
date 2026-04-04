<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Configuration\Application\Contracts\CreateOrgUnitServiceInterface;
use Modules\Configuration\Application\Contracts\DeleteOrgUnitServiceInterface;
use Modules\Configuration\Application\Contracts\GetOrgUnitServiceInterface;
use Modules\Configuration\Application\Contracts\GetSettingGroupServiceInterface;
use Modules\Configuration\Application\Contracts\GetSettingServiceInterface;
use Modules\Configuration\Application\Contracts\ListOrgUnitsServiceInterface;
use Modules\Configuration\Application\Contracts\OrgUnitTreeServiceInterface;
use Modules\Configuration\Application\Contracts\SetSettingServiceInterface;
use Modules\Configuration\Application\Contracts\UpdateOrgUnitServiceInterface;
use Modules\Configuration\Application\Services\CreateOrgUnitService;
use Modules\Configuration\Application\Services\DeleteOrgUnitService;
use Modules\Configuration\Application\Services\GetOrgUnitService;
use Modules\Configuration\Application\Services\GetSettingGroupService;
use Modules\Configuration\Application\Services\GetSettingService;
use Modules\Configuration\Application\Services\ListOrgUnitsService;
use Modules\Configuration\Application\Services\OrgUnitTreeService;
use Modules\Configuration\Application\Services\SetSettingService;
use Modules\Configuration\Application\Services\UpdateOrgUnitService;
use Modules\Configuration\Domain\RepositoryInterfaces\OrgUnitRepositoryInterface;
use Modules\Configuration\Domain\RepositoryInterfaces\SettingRepositoryInterface;
use Modules\Configuration\Infrastructure\Persistence\Eloquent\Models\OrgUnitModel;
use Modules\Configuration\Infrastructure\Persistence\Eloquent\Models\SettingModel;
use Modules\Configuration\Infrastructure\Persistence\Eloquent\Repositories\EloquentOrgUnitRepository;
use Modules\Configuration\Infrastructure\Persistence\Eloquent\Repositories\EloquentSettingRepository;

class ConfigurationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrgUnitRepositoryInterface::class, function ($app) {
            return new EloquentOrgUnitRepository($app->make(OrgUnitModel::class));
        });

        $this->app->bind(SettingRepositoryInterface::class, function ($app) {
            return new EloquentSettingRepository($app->make(SettingModel::class));
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

        $this->app->bind(GetOrgUnitServiceInterface::class, function ($app) {
            return new GetOrgUnitService($app->make(OrgUnitRepositoryInterface::class));
        });

        $this->app->bind(ListOrgUnitsServiceInterface::class, function ($app) {
            return new ListOrgUnitsService($app->make(OrgUnitRepositoryInterface::class));
        });

        $this->app->bind(OrgUnitTreeServiceInterface::class, function ($app) {
            return new OrgUnitTreeService($app->make(OrgUnitRepositoryInterface::class));
        });

        $this->app->bind(GetSettingServiceInterface::class, function ($app) {
            return new GetSettingService($app->make(SettingRepositoryInterface::class));
        });

        $this->app->bind(SetSettingServiceInterface::class, function ($app) {
            return new SetSettingService($app->make(SettingRepositoryInterface::class));
        });

        $this->app->bind(GetSettingGroupServiceInterface::class, function ($app) {
            return new GetSettingGroupService($app->make(SettingRepositoryInterface::class));
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        if (file_exists($routes = __DIR__.'/../../routes/api.php')) {
            $this->loadRoutesFrom($routes);
        }
    }
}
