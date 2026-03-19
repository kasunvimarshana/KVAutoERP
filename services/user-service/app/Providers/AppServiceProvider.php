<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\UserProfileRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Repositories\UserProfileRepository;
use App\Repositories\UserRepository;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * All interface → implementation bindings for the User service.
     * This is the single place to swap implementations at runtime.
     */
    private array $repositoryBindings = [
        UserRepositoryInterface::class        => UserRepository::class,
        UserProfileRepositoryInterface::class => UserProfileRepository::class,
    ];

    private array $serviceBindings = [
        UserServiceInterface::class => UserService::class,
    ];

    public function register(): void
    {
        foreach ($this->repositoryBindings as $abstract => $concrete) {
            $this->app->singleton($abstract, $concrete);
        }

        foreach ($this->serviceBindings as $abstract => $concrete) {
            $this->app->singleton($abstract, $concrete);
        }
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
