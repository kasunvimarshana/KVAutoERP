<?php

declare(strict_types=1);

namespace Modules\UserProfile\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\UserProfile\Application\Contracts\UserProfileServiceInterface;
use Modules\UserProfile\Application\Services\UserProfileService;
use Modules\UserProfile\Domain\RepositoryInterfaces\UserProfileRepositoryInterface;
use Modules\UserProfile\Infrastructure\Persistence\Eloquent\Models\UserProfileModel;
use Modules\UserProfile\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserProfileRepository;

class UserProfileServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserProfileRepositoryInterface::class, function ($app) {
            return new EloquentUserProfileRepository($app->make(UserProfileModel::class));
        });

        $this->app->bind(UserProfileServiceInterface::class, function ($app) {
            return new UserProfileService(
                $app->make(UserProfileRepositoryInterface::class),
            );
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
