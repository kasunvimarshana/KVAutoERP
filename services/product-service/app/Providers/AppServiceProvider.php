<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\UomRepositoryInterface;
use App\Contracts\Services\CategoryServiceInterface;
use App\Contracts\Services\ProductServiceInterface;
use App\Contracts\Services\UomServiceInterface;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UomRepository;
use App\Services\CategoryService;
use App\Services\ProductService;
use App\Services\UomService;
use Illuminate\Support\ServiceProvider;

/**
 * Core application service provider.
 *
 * Binds all product-domain interfaces to their concrete implementations
 * and loads service-specific configuration files.
 */
final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(UomRepositoryInterface::class, UomRepository::class);

        $this->app->bind(ProductServiceInterface::class, ProductService::class);
        $this->app->bind(CategoryServiceInterface::class, CategoryService::class);
        $this->app->bind(UomServiceInterface::class, UomService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
