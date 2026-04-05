<?php declare(strict_types=1);
namespace Modules\Tenant\Infrastructure\Providers;
use Illuminate\Support\ServiceProvider;
use Modules\Tenant\Application\Contracts\TenantServiceInterface;
use Modules\Tenant\Application\Services\TenantService;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use Modules\Tenant\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use Modules\Tenant\Infrastructure\Persistence\Eloquent\Repositories\EloquentTenantRepository;
class TenantServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(TenantRepositoryInterface::class, fn($app) => new EloquentTenantRepository($app->make(TenantModel::class)));
        $this->app->bind(TenantServiceInterface::class, fn($app) => new TenantService($app->make(TenantRepositoryInterface::class)));
    }
    public function boot(): void {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
