<?php

namespace App\Providers;

use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Events\ProductDeleted;
use App\Modules\Product\Events\ProductUpdated;
use App\Modules\Product\Listeners\PublishProductCreated;
use App\Modules\Product\Listeners\PublishProductDeleted;
use App\Modules\Product\Listeners\PublishProductUpdated;
use App\Modules\Product\Repositories\Contracts\ProductRepositoryInterface;
use App\Modules\Product\Repositories\ProductRepository;
use App\Modules\Product\Services\Contracts\ProductServiceInterface;
use App\Modules\Product\Services\ProductService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ProductServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(ProductServiceInterface::class, ProductService::class);
    }

    public function boot(): void
    {
        $this->registerEventListeners();
        $this->loadRoutes();
    }

    private function registerEventListeners(): void
    {
        Event::listen(ProductCreated::class, PublishProductCreated::class);
        Event::listen(ProductUpdated::class, PublishProductUpdated::class);
        Event::listen(ProductDeleted::class, PublishProductDeleted::class);
    }

    private function loadRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('app/Modules/Product/Routes/api.php'));
    }
}
