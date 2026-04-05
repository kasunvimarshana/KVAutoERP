<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Configuration\Application\Contracts\CurrencyServiceInterface;
use Modules\Configuration\Application\Contracts\LanguageServiceInterface;
use Modules\Configuration\Application\Contracts\SettingServiceInterface;
use Modules\Configuration\Application\Services\CurrencyService;
use Modules\Configuration\Application\Services\LanguageService;
use Modules\Configuration\Application\Services\SettingService;
use Modules\Configuration\Domain\RepositoryInterfaces\CurrencyRepositoryInterface;
use Modules\Configuration\Domain\RepositoryInterfaces\LanguageRepositoryInterface;
use Modules\Configuration\Domain\RepositoryInterfaces\SettingRepositoryInterface;
use Modules\Configuration\Infrastructure\Persistence\Eloquent\Models\CurrencyModel;
use Modules\Configuration\Infrastructure\Persistence\Eloquent\Models\LanguageModel;
use Modules\Configuration\Infrastructure\Persistence\Eloquent\Models\SettingModel;
use Modules\Configuration\Infrastructure\Persistence\Eloquent\Repositories\EloquentCurrencyRepository;
use Modules\Configuration\Infrastructure\Persistence\Eloquent\Repositories\EloquentLanguageRepository;
use Modules\Configuration\Infrastructure\Persistence\Eloquent\Repositories\EloquentSettingRepository;

class ConfigurationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SettingRepositoryInterface::class, function ($app) {
            return new EloquentSettingRepository($app->make(SettingModel::class));
        });

        $this->app->bind(CurrencyRepositoryInterface::class, function ($app) {
            return new EloquentCurrencyRepository($app->make(CurrencyModel::class));
        });

        $this->app->bind(LanguageRepositoryInterface::class, function ($app) {
            return new EloquentLanguageRepository($app->make(LanguageModel::class));
        });

        $this->app->bind(SettingServiceInterface::class, function ($app) {
            return new SettingService(
                $app->make(SettingRepositoryInterface::class),
            );
        });

        $this->app->bind(CurrencyServiceInterface::class, function ($app) {
            return new CurrencyService(
                $app->make(CurrencyRepositoryInterface::class),
            );
        });

        $this->app->bind(LanguageServiceInterface::class, function ($app) {
            return new LanguageService(
                $app->make(LanguageRepositoryInterface::class),
            );
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
