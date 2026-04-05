<?php

declare(strict_types=1);

namespace Modules\Pricing\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Pricing\Application\Contracts\DiscountServiceInterface;
use Modules\Pricing\Application\Contracts\PriceListItemServiceInterface;
use Modules\Pricing\Application\Contracts\PriceListServiceInterface;
use Modules\Pricing\Application\Contracts\ResolvePriceServiceInterface;
use Modules\Pricing\Application\Services\DiscountService;
use Modules\Pricing\Application\Services\PriceListItemService;
use Modules\Pricing\Application\Services\PriceListService;
use Modules\Pricing\Application\Services\ResolvePriceService;
use Modules\Pricing\Domain\RepositoryInterfaces\DiscountRepositoryInterface;
use Modules\Pricing\Domain\RepositoryInterfaces\PriceListItemRepositoryInterface;
use Modules\Pricing\Domain\RepositoryInterfaces\PriceListRepositoryInterface;
use Modules\Pricing\Infrastructure\Persistence\Eloquent\Models\DiscountModel;
use Modules\Pricing\Infrastructure\Persistence\Eloquent\Models\PriceListItemModel;
use Modules\Pricing\Infrastructure\Persistence\Eloquent\Models\PriceListModel;
use Modules\Pricing\Infrastructure\Persistence\Eloquent\Repositories\EloquentDiscountRepository;
use Modules\Pricing\Infrastructure\Persistence\Eloquent\Repositories\EloquentPriceListItemRepository;
use Modules\Pricing\Infrastructure\Persistence\Eloquent\Repositories\EloquentPriceListRepository;

class PricingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            PriceListRepositoryInterface::class,
            fn ($app) => new EloquentPriceListRepository($app->make(PriceListModel::class))
        );

        $this->app->bind(
            PriceListItemRepositoryInterface::class,
            fn ($app) => new EloquentPriceListItemRepository($app->make(PriceListItemModel::class))
        );

        $this->app->bind(
            DiscountRepositoryInterface::class,
            fn ($app) => new EloquentDiscountRepository($app->make(DiscountModel::class))
        );

        $this->app->bind(
            PriceListServiceInterface::class,
            fn ($app) => new PriceListService($app->make(PriceListRepositoryInterface::class))
        );

        $this->app->bind(
            PriceListItemServiceInterface::class,
            fn ($app) => new PriceListItemService($app->make(PriceListItemRepositoryInterface::class))
        );

        $this->app->bind(
            ResolvePriceServiceInterface::class,
            fn ($app) => new ResolvePriceService($app->make(PriceListItemRepositoryInterface::class))
        );

        $this->app->bind(
            DiscountServiceInterface::class,
            fn ($app) => new DiscountService($app->make(DiscountRepositoryInterface::class))
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../../../routes/api.php');
    }
}
