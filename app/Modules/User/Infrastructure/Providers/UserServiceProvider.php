<?php

declare(strict_types=1);

namespace Modules\User\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\User\Application\Contracts\ChangePasswordServiceInterface;
use Modules\User\Application\Contracts\CreateUserServiceInterface;
use Modules\User\Application\Contracts\DeleteUserServiceInterface;
use Modules\User\Application\Contracts\GetUserServiceInterface;
use Modules\User\Application\Contracts\ListUsersServiceInterface;
use Modules\User\Application\Contracts\UpdateProfileServiceInterface;
use Modules\User\Application\Contracts\UpdateUserServiceInterface;
use Modules\User\Application\Contracts\UploadAvatarServiceInterface;
use Modules\User\Application\Services\ChangePasswordService;
use Modules\User\Application\Services\CreateUserService;
use Modules\User\Application\Services\DeleteUserService;
use Modules\User\Application\Services\GetUserService;
use Modules\User\Application\Services\ListUsersService;
use Modules\User\Application\Services\UpdateProfileService;
use Modules\User\Application\Services\UpdateUserService;
use Modules\User\Application\Services\UploadAvatarService;
use Modules\User\Domain\RepositoryInterfaces\UserRepositoryInterface;
use Modules\User\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Modules\User\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository;

class UserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, function ($app) {
            return new EloquentUserRepository($app->make(UserModel::class));
        });

        $this->app->bind(CreateUserServiceInterface::class, function ($app) {
            return new CreateUserService($app->make(UserRepositoryInterface::class));
        });

        $this->app->bind(UpdateUserServiceInterface::class, function ($app) {
            return new UpdateUserService($app->make(UserRepositoryInterface::class));
        });

        $this->app->bind(DeleteUserServiceInterface::class, function ($app) {
            return new DeleteUserService($app->make(UserRepositoryInterface::class));
        });

        $this->app->bind(GetUserServiceInterface::class, function ($app) {
            return new GetUserService($app->make(UserRepositoryInterface::class));
        });

        $this->app->bind(ListUsersServiceInterface::class, function ($app) {
            return new ListUsersService($app->make(UserRepositoryInterface::class));
        });

        $this->app->bind(UpdateProfileServiceInterface::class, function ($app) {
            return new UpdateProfileService($app->make(UserRepositoryInterface::class));
        });

        $this->app->bind(ChangePasswordServiceInterface::class, function ($app) {
            return new ChangePasswordService($app->make(UserRepositoryInterface::class));
        });

        $this->app->bind(UploadAvatarServiceInterface::class, function ($app) {
            return new UploadAvatarService($app->make(UserRepositoryInterface::class));
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
    }
}
