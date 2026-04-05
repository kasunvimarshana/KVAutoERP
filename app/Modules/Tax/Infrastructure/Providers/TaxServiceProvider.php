<?php

declare(strict_types=1);

namespace Modules\Tax\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Tax\Application\Contracts\CalculateTaxServiceInterface;
use Modules\Tax\Application\Contracts\TaxGroupServiceInterface;
use Modules\Tax\Application\Contracts\TaxRateServiceInterface;
use Modules\Tax\Application\Services\CalculateTaxService;
use Modules\Tax\Application\Services\TaxGroupService;
use Modules\Tax\Application\Services\TaxRateService;
use Modules\Tax\Domain\RepositoryInterfaces\TaxGroupRateRepositoryInterface;
use Modules\Tax\Domain\RepositoryInterfaces\TaxGroupRepositoryInterface;
use Modules\Tax\Domain\RepositoryInterfaces\TaxRateRepositoryInterface;
use Modules\Tax\Infrastructure\Persistence\Eloquent\Models\TaxGroupModel;
use Modules\Tax\Infrastructure\Persistence\Eloquent\Models\TaxGroupRateModel;
use Modules\Tax\Infrastructure\Persistence\Eloquent\Models\TaxRateModel;
use Modules\Tax\Infrastructure\Persistence\Eloquent\Repositories\EloquentTaxGroupRateRepository;
use Modules\Tax\Infrastructure\Persistence\Eloquent\Repositories\EloquentTaxGroupRepository;
use Modules\Tax\Infrastructure\Persistence\Eloquent\Repositories\EloquentTaxRateRepository;

class TaxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            TaxRateRepositoryInterface::class,
            fn ($app) => new EloquentTaxRateRepository($app->make(TaxRateModel::class))
        );

        $this->app->bind(
            TaxGroupRepositoryInterface::class,
            fn ($app) => new EloquentTaxGroupRepository($app->make(TaxGroupModel::class))
        );

        $this->app->bind(
            TaxGroupRateRepositoryInterface::class,
            fn ($app) => new EloquentTaxGroupRateRepository($app->make(TaxGroupRateModel::class))
        );

        $this->app->bind(
            TaxRateServiceInterface::class,
            fn ($app) => new TaxRateService($app->make(TaxRateRepositoryInterface::class))
        );

        $this->app->bind(
            TaxGroupServiceInterface::class,
            fn ($app) => new TaxGroupService(
                $app->make(TaxGroupRepositoryInterface::class),
                $app->make(TaxGroupRateRepositoryInterface::class),
                $app->make(TaxRateRepositoryInterface::class),
            )
        );

        $this->app->bind(
            CalculateTaxServiceInterface::class,
            fn ($app) => new CalculateTaxService(
                $app->make(TaxGroupRateRepositoryInterface::class),
                $app->make(TaxRateRepositoryInterface::class),
            )
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../../../routes/api.php');
    }
}
