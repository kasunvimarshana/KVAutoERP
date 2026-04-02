<?php

declare(strict_types=1);

namespace Modules\Returns\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Returns\Application\Contracts\ApproveStockReturnServiceInterface;
use Modules\Returns\Application\Contracts\CancelStockReturnServiceInterface;
use Modules\Returns\Application\Contracts\CompleteStockReturnServiceInterface;
use Modules\Returns\Application\Contracts\CreateStockReturnLineServiceInterface;
use Modules\Returns\Application\Contracts\CreateStockReturnServiceInterface;
use Modules\Returns\Application\Contracts\DeleteStockReturnLineServiceInterface;
use Modules\Returns\Application\Contracts\DeleteStockReturnServiceInterface;
use Modules\Returns\Application\Contracts\FindStockReturnLineServiceInterface;
use Modules\Returns\Application\Contracts\FindStockReturnServiceInterface;
use Modules\Returns\Application\Contracts\IssueCreditMemoServiceInterface;
use Modules\Returns\Application\Contracts\RejectStockReturnServiceInterface;
use Modules\Returns\Application\Contracts\UpdateStockReturnLineServiceInterface;
use Modules\Returns\Application\Contracts\UpdateStockReturnServiceInterface;
use Modules\Returns\Application\Services\ApproveStockReturnService;
use Modules\Returns\Application\Services\CancelStockReturnService;
use Modules\Returns\Application\Services\CompleteStockReturnService;
use Modules\Returns\Application\Services\CreateStockReturnLineService;
use Modules\Returns\Application\Services\CreateStockReturnService;
use Modules\Returns\Application\Services\DeleteStockReturnLineService;
use Modules\Returns\Application\Services\DeleteStockReturnService;
use Modules\Returns\Application\Services\FindStockReturnLineService;
use Modules\Returns\Application\Services\FindStockReturnService;
use Modules\Returns\Application\Services\IssueCreditMemoService;
use Modules\Returns\Application\Services\RejectStockReturnService;
use Modules\Returns\Application\Services\UpdateStockReturnLineService;
use Modules\Returns\Application\Services\UpdateStockReturnService;
use Modules\Returns\Domain\RepositoryInterfaces\StockReturnLineRepositoryInterface;
use Modules\Returns\Domain\RepositoryInterfaces\StockReturnRepositoryInterface;
use Modules\Returns\Infrastructure\Persistence\Eloquent\Models\StockReturnLineModel;
use Modules\Returns\Infrastructure\Persistence\Eloquent\Models\StockReturnModel;
use Modules\Returns\Infrastructure\Persistence\Eloquent\Repositories\EloquentStockReturnLineRepository;
use Modules\Returns\Infrastructure\Persistence\Eloquent\Repositories\EloquentStockReturnRepository;

class ReturnsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // --- Repositories ---
        $this->app->bind(StockReturnRepositoryInterface::class, fn ($app) =>
            new EloquentStockReturnRepository($app->make(StockReturnModel::class)));

        $this->app->bind(StockReturnLineRepositoryInterface::class, fn ($app) =>
            new EloquentStockReturnLineRepository($app->make(StockReturnLineModel::class)));

        // --- Services: StockReturn ---
        $this->app->bind(CreateStockReturnServiceInterface::class, fn ($app) =>
            new CreateStockReturnService($app->make(StockReturnRepositoryInterface::class)));

        $this->app->bind(FindStockReturnServiceInterface::class, fn ($app) =>
            new FindStockReturnService($app->make(StockReturnRepositoryInterface::class)));

        $this->app->bind(UpdateStockReturnServiceInterface::class, fn ($app) =>
            new UpdateStockReturnService($app->make(StockReturnRepositoryInterface::class)));

        $this->app->bind(DeleteStockReturnServiceInterface::class, fn ($app) =>
            new DeleteStockReturnService($app->make(StockReturnRepositoryInterface::class)));

        $this->app->bind(ApproveStockReturnServiceInterface::class, fn ($app) =>
            new ApproveStockReturnService($app->make(StockReturnRepositoryInterface::class)));

        $this->app->bind(RejectStockReturnServiceInterface::class, fn ($app) =>
            new RejectStockReturnService($app->make(StockReturnRepositoryInterface::class)));

        $this->app->bind(CompleteStockReturnServiceInterface::class, fn ($app) =>
            new CompleteStockReturnService($app->make(StockReturnRepositoryInterface::class)));

        $this->app->bind(CancelStockReturnServiceInterface::class, fn ($app) =>
            new CancelStockReturnService($app->make(StockReturnRepositoryInterface::class)));

        $this->app->bind(IssueCreditMemoServiceInterface::class, fn ($app) =>
            new IssueCreditMemoService($app->make(StockReturnRepositoryInterface::class)));

        // --- Services: StockReturnLine ---
        $this->app->bind(CreateStockReturnLineServiceInterface::class, fn ($app) =>
            new CreateStockReturnLineService($app->make(StockReturnLineRepositoryInterface::class)));

        $this->app->bind(FindStockReturnLineServiceInterface::class, fn ($app) =>
            new FindStockReturnLineService($app->make(StockReturnLineRepositoryInterface::class)));

        $this->app->bind(UpdateStockReturnLineServiceInterface::class, fn ($app) =>
            new UpdateStockReturnLineService($app->make(StockReturnLineRepositoryInterface::class)));

        $this->app->bind(DeleteStockReturnLineServiceInterface::class, fn ($app) =>
            new DeleteStockReturnLineService($app->make(StockReturnLineRepositoryInterface::class)));
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        Route::middleware(['api', 'auth:api', 'resolve.tenant'])
            ->prefix('api')
            ->group(function () {
                $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
            });
    }
}
