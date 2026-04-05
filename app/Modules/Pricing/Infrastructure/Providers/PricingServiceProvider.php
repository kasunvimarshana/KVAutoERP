<?php declare(strict_types=1);
namespace Modules\Pricing\Infrastructure\Providers;
use Illuminate\Support\ServiceProvider;
use Modules\Pricing\Application\Services\ResolvePriceService;
use Modules\Pricing\Domain\RepositoryInterfaces\PriceListRepositoryInterface;
use Modules\Pricing\Infrastructure\Persistence\Eloquent\Models\PriceListItemModel;
use Modules\Pricing\Infrastructure\Persistence\Eloquent\Models\PriceListModel;
use Modules\Pricing\Infrastructure\Persistence\Eloquent\Repositories\EloquentPriceListRepository;
class PricingServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(PriceListRepositoryInterface::class, fn($app)=>new EloquentPriceListRepository($app->make(PriceListModel::class),$app->make(PriceListItemModel::class)));
        $this->app->bind(ResolvePriceService::class, fn($app)=>new ResolvePriceService($app->make(PriceListRepositoryInterface::class)));
    }
    public function boot(): void { $this->loadMigrationsFrom(__DIR__.'/../../database/migrations'); }
}
