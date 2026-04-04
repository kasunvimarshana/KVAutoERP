<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Product\Application\Contracts\CreateProductCategoryServiceInterface;
use Modules\Product\Application\Contracts\CreateProductServiceInterface;
use Modules\Product\Application\Contracts\CreateProductVariantServiceInterface;
use Modules\Product\Application\Contracts\DeleteProductCategoryServiceInterface;
use Modules\Product\Application\Contracts\DeleteProductServiceInterface;
use Modules\Product\Application\Contracts\DeleteProductVariantServiceInterface;
use Modules\Product\Application\Contracts\GetProductCategoryTreeServiceInterface;
use Modules\Product\Application\Contracts\GetProductServiceInterface;
use Modules\Product\Application\Contracts\ListProductsServiceInterface;
use Modules\Product\Application\Contracts\UpdateProductCategoryServiceInterface;
use Modules\Product\Application\Contracts\UpdateProductServiceInterface;
use Modules\Product\Application\Contracts\UpdateProductVariantServiceInterface;
use Modules\Product\Application\Services\CreateProductCategoryService;
use Modules\Product\Application\Services\CreateProductService;
use Modules\Product\Application\Services\CreateProductVariantService;
use Modules\Product\Application\Services\DeleteProductCategoryService;
use Modules\Product\Application\Services\DeleteProductService;
use Modules\Product\Application\Services\DeleteProductVariantService;
use Modules\Product\Application\Services\GetProductCategoryTreeService;
use Modules\Product\Application\Services\GetProductService;
use Modules\Product\Application\Services\ListProductsService;
use Modules\Product\Application\Services\UpdateProductCategoryService;
use Modules\Product\Application\Services\UpdateProductService;
use Modules\Product\Application\Services\UpdateProductVariantService;
use Modules\Product\Domain\Repositories\ProductAttributeRepositoryInterface;
use Modules\Product\Domain\Repositories\ProductCategoryRepositoryInterface;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use Modules\Product\Domain\Repositories\ProductVariantRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Models\ProductAttributeModel;
use Modules\Product\Infrastructure\Persistence\Models\ProductCategoryClosureModel;
use Modules\Product\Infrastructure\Persistence\Models\ProductCategoryModel;
use Modules\Product\Infrastructure\Persistence\Models\ProductModel;
use Modules\Product\Infrastructure\Persistence\Models\ProductVariantModel;
use Modules\Product\Infrastructure\Persistence\Repositories\EloquentProductAttributeRepository;
use Modules\Product\Infrastructure\Persistence\Repositories\EloquentProductCategoryRepository;
use Modules\Product\Infrastructure\Persistence\Repositories\EloquentProductRepository;
use Modules\Product\Infrastructure\Persistence\Repositories\EloquentProductVariantRepository;

class ProductServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductCategoryRepositoryInterface::class, function ($app) {
            return new EloquentProductCategoryRepository(
                $app->make(ProductCategoryModel::class),
                $app->make(ProductCategoryClosureModel::class),
            );
        });

        $this->app->bind(ProductRepositoryInterface::class, function ($app) {
            return new EloquentProductRepository($app->make(ProductModel::class));
        });

        $this->app->bind(ProductVariantRepositoryInterface::class, function ($app) {
            return new EloquentProductVariantRepository($app->make(ProductVariantModel::class));
        });

        $this->app->bind(ProductAttributeRepositoryInterface::class, function ($app) {
            return new EloquentProductAttributeRepository($app->make(ProductAttributeModel::class));
        });

        $this->app->bind(CreateProductCategoryServiceInterface::class, function ($app) {
            return new CreateProductCategoryService($app->make(ProductCategoryRepositoryInterface::class));
        });

        $this->app->bind(UpdateProductCategoryServiceInterface::class, function ($app) {
            return new UpdateProductCategoryService($app->make(ProductCategoryRepositoryInterface::class));
        });

        $this->app->bind(DeleteProductCategoryServiceInterface::class, function ($app) {
            return new DeleteProductCategoryService($app->make(ProductCategoryRepositoryInterface::class));
        });

        $this->app->bind(GetProductCategoryTreeServiceInterface::class, function ($app) {
            return new GetProductCategoryTreeService($app->make(ProductCategoryRepositoryInterface::class));
        });

        $this->app->bind(CreateProductServiceInterface::class, function ($app) {
            return new CreateProductService($app->make(ProductRepositoryInterface::class));
        });

        $this->app->bind(UpdateProductServiceInterface::class, function ($app) {
            return new UpdateProductService($app->make(ProductRepositoryInterface::class));
        });

        $this->app->bind(DeleteProductServiceInterface::class, function ($app) {
            return new DeleteProductService($app->make(ProductRepositoryInterface::class));
        });

        $this->app->bind(GetProductServiceInterface::class, function ($app) {
            return new GetProductService($app->make(ProductRepositoryInterface::class));
        });

        $this->app->bind(ListProductsServiceInterface::class, function ($app) {
            return new ListProductsService($app->make(ProductRepositoryInterface::class));
        });

        $this->app->bind(CreateProductVariantServiceInterface::class, function ($app) {
            return new CreateProductVariantService($app->make(ProductVariantRepositoryInterface::class));
        });

        $this->app->bind(UpdateProductVariantServiceInterface::class, function ($app) {
            return new UpdateProductVariantService($app->make(ProductVariantRepositoryInterface::class));
        });

        $this->app->bind(DeleteProductVariantServiceInterface::class, function ($app) {
            return new DeleteProductVariantService($app->make(ProductVariantRepositoryInterface::class));
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        Route::group([], function () {
            $routeFile = __DIR__.'/../../routes/api.php';
            if (file_exists($routeFile)) {
                require $routeFile;
            }
        });
    }
}
