<?php

declare(strict_types=1);

namespace Modules\Currency\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Currency\Application\Contracts\ConvertAmountServiceInterface;
use Modules\Currency\Application\Contracts\CurrencyServiceInterface;
use Modules\Currency\Application\Services\ConvertAmountService;
use Modules\Currency\Application\Services\CurrencyService;
use Modules\Currency\Domain\RepositoryInterfaces\CurrencyRepositoryInterface;
use Modules\Currency\Domain\RepositoryInterfaces\ExchangeRateRepositoryInterface;
use Modules\Currency\Infrastructure\Persistence\Eloquent\Models\CurrencyModel;
use Modules\Currency\Infrastructure\Persistence\Eloquent\Models\ExchangeRateModel;
use Modules\Currency\Infrastructure\Persistence\Eloquent\Repositories\EloquentCurrencyRepository;
use Modules\Currency\Infrastructure\Persistence\Eloquent\Repositories\EloquentExchangeRateRepository;

class CurrencyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CurrencyRepositoryInterface::class, function ($app) {
            return new EloquentCurrencyRepository($app->make(CurrencyModel::class));
        });

        $this->app->bind(ExchangeRateRepositoryInterface::class, function ($app) {
            return new EloquentExchangeRateRepository($app->make(ExchangeRateModel::class));
        });

        $this->app->bind(CurrencyServiceInterface::class, function ($app) {
            return new CurrencyService($app->make(CurrencyRepositoryInterface::class));
        });

        $this->app->bind(ConvertAmountServiceInterface::class, function ($app) {
            return new ConvertAmountService($app->make(ExchangeRateRepositoryInterface::class));
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
