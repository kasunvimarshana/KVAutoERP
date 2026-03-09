<?php

declare(strict_types=1);

namespace App\Modules\Auth\Providers;

use App\Core\Authorization\ABAC\Abac;
use App\Core\Authorization\ABAC\AbacPolicyRegistrar;
use App\Modules\Auth\Application\Services\AuthService;
use App\Modules\Auth\Domain\Models\User;
use App\Modules\Auth\Infrastructure\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

/**
 * AuthServiceProvider
 *
 * Registers auth-module bindings, Passport configuration, and ABAC policies.
 */
class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UserRepository::class);
        $this->app->singleton(AuthService::class);

        // ABAC singleton
        $this->app->singleton(Abac::class);
        $this->app->singleton(AbacPolicyRegistrar::class, fn ($app) => new AbacPolicyRegistrar(
            $app->make(Abac::class)
        ));
    }

    public function boot(): void
    {
        // Configure Passport token lifetimes
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        // Register ABAC policies
        $this->app->make(AbacPolicyRegistrar::class)->register();

        // Override the default user model to use our DDD model
        Auth::provider('eloquent', function ($app, array $config) {
            return new \Illuminate\Auth\EloquentUserProvider(
                $app['hash'],
                $config['model']
            );
        });
    }
}
