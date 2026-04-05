<?php declare(strict_types=1);
namespace Modules\HR\Infrastructure\Providers;
use Illuminate\Support\ServiceProvider;
use Modules\HR\Domain\RepositoryInterfaces\DepartmentRepositoryInterface;
use Modules\HR\Domain\RepositoryInterfaces\EmployeeRepositoryInterface;
use Modules\HR\Infrastructure\Persistence\Eloquent\Models\DepartmentModel;
use Modules\HR\Infrastructure\Persistence\Eloquent\Models\EmployeeModel;
use Modules\HR\Infrastructure\Persistence\Eloquent\Repositories\EloquentDepartmentRepository;
use Modules\HR\Infrastructure\Persistence\Eloquent\Repositories\EloquentEmployeeRepository;
class HRServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(DepartmentRepositoryInterface::class, fn($app)=>new EloquentDepartmentRepository($app->make(DepartmentModel::class)));
        $this->app->bind(EmployeeRepositoryInterface::class, fn($app)=>new EloquentEmployeeRepository($app->make(EmployeeModel::class)));
    }
    public function boot(): void { $this->loadMigrationsFrom(__DIR__.'/../../database/migrations'); }
}
