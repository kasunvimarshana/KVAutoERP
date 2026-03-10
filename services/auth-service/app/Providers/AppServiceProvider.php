<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Contracts\Repositories\UserRepositoryInterface;
use App\Application\Contracts\Services\AuthServiceInterface;
use App\Application\Contracts\Services\TenantConfigServiceInterface;
use App\Application\Services\AuthService;
use App\Infrastructure\Repositories\UserRepository;
use App\Infrastructure\Services\TenantConfigService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Passport;

/**
 * Application Service Provider
 * 
 * Binds interfaces to concrete implementations.
 * Configures Laravel Passport.
 */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);

        // Service bindings
        $this->app->singleton(TenantConfigServiceInterface::class, TenantConfigService::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
    }

    public function boot(): void
    {
        // Configure Passport token expiration
        Passport::tokensExpireIn(now()->addDays(1));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        // Define OAuth scopes for RBAC
        Passport::tokensCan([
            '*' => 'Full access (super admin)',
            'read' => 'Read-only access',
            'write' => 'Create and update records',
            'delete' => 'Delete records',
        ]);

        if (config('app.debug')) {
            DB::listen(function ($query) {
                Log::channel('daily')->debug('SQL', [
                    'sql' => $query->sql,
                    'time' => $query->time,
                ]);
            });
        }
    }
}
