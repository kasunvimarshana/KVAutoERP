<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Configuration\Application\Contracts\OrgUnitServiceInterface;
use Modules\Configuration\Application\Contracts\SettingServiceInterface;
use Modules\Configuration\Application\Services\OrgUnitService;
use Modules\Configuration\Application\Services\SettingService;
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
        $this->app->bind(SettingRepositoryInterface::class, function ($app) {
            return new EloquentSettingRepository($app->make(SettingModel::class));
        });

        $this->app->bind(OrgUnitRepositoryInterface::class, function ($app) {
            return new EloquentOrgUnitRepository($app->make(OrgUnitModel::class));
        });

        $this->app->bind(SettingServiceInterface::class, function ($app) {
            return new SettingService($app->make(SettingRepositoryInterface::class));
        });

        $this->app->bind(OrgUnitServiceInterface::class, function ($app) {
            return new OrgUnitService($app->make(OrgUnitRepositoryInterface::class));
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
