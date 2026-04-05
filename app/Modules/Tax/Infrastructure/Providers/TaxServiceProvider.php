<?php declare(strict_types=1);
namespace Modules\Tax\Infrastructure\Providers;
use Illuminate\Support\ServiceProvider;
use Modules\Tax\Application\Contracts\CalculateTaxServiceInterface;
use Modules\Tax\Application\Services\CalculateTaxService;
use Modules\Tax\Domain\RepositoryInterfaces\TaxGroupRepositoryInterface;
use Modules\Tax\Infrastructure\Persistence\Eloquent\Models\TaxGroupModel;
use Modules\Tax\Infrastructure\Persistence\Eloquent\Models\TaxGroupRateModel;
use Modules\Tax\Infrastructure\Persistence\Eloquent\Repositories\EloquentTaxGroupRepository;
class TaxServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(TaxGroupRepositoryInterface::class, fn($app) => new EloquentTaxGroupRepository($app->make(TaxGroupModel::class), $app->make(TaxGroupRateModel::class)));
        $this->app->bind(CalculateTaxServiceInterface::class, fn() => new CalculateTaxService());
    }
    public function boot(): void {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
