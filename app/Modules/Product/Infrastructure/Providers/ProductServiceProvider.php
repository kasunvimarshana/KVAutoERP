<?php declare(strict_types=1);
namespace Modules\Product\Infrastructure\Providers;
use Illuminate\Support\ServiceProvider;
use Modules\Product\Application\Contracts\ProductServiceInterface;
use Modules\Product\Application\Services\ProductService;
use Modules\Product\Domain\RepositoryInterfaces\ProductComponentRepositoryInterface;
use Modules\Product\Domain\RepositoryInterfaces\ProductRepositoryInterface;
use Modules\Product\Domain\RepositoryInterfaces\ProductVariantRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductComponentModel;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductVariantModel;
use Modules\Product\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductComponentRepository;
use Modules\Product\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductRepository;
use Modules\Product\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductVariantRepository;
class ProductServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(ProductRepositoryInterface::class, fn($app)=>new EloquentProductRepository($app->make(ProductModel::class)));
        $this->app->bind(ProductVariantRepositoryInterface::class, fn($app)=>new EloquentProductVariantRepository($app->make(ProductVariantModel::class)));
        $this->app->bind(ProductComponentRepositoryInterface::class, fn($app)=>new EloquentProductComponentRepository($app->make(ProductComponentModel::class)));
        $this->app->bind(ProductServiceInterface::class, fn($app)=>new ProductService($app->make(ProductRepositoryInterface::class)));
    }
    public function boot(): void { $this->loadMigrationsFrom(__DIR__.'/../../database/migrations'); }
}
