<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Auth\Application\Contracts\AssignUserRoleServiceInterface;
use Modules\Auth\Application\Contracts\AuthorizationServiceInterface;
use Modules\Auth\Application\Contracts\CheckPermissionServiceInterface;
use Modules\Auth\Application\Contracts\CreateRoleServiceInterface;
use Modules\Auth\Application\Contracts\DeleteRoleServiceInterface;
use Modules\Auth\Application\Contracts\GetUserPermissionsServiceInterface;
use Modules\Auth\Application\Contracts\RevokeUserRoleServiceInterface;
use Modules\Auth\Application\Contracts\SyncRolePermissionsServiceInterface;
use Modules\Auth\Application\Contracts\UpdateRoleServiceInterface;
use Modules\Auth\Application\Services\AssignUserRoleService;
use Modules\Auth\Application\Services\AuthorizationService;
use Modules\Auth\Application\Services\CheckPermissionService;
use Modules\Auth\Application\Services\CreateRoleService;
use Modules\Auth\Application\Services\DeleteRoleService;
use Modules\Auth\Application\Services\GetUserPermissionsService;
use Modules\Auth\Application\Services\RevokeUserRoleService;
use Modules\Auth\Application\Services\SyncRolePermissionsService;
use Modules\Auth\Application\Services\UpdateRoleService;
use Modules\Auth\Domain\Repositories\PermissionRepositoryInterface;
use Modules\Auth\Domain\Repositories\RoleRepositoryInterface;
use Modules\Auth\Domain\Repositories\UserRoleRepositoryInterface;
use Modules\Auth\Infrastructure\Persistence\Models\PermissionModel;
use Modules\Auth\Infrastructure\Persistence\Models\RoleModel;
use Modules\Auth\Infrastructure\Persistence\Models\UserRoleModel;
use Modules\Auth\Infrastructure\Persistence\Repositories\EloquentPermissionRepository;
use Modules\Auth\Infrastructure\Persistence\Repositories\EloquentRoleRepository;
use Modules\Auth\Infrastructure\Persistence\Repositories\EloquentUserRoleRepository;

class AuthorizationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RoleRepositoryInterface::class, function ($app) {
            return new EloquentRoleRepository($app->make(RoleModel::class));
        });

        $this->app->bind(PermissionRepositoryInterface::class, function ($app) {
            return new EloquentPermissionRepository($app->make(PermissionModel::class));
        });

        $this->app->bind(UserRoleRepositoryInterface::class, function ($app) {
            return new EloquentUserRoleRepository(
                $app->make(UserRoleModel::class),
                $app->make(RoleModel::class),
            );
        });

        $this->app->singleton(AuthorizationServiceInterface::class, function ($app) {
            return new AuthorizationService($app->make(UserRoleRepositoryInterface::class));
        });

        $this->app->bind(CreateRoleServiceInterface::class, function ($app) {
            return new CreateRoleService($app->make(RoleRepositoryInterface::class));
        });

        $this->app->bind(UpdateRoleServiceInterface::class, function ($app) {
            return new UpdateRoleService($app->make(RoleRepositoryInterface::class));
        });

        $this->app->bind(DeleteRoleServiceInterface::class, function ($app) {
            return new DeleteRoleService($app->make(RoleRepositoryInterface::class));
        });

        $this->app->bind(SyncRolePermissionsServiceInterface::class, function ($app) {
            return new SyncRolePermissionsService($app->make(RoleRepositoryInterface::class));
        });

        $this->app->bind(AssignUserRoleServiceInterface::class, function ($app) {
            return new AssignUserRoleService($app->make(UserRoleRepositoryInterface::class));
        });

        $this->app->bind(RevokeUserRoleServiceInterface::class, function ($app) {
            return new RevokeUserRoleService($app->make(UserRoleRepositoryInterface::class));
        });

        $this->app->bind(GetUserPermissionsServiceInterface::class, function ($app) {
            return new GetUserPermissionsService($app->make(UserRoleRepositoryInterface::class));
        });

        $this->app->bind(CheckPermissionServiceInterface::class, function ($app) {
            return new CheckPermissionService($app->make(UserRoleRepositoryInterface::class));
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        Route::group([], function () {
            $routeFile = __DIR__.'/../../routes/api.php';
            if (file_exists($routeFile)) {
                require $routeFile;
            }
        });
    }
}
