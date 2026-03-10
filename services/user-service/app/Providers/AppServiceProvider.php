<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Contracts\Repositories\UserProfileRepositoryInterface;
use App\Application\Contracts\Services\UserServiceInterface;
use App\Application\Services\UserService;
use App\Infrastructure\Repositories\UserProfileRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserProfileRepositoryInterface::class, UserProfileRepository::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
    }

    public function boot(): void {}
}
