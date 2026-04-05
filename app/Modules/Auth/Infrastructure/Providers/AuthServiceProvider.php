<?php declare(strict_types=1);
namespace Modules\Auth\Infrastructure\Providers;
use Illuminate\Support\ServiceProvider;
use Modules\Auth\Application\Contracts\UserServiceInterface;
use Modules\Auth\Application\Services\UserService;
use Modules\Auth\Domain\RepositoryInterfaces\UserRepositoryInterface;
use Modules\Auth\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Modules\Auth\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository;
class AuthServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(UserRepositoryInterface::class, fn($app) => new EloquentUserRepository($app->make(UserModel::class)));
        $this->app->bind(UserServiceInterface::class, fn($app) => new UserService($app->make(UserRepositoryInterface::class)));
    }
    public function boot(): void {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
