<?php declare(strict_types=1);
namespace Modules\Configuration\Infrastructure\Providers;
use Illuminate\Support\ServiceProvider;
use Modules\Configuration\Application\Services\OrgUnitService;
use Modules\Configuration\Domain\RepositoryInterfaces\OrgUnitRepositoryInterface;
use Modules\Configuration\Infrastructure\Persistence\Eloquent\Models\OrgUnitModel;
use Modules\Configuration\Infrastructure\Persistence\Eloquent\Repositories\EloquentOrgUnitRepository;
class ConfigurationServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(OrgUnitRepositoryInterface::class, fn($app) => new EloquentOrgUnitRepository($app->make(OrgUnitModel::class)));
        $this->app->bind(OrgUnitService::class, fn($app) => new OrgUnitService($app->make(OrgUnitRepositoryInterface::class)));
    }
    public function boot(): void {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
