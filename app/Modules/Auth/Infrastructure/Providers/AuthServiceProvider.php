<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Auth\Application\Contracts\AuthServiceInterface;
use Modules\Auth\Application\Contracts\RoleServiceInterface;
use Modules\Auth\Application\Contracts\UserServiceInterface;
use Modules\Auth\Application\Services\AuthService;
use Modules\Auth\Application\Services\RoleService;
use Modules\Auth\Application\Services\UserService;
use Modules\Auth\Domain\RepositoryInterfaces\RoleRepositoryInterface;
use Modules\Auth\Domain\RepositoryInterfaces\UserRepositoryInterface;
use Modules\Auth\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use Modules\Auth\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Modules\Auth\Infrastructure\Persistence\Eloquent\Repositories\EloquentRoleRepository;
use Modules\Auth\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, function ($app) {
            return new EloquentUserRepository($app->make(UserModel::class));
        });

        $this->app->bind(RoleRepositoryInterface::class, function ($app) {
            return new EloquentRoleRepository($app->make(RoleModel::class));
        });

        $this->app->bind(UserServiceInterface::class, function ($app) {
            return new UserService($app->make(UserRepositoryInterface::class));
        });

        $this->app->bind(AuthServiceInterface::class, function ($app) {
            return new AuthService($app->make(UserRepositoryInterface::class));
        });

        $this->app->bind(RoleServiceInterface::class, function ($app) {
            return new RoleService($app->make(RoleRepositoryInterface::class));
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
