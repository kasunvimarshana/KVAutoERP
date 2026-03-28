<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Application\Contracts\FileStorageServiceInterface;
use Modules\Product\Application\Contracts\CreateComboItemServiceInterface;
use Modules\Product\Application\Contracts\CreateProductServiceInterface;
use Modules\Product\Application\Contracts\CreateProductVariationServiceInterface;
use Modules\Product\Application\Contracts\DeleteComboItemServiceInterface;
use Modules\Product\Application\Contracts\DeleteProductImageServiceInterface;
use Modules\Product\Application\Contracts\DeleteProductServiceInterface;
use Modules\Product\Application\Contracts\DeleteProductVariationServiceInterface;
use Modules\Product\Application\Contracts\UpdateComboItemServiceInterface;
use Modules\Product\Application\Contracts\UpdateProductServiceInterface;
use Modules\Product\Application\Contracts\UpdateProductVariationServiceInterface;
use Modules\Product\Application\Contracts\UploadProductImageServiceInterface;
use Modules\Product\Application\Services\CreateComboItemService;
use Modules\Product\Application\Services\CreateProductService;
use Modules\Product\Application\Services\CreateProductVariationService;
use Modules\Product\Application\Services\DeleteComboItemService;
use Modules\Product\Application\Services\DeleteProductImageService;
use Modules\Product\Application\Services\DeleteProductService;
use Modules\Product\Application\Services\DeleteProductVariationService;
use Modules\Product\Application\Services\UpdateComboItemService;
use Modules\Product\Application\Services\UpdateProductService;
use Modules\Product\Application\Services\UpdateProductVariationService;
use Modules\Product\Application\Services\UploadProductImageService;
use Modules\Product\Domain\RepositoryInterfaces\ComboItemRepositoryInterface;
use Modules\Product\Domain\RepositoryInterfaces\ProductImageRepositoryInterface;
use Modules\Product\Domain\RepositoryInterfaces\ProductRepositoryInterface;
use Modules\Product\Domain\RepositoryInterfaces\ProductVariationRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductComboItemModel;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductImageModel;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductVariationModel;
use Modules\Product\Infrastructure\Persistence\Eloquent\Repositories\EloquentComboItemRepository;
use Modules\Product\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductImageRepository;
use Modules\Product\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductRepository;
use Modules\Product\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductVariationRepository;

class ProductServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ── Repositories ─────────────────────────────────────────────────────

        $this->app->bind(ProductRepositoryInterface::class, function ($app) {
            return new EloquentProductRepository($app->make(ProductModel::class));
        });

        $this->app->bind(ProductImageRepositoryInterface::class, function ($app) {
            return new EloquentProductImageRepository($app->make(ProductImageModel::class));
        });

        $this->app->bind(ProductVariationRepositoryInterface::class, function ($app) {
            return new EloquentProductVariationRepository($app->make(ProductVariationModel::class));
        });

        $this->app->bind(ComboItemRepositoryInterface::class, function ($app) {
            return new EloquentComboItemRepository($app->make(ProductComboItemModel::class));
        });

        // ── Application Services ──────────────────────────────────────────────

        $this->app->bind(CreateProductServiceInterface::class, function ($app) {
            return new CreateProductService($app->make(ProductRepositoryInterface::class));
        });

        $this->app->bind(UpdateProductServiceInterface::class, function ($app) {
            return new UpdateProductService($app->make(ProductRepositoryInterface::class));
        });

        $this->app->bind(DeleteProductServiceInterface::class, function ($app) {
            return new DeleteProductService($app->make(ProductRepositoryInterface::class));
        });

        $this->app->bind(UploadProductImageServiceInterface::class, function ($app) {
            return new UploadProductImageService(
                $app->make(ProductRepositoryInterface::class),
                $app->make(ProductImageRepositoryInterface::class),
                $app->make(FileStorageServiceInterface::class)
            );
        });

        $this->app->bind(DeleteProductImageServiceInterface::class, function ($app) {
            return new DeleteProductImageService(
                $app->make(ProductImageRepositoryInterface::class),
                $app->make(FileStorageServiceInterface::class)
            );
        });

        $this->app->bind(CreateProductVariationServiceInterface::class, function ($app) {
            return new CreateProductVariationService(
                $app->make(ProductRepositoryInterface::class),
                $app->make(ProductVariationRepositoryInterface::class),
            );
        });

        $this->app->bind(UpdateProductVariationServiceInterface::class, function ($app) {
            return new UpdateProductVariationService($app->make(ProductVariationRepositoryInterface::class));
        });

        $this->app->bind(DeleteProductVariationServiceInterface::class, function ($app) {
            return new DeleteProductVariationService($app->make(ProductVariationRepositoryInterface::class));
        });

        $this->app->bind(CreateComboItemServiceInterface::class, function ($app) {
            return new CreateComboItemService(
                $app->make(ProductRepositoryInterface::class),
                $app->make(ComboItemRepositoryInterface::class),
            );
        });

        $this->app->bind(UpdateComboItemServiceInterface::class, function ($app) {
            return new UpdateComboItemService($app->make(ComboItemRepositoryInterface::class));
        });

        $this->app->bind(DeleteComboItemServiceInterface::class, function ($app) {
            return new DeleteComboItemService($app->make(ComboItemRepositoryInterface::class));
        });
    }

    public function boot(): void
    {
        Route::middleware('api')
             ->prefix('api')
             ->group(function () {
                 $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
             });

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
