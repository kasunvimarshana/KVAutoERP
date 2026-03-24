<?php

namespace Modules\Auth\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Modules\Auth\Application\Contracts\AuthenticationServiceInterface;
use Modules\Auth\Application\Contracts\AuthorizationServiceInterface;
use Modules\Auth\Application\Contracts\LoginServiceInterface;
use Modules\Auth\Application\Contracts\LogoutServiceInterface;
use Modules\Auth\Application\Contracts\RegisterUserServiceInterface;
use Modules\Auth\Application\Contracts\SsoServiceInterface;
use Modules\Auth\Application\Contracts\TokenServiceInterface;
use Modules\Auth\Application\Services\AbacAuthorizationStrategy;
use Modules\Auth\Application\Services\AuthenticationService;
use Modules\Auth\Application\Services\AuthorizationService;
use Modules\Auth\Application\Services\LoginService;
use Modules\Auth\Application\Services\LogoutService;
use Modules\Auth\Application\Services\PassportTokenService;
use Modules\Auth\Application\Services\RbacAuthorizationStrategy;
use Modules\Auth\Application\Services\RegisterUserService;
use Modules\Auth\Application\Services\SsoService;
use Modules\Auth\Application\UseCases\GetAuthenticatedUser;

class AuthModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Token service: swappable via binding (default: Passport)
        $this->app->bind(TokenServiceInterface::class, PassportTokenService::class);

        // Authorization strategies (RBAC + ABAC)
        $this->app->bind(RbacAuthorizationStrategy::class, RbacAuthorizationStrategy::class);
        $this->app->bind(AbacAuthorizationStrategy::class, AbacAuthorizationStrategy::class);

        // Composite authorization service (delegates to strategies)
        $this->app->bind(AuthorizationServiceInterface::class, function ($app) {
            return new AuthorizationService(
                $app->make(RbacAuthorizationStrategy::class),
                $app->make(AbacAuthorizationStrategy::class),
            );
        });

        // Core auth service
        $this->app->bind(AuthenticationServiceInterface::class, function ($app) {
            return new AuthenticationService(
                $app->make(TokenServiceInterface::class),
            );
        });

        // Register / Login / Logout services
        $this->app->bind(RegisterUserServiceInterface::class, RegisterUserService::class);

        $this->app->bind(LoginServiceInterface::class, function ($app) {
            return new LoginService(
                $app->make(AuthenticationServiceInterface::class),
            );
        });

        $this->app->bind(LogoutServiceInterface::class, function ($app) {
            return new LogoutService(
                $app->make(AuthenticationServiceInterface::class),
            );
        });

        // SSO service
        $this->app->bind(SsoServiceInterface::class, function ($app) {
            return new SsoService(
                $app->make(TokenServiceInterface::class),
            );
        });

        // Use case (transient, constructed inline)
        $this->app->bind(GetAuthenticatedUser::class, GetAuthenticatedUser::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Configure Passport token lifetime
        Passport::tokensExpireIn(now()->addDays(
            (int) config('auth.passport.token_expiry_days', 15)
        ));
        Passport::refreshTokensExpireIn(now()->addDays(
            (int) config('auth.passport.refresh_token_expiry_days', 30)
        ));
        Passport::personalAccessTokensExpireIn(now()->addMonths(
            (int) config('auth.passport.personal_token_expiry_months', 6)
        ));
    }
}
