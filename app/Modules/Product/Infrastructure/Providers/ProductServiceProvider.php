<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Product\Application\Contracts\AttachmentServiceInterface;
use Modules\Product\Application\Contracts\CategoryServiceInterface;
use Modules\Product\Application\Contracts\ProductComponentServiceInterface;
use Modules\Product\Application\Contracts\ProductServiceInterface;
use Modules\Product\Application\Contracts\ProductVariantServiceInterface;
use Modules\Product\Application\Services\AttachmentService;
use Modules\Product\Application\Services\CategoryService;
use Modules\Product\Application\Services\ProductComponentService;
use Modules\Product\Application\Services\ProductService;
use Modules\Product\Application\Services\ProductVariantService;
use Modules\Product\Domain\RepositoryInterfaces\AttachmentRepositoryInterface;
use Modules\Product\Domain\RepositoryInterfaces\CategoryRepositoryInterface;
use Modules\Product\Domain\RepositoryInterfaces\ProductComponentRepositoryInterface;
use Modules\Product\Domain\RepositoryInterfaces\ProductRepositoryInterface;
use Modules\Product\Domain\RepositoryInterfaces\ProductVariantRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\AttachmentModel;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\CategoryModel;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductComponentModel;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductVariantModel;
use Modules\Product\Infrastructure\Persistence\Eloquent\Repositories\EloquentAttachmentRepository;
use Modules\Product\Infrastructure\Persistence\Eloquent\Repositories\EloquentCategoryRepository;
use Modules\Product\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductComponentRepository;
use Modules\Product\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductRepository;
use Modules\Product\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductVariantRepository;

class ProductServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CategoryRepositoryInterface::class, function ($app) {
            return new EloquentCategoryRepository($app->make(CategoryModel::class));
        });

        $this->app->bind(ProductRepositoryInterface::class, function ($app) {
            return new EloquentProductRepository($app->make(ProductModel::class));
        });

        $this->app->bind(ProductVariantRepositoryInterface::class, function ($app) {
            return new EloquentProductVariantRepository($app->make(ProductVariantModel::class));
        });

        $this->app->bind(ProductComponentRepositoryInterface::class, function ($app) {
            return new EloquentProductComponentRepository($app->make(ProductComponentModel::class));
        });

        $this->app->bind(AttachmentRepositoryInterface::class, function ($app) {
            return new EloquentAttachmentRepository($app->make(AttachmentModel::class));
        });

        $this->app->bind(CategoryServiceInterface::class, function ($app) {
            return new CategoryService($app->make(CategoryRepositoryInterface::class));
        });

        $this->app->bind(ProductServiceInterface::class, function ($app) {
            return new ProductService($app->make(ProductRepositoryInterface::class));
        });

        $this->app->bind(ProductVariantServiceInterface::class, function ($app) {
            return new ProductVariantService($app->make(ProductVariantRepositoryInterface::class));
        });

        $this->app->bind(ProductComponentServiceInterface::class, function ($app) {
            return new ProductComponentService($app->make(ProductComponentRepositoryInterface::class));
        });

        $this->app->bind(AttachmentServiceInterface::class, function ($app) {
            return new AttachmentService($app->make(AttachmentRepositoryInterface::class));
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
