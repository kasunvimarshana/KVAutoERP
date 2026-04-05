<?php declare(strict_types=1);
namespace Modules\Currency\Infrastructure\Providers;
use Illuminate\Support\ServiceProvider;
use Modules\Currency\Application\Contracts\ConvertAmountServiceInterface;
use Modules\Currency\Application\Services\ConvertAmountService;
use Modules\Currency\Domain\RepositoryInterfaces\CurrencyRepositoryInterface;
use Modules\Currency\Domain\RepositoryInterfaces\ExchangeRateRepositoryInterface;
use Modules\Currency\Infrastructure\Persistence\Eloquent\Models\CurrencyModel;
use Modules\Currency\Infrastructure\Persistence\Eloquent\Models\ExchangeRateModel;
use Modules\Currency\Infrastructure\Persistence\Eloquent\Repositories\EloquentCurrencyRepository;
use Modules\Currency\Infrastructure\Persistence\Eloquent\Repositories\EloquentExchangeRateRepository;
class CurrencyServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(CurrencyRepositoryInterface::class, fn($app) => new EloquentCurrencyRepository($app->make(CurrencyModel::class)));
        $this->app->bind(ExchangeRateRepositoryInterface::class, fn($app) => new EloquentExchangeRateRepository($app->make(ExchangeRateModel::class)));
        $this->app->bind(ConvertAmountServiceInterface::class, fn($app) => new ConvertAmountService($app->make(ExchangeRateRepositoryInterface::class)));
    }
    public function boot(): void {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
