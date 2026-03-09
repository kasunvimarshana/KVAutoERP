<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Auth\Handlers\LoginCommandHandler;
use App\Application\Auth\Handlers\LogoutCommandHandler;
use App\Application\Auth\Handlers\RefreshTokenCommandHandler;
use App\Application\Auth\Handlers\RegisterCommandHandler;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Domain\Auth\Services\AuthDomainService;
use App\Infrastructure\Repositories\EloquentUserRepository;
use App\Services\AuthService;
use Illuminate\Support\ServiceProvider;

/**
 * Application Service Provider.
 *
 * Binds domain contracts to their concrete implementations and
 * registers any cross-cutting concerns (health checks, etc.).
 */
final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     */
    public function register(): void
    {
        // Domain Repository binding.
        $this->app->bind(
            abstract: UserRepositoryInterface::class,
            concrete: EloquentUserRepository::class,
        );

        // Domain Service — resolved with configured TTLs.
        $this->app->singleton(AuthDomainService::class, function (): AuthDomainService {
            return new AuthDomainService(
                accessTokenTtl: (int) config('passport.token_expiry', 60),
                refreshTokenTtl: (int) config('passport.refresh_token_expiry', 30),
            );
        });

        // Application Service — wire all command handlers.
        $this->app->singleton(AuthService::class, function ($app): AuthService {
            return new AuthService(
                repository: $app->make(UserRepositoryInterface::class),
                loginHandler: $app->make(LoginCommandHandler::class),
                registerHandler: $app->make(RegisterCommandHandler::class),
                logoutHandler: $app->make(LogoutCommandHandler::class),
                refreshHandler: $app->make(RefreshTokenCommandHandler::class),
                messageBroker: $app->bound(\App\Shared\Contracts\MessageBrokerInterface::class)
                    ? $app->make(\App\Shared\Contracts\MessageBrokerInterface::class)
                    : null,
                logger: $app->make(\Psr\Log\LoggerInterface::class),
            );
        });
    }

    /**
     * Bootstrap application services.
     */
    public function boot(): void
    {
        // Enforce strict type checking in development.
        if ($this->app->environment('local', 'testing')) {
            \Illuminate\Database\Eloquent\Model::shouldBeStrict();
        }
    }
}
