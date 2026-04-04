<?php
declare(strict_types=1);
namespace Modules\Pricing\Infrastructure\Providers;
use Illuminate\Support\ServiceProvider;
use Modules\Pricing\Domain\RepositoryInterfaces\PriceListRepositoryInterface;
use Modules\Pricing\Domain\RepositoryInterfaces\TaxRateRepositoryInterface;
use Modules\Pricing\Infrastructure\Persistence\Eloquent\Models\PriceListModel;
use Modules\Pricing\Infrastructure\Persistence\Eloquent\Models\TaxRateModel;
use Modules\Pricing\Infrastructure\Persistence\Eloquent\Repositories\EloquentPriceListRepository;
use Modules\Pricing\Infrastructure\Persistence\Eloquent\Repositories\EloquentTaxRateRepository;
class PricingServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(PriceListRepositoryInterface::class, fn($app) => new EloquentPriceListRepository($app->make(PriceListModel::class)));
        $this->app->bind(TaxRateRepositoryInterface::class, fn($app) => new EloquentTaxRateRepository($app->make(TaxRateModel::class)));
    }
    public function boot(): void {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
    }
}
