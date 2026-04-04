<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Product\Application\Contracts\ProductCategoryServiceInterface;
use Modules\Product\Application\Contracts\ProductServiceInterface;
use Modules\Product\Application\Services\ProductCategoryService;
use Modules\Product\Application\Services\ProductService;
use Modules\Product\Domain\RepositoryInterfaces\ProductCategoryRepositoryInterface;
use Modules\Product\Domain\RepositoryInterfaces\ProductRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductCategoryModel;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use Modules\Product\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductCategoryRepository;
use Modules\Product\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductRepository;

class ProductServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductCategoryRepositoryInterface::class, fn($app) =>
            new EloquentProductCategoryRepository($app->make(ProductCategoryModel::class))
        );
        $this->app->bind(ProductRepositoryInterface::class, fn($app) =>
            new EloquentProductRepository($app->make(ProductModel::class))
        );
        $this->app->bind(ProductCategoryServiceInterface::class, fn($app) =>
            new ProductCategoryService($app->make(ProductCategoryRepositoryInterface::class))
        );
        $this->app->bind(ProductServiceInterface::class, fn($app) =>
            new ProductService($app->make(ProductRepositoryInterface::class))
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
    }
}
