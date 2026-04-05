<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Product\Application\Contracts\CategoryServiceInterface;
use Modules\Product\Application\Contracts\ProductServiceInterface;
use Modules\Product\Application\Contracts\ProductVariantServiceInterface;
use Modules\Product\Application\Contracts\UnitOfMeasureServiceInterface;
use Modules\Product\Application\Services\CategoryService;
use Modules\Product\Application\Services\ProductService;
use Modules\Product\Application\Services\ProductVariantService;
use Modules\Product\Application\Services\UnitOfMeasureService;
use Modules\Product\Domain\RepositoryInterfaces\CategoryRepositoryInterface;
use Modules\Product\Domain\RepositoryInterfaces\ProductComponentRepositoryInterface;
use Modules\Product\Domain\RepositoryInterfaces\ProductRepositoryInterface;
use Modules\Product\Domain\RepositoryInterfaces\ProductVariantRepositoryInterface;
use Modules\Product\Domain\RepositoryInterfaces\UnitOfMeasureRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\CategoryModel;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductComponentModel;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductVariantModel;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\UnitOfMeasureModel;
use Modules\Product\Infrastructure\Persistence\Eloquent\Repositories\EloquentCategoryRepository;
use Modules\Product\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductComponentRepository;
use Modules\Product\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductRepository;
use Modules\Product\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductVariantRepository;
use Modules\Product\Infrastructure\Persistence\Eloquent\Repositories\EloquentUnitOfMeasureRepository;

class ProductServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(CategoryRepositoryInterface::class, fn ($app) => new EloquentCategoryRepository(
            $app->make(CategoryModel::class),
        ));

        $this->app->bind(ProductRepositoryInterface::class, fn ($app) => new EloquentProductRepository(
            $app->make(ProductModel::class),
        ));

        $this->app->bind(ProductVariantRepositoryInterface::class, fn ($app) => new EloquentProductVariantRepository(
            $app->make(ProductVariantModel::class),
        ));

        $this->app->bind(ProductComponentRepositoryInterface::class, fn ($app) => new EloquentProductComponentRepository(
            $app->make(ProductComponentModel::class),
        ));

        $this->app->bind(UnitOfMeasureRepositoryInterface::class, fn ($app) => new EloquentUnitOfMeasureRepository(
            $app->make(UnitOfMeasureModel::class),
        ));

        // Service bindings
        $this->app->bind(CategoryServiceInterface::class, fn ($app) => new CategoryService(
            $app->make(CategoryRepositoryInterface::class),
        ));

        $this->app->bind(ProductServiceInterface::class, fn ($app) => new ProductService(
            $app->make(ProductRepositoryInterface::class),
            $app->make(ProductVariantRepositoryInterface::class),
            $app->make(ProductComponentRepositoryInterface::class),
        ));

        $this->app->bind(ProductVariantServiceInterface::class, fn ($app) => new ProductVariantService(
            $app->make(ProductVariantRepositoryInterface::class),
        ));

        $this->app->bind(UnitOfMeasureServiceInterface::class, fn ($app) => new UnitOfMeasureService(
            $app->make(UnitOfMeasureRepositoryInterface::class),
        ));
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
