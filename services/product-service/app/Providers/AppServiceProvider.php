<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Contracts\Repositories\ProductRepositoryInterface;
use App\Application\Contracts\Services\ProductServiceInterface;
use App\Application\Services\ProductService;
use App\Infrastructure\Repositories\ProductRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(ProductServiceInterface::class, ProductService::class);
    }

    public function boot(): void {}
}
