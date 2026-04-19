<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Infrastructure\Concerns\LoadsModuleRoutesAndMigrations;
use Modules\Shared\Application\Contracts\FindCountriesServiceInterface;
use Modules\Shared\Application\Contracts\FindCurrenciesServiceInterface;
use Modules\Shared\Application\Contracts\FindLanguagesServiceInterface;
use Modules\Shared\Application\Contracts\FindTimezonesServiceInterface;
use Modules\Shared\Application\Services\FindCountriesService;
use Modules\Shared\Application\Services\FindCurrenciesService;
use Modules\Shared\Application\Services\FindLanguagesService;
use Modules\Shared\Application\Services\FindTimezonesService;
use Modules\Shared\Domain\RepositoryInterfaces\CountryRepositoryInterface;
use Modules\Shared\Domain\RepositoryInterfaces\CurrencyRepositoryInterface;
use Modules\Shared\Domain\RepositoryInterfaces\LanguageRepositoryInterface;
use Modules\Shared\Domain\RepositoryInterfaces\TimezoneRepositoryInterface;
use Modules\Shared\Infrastructure\Persistence\Eloquent\Repositories\EloquentCountryRepository;
use Modules\Shared\Infrastructure\Persistence\Eloquent\Repositories\EloquentCurrencyRepository;
use Modules\Shared\Infrastructure\Persistence\Eloquent\Repositories\EloquentLanguageRepository;
use Modules\Shared\Infrastructure\Persistence\Eloquent\Repositories\EloquentTimezoneRepository;

class SharedServiceProvider extends ServiceProvider
{
    use LoadsModuleRoutesAndMigrations;

    public function register(): void
    {
        $repositoryBindings = [
            CountryRepositoryInterface::class => EloquentCountryRepository::class,
            CurrencyRepositoryInterface::class => EloquentCurrencyRepository::class,
            LanguageRepositoryInterface::class => EloquentLanguageRepository::class,
            TimezoneRepositoryInterface::class => EloquentTimezoneRepository::class,
        ];

        foreach ($repositoryBindings as $contract => $implementation) {
            $this->app->bind($contract, $implementation);
        }

        // Services
        $this->app->bind(FindCountriesServiceInterface::class, FindCountriesService::class);
        $this->app->bind(FindCurrenciesServiceInterface::class, FindCurrenciesService::class);
        $this->app->bind(FindLanguagesServiceInterface::class, FindLanguagesService::class);
        $this->app->bind(FindTimezonesServiceInterface::class, FindTimezonesService::class);
    }

    public function boot(): void
    {
        $this->bootModule(
            __DIR__.'/../../routes/api.php',
            __DIR__.'/../../database/migrations',
        );
    }
}
