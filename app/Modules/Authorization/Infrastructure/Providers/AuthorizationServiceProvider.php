<?php

declare(strict_types=1);

namespace Modules\Authorization\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Authorization\Application\Contracts\AssignPermissionServiceInterface;
use Modules\Authorization\Application\Contracts\AssignRoleToUserServiceInterface;
use Modules\Authorization\Application\Contracts\CheckUserPermissionServiceInterface;
use Modules\Authorization\Application\Contracts\CreateRoleServiceInterface;
use Modules\Authorization\Application\Contracts\DeleteRoleServiceInterface;
use Modules\Authorization\Application\Contracts\GetRoleServiceInterface;
use Modules\Authorization\Application\Contracts\GetUserPermissionsServiceInterface;
use Modules\Authorization\Application\Contracts\ListRolesServiceInterface;
use Modules\Authorization\Application\Contracts\RevokePermissionServiceInterface;
use Modules\Authorization\Application\Contracts\RevokeRoleFromUserServiceInterface;
use Modules\Authorization\Application\Contracts\UpdateRoleServiceInterface;
use Modules\Authorization\Application\Services\AssignPermissionService;
use Modules\Authorization\Application\Services\AssignRoleToUserService;
use Modules\Authorization\Application\Services\CheckUserPermissionService;
use Modules\Authorization\Application\Services\CreateRoleService;
use Modules\Authorization\Application\Services\DeleteRoleService;
use Modules\Authorization\Application\Services\GetRoleService;
use Modules\Authorization\Application\Services\GetUserPermissionsService;
use Modules\Authorization\Application\Services\ListRolesService;
use Modules\Authorization\Application\Services\RevokePermissionService;
use Modules\Authorization\Application\Services\RevokeRoleFromUserService;
use Modules\Authorization\Application\Services\UpdateRoleService;
use Modules\Authorization\Domain\RepositoryInterfaces\PermissionRepositoryInterface;
use Modules\Authorization\Domain\RepositoryInterfaces\RoleRepositoryInterface;
use Modules\Authorization\Domain\RepositoryInterfaces\UserRoleRepositoryInterface;
use Modules\Authorization\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use Modules\Authorization\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use Modules\Authorization\Infrastructure\Persistence\Eloquent\Models\UserRoleModel;
use Modules\Authorization\Infrastructure\Persistence\Eloquent\Repositories\EloquentPermissionRepository;
use Modules\Authorization\Infrastructure\Persistence\Eloquent\Repositories\EloquentRoleRepository;
use Modules\Authorization\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRoleRepository;

class AuthorizationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RoleRepositoryInterface::class, function ($app) {
            return new EloquentRoleRepository(
                $app->make(RoleModel::class),
                $app->make(PermissionModel::class),
            );
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

        $this->app->bind(CreateRoleServiceInterface::class, function ($app) {
            return new CreateRoleService($app->make(RoleRepositoryInterface::class));
        });

        $this->app->bind(UpdateRoleServiceInterface::class, function ($app) {
            return new UpdateRoleService($app->make(RoleRepositoryInterface::class));
        });

        $this->app->bind(DeleteRoleServiceInterface::class, function ($app) {
            return new DeleteRoleService($app->make(RoleRepositoryInterface::class));
        });

        $this->app->bind(GetRoleServiceInterface::class, function ($app) {
            return new GetRoleService($app->make(RoleRepositoryInterface::class));
        });

        $this->app->bind(ListRolesServiceInterface::class, function ($app) {
            return new ListRolesService($app->make(RoleRepositoryInterface::class));
        });

        $this->app->bind(AssignPermissionServiceInterface::class, function ($app) {
            return new AssignPermissionService(
                $app->make(RoleRepositoryInterface::class),
                $app->make(PermissionRepositoryInterface::class),
            );
        });

        $this->app->bind(RevokePermissionServiceInterface::class, function ($app) {
            return new RevokePermissionService(
                $app->make(RoleRepositoryInterface::class),
                $app->make(PermissionRepositoryInterface::class),
            );
        });

        $this->app->bind(AssignRoleToUserServiceInterface::class, function ($app) {
            return new AssignRoleToUserService(
                $app->make(UserRoleRepositoryInterface::class),
                $app->make(RoleRepositoryInterface::class),
            );
        });

        $this->app->bind(RevokeRoleFromUserServiceInterface::class, function ($app) {
            return new RevokeRoleFromUserService(
                $app->make(UserRoleRepositoryInterface::class),
                $app->make(RoleRepositoryInterface::class),
            );
        });

        $this->app->bind(GetUserPermissionsServiceInterface::class, function ($app) {
            return new GetUserPermissionsService(
                $app->make(UserRoleRepositoryInterface::class),
                $app->make(RoleRepositoryInterface::class),
            );
        });

        $this->app->bind(CheckUserPermissionServiceInterface::class, function ($app) {
            return new CheckUserPermissionService($app->make(UserRoleRepositoryInterface::class));
        });

        $this->app->bind(\Modules\Auth\Application\Contracts\AuthorizationServiceInterface::class, function ($app) {
            return new \Modules\Auth\Application\Services\AuthorizationService(
                $app->make(UserRoleRepositoryInterface::class),
            );
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
    }
}
