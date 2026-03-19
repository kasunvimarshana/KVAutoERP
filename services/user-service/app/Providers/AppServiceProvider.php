<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Contracts\Repositories\UserProfileRepositoryInterface;
use App\Contracts\Services\PermissionServiceInterface;
use App\Contracts\Services\RoleServiceInterface;
use App\Contracts\Services\UserProfileServiceInterface;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserProfileRepository;
use App\Services\PermissionService;
use App\Services\RoleService;
use App\Services\UserProfileService;
use Illuminate\Support\ServiceProvider;
use Predis\Client as PredisClient;

/**
 * Application service provider — binds all interfaces to concrete implementations.
 */
final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Redis client (Predis) — singleton for the lifetime of the request.
        $this->app->singleton(PredisClient::class, function (): PredisClient {
            return new PredisClient([
                'scheme'   => 'tcp',
                'host'     => (string) config('database.redis.default.host', '127.0.0.1'),
                'port'     => (int)    config('database.redis.default.port', 6379),
                'password' => config('database.redis.default.password') ?: null,
                'database' => (int)    config('database.redis.default.database', 0),
            ]);
        });

        // Repository bindings.
        $this->app->bind(UserProfileRepositoryInterface::class, UserProfileRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);

        // Service bindings.
        $this->app->bind(UserProfileServiceInterface::class, UserProfileService::class);
        $this->app->bind(RoleServiceInterface::class, RoleService::class);
        $this->app->bind(PermissionServiceInterface::class, PermissionService::class);
    }

    /**
     * Bootstrap application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
