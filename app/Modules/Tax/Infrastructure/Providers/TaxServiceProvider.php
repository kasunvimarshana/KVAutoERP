<?php

declare(strict_types=1);

namespace Modules\Tax\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Tax\Application\Contracts\CalculateTaxServiceInterface;
use Modules\Tax\Application\Contracts\TaxGroupRateServiceInterface;
use Modules\Tax\Application\Contracts\TaxGroupServiceInterface;
use Modules\Tax\Application\Services\CalculateTaxService;
use Modules\Tax\Application\Services\TaxGroupRateService;
use Modules\Tax\Application\Services\TaxGroupService;
use Modules\Tax\Domain\RepositoryInterfaces\TaxGroupRateRepositoryInterface;
use Modules\Tax\Domain\RepositoryInterfaces\TaxGroupRepositoryInterface;
use Modules\Tax\Infrastructure\Persistence\Eloquent\Models\TaxGroupModel;
use Modules\Tax\Infrastructure\Persistence\Eloquent\Models\TaxGroupRateModel;
use Modules\Tax\Infrastructure\Persistence\Eloquent\Repositories\EloquentTaxGroupRateRepository;
use Modules\Tax\Infrastructure\Persistence\Eloquent\Repositories\EloquentTaxGroupRepository;

class TaxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TaxGroupRepositoryInterface::class, function ($app) {
            return new EloquentTaxGroupRepository($app->make(TaxGroupModel::class));
        });

        $this->app->bind(TaxGroupRateRepositoryInterface::class, function ($app) {
            return new EloquentTaxGroupRateRepository($app->make(TaxGroupRateModel::class));
        });

        $this->app->bind(TaxGroupServiceInterface::class, function ($app) {
            return new TaxGroupService($app->make(TaxGroupRepositoryInterface::class));
        });

        $this->app->bind(TaxGroupRateServiceInterface::class, function ($app) {
            return new TaxGroupRateService($app->make(TaxGroupRateRepositoryInterface::class));
        });

        $this->app->bind(CalculateTaxServiceInterface::class, function ($app) {
            return new CalculateTaxService(
                $app->make(TaxGroupRepositoryInterface::class),
                $app->make(TaxGroupRateRepositoryInterface::class),
            );
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../../../routes/api.php');
    }
}
