<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\RefreshTokenRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\AuditLogServiceInterface;
use App\Contracts\Services\AuthServiceInterface;
use App\Contracts\Services\RevocationServiceInterface;
use App\Repositories\RefreshTokenRepository;
use App\Repositories\UserRepository;
use App\Services\AuditLogService;
use App\Services\AuthContext;
use App\Services\AuthService;
use App\Services\JwtTokenService;
use App\Services\RevocationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use KvEnterprise\SharedKernel\Contracts\Auth\AuthContextInterface;
use KvEnterprise\SharedKernel\Contracts\Auth\TokenServiceInterface;
use Predis\Client as PredisClient;

/**
 * Application service provider — binds all interfaces to concrete implementations.
 */
final class AppServiceProvider extends ServiceProvider
{
    /**
     * All interface → implementation bindings for this service.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        UserRepositoryInterface::class         => UserRepository::class,
        RefreshTokenRepositoryInterface::class => RefreshTokenRepository::class,
        AuditLogServiceInterface::class        => AuditLogService::class,
    ];

    /**
     * Register application services.
     *
     * @return void
     */
    public function register(): void
    {
        // AuthContext as a singleton so claims survive the request.
        $this->app->singleton(AuthContext::class);
        $this->app->singleton(AuthContextInterface::class, AuthContext::class);

        // Redis client (Predis).
        $this->app->singleton(PredisClient::class, function (): PredisClient {
            return new PredisClient([
                'scheme'   => 'tcp',
                'host'     => (string) config('database.redis.default.host', '127.0.0.1'),
                'port'     => (int)    config('database.redis.default.port', 6379),
                'password' => config('database.redis.default.password') ?: null,
                'database' => (int)    config('database.redis.default.database', 0),
            ]);
        });

        // Revocation service (singleton — stateless, Redis-backed).
        $this->app->singleton(RevocationServiceInterface::class, function (): RevocationService {
            return new RevocationService(
                redis: $this->app->make(PredisClient::class),
                prefix: (string) config('auth_service.revocation_prefix', 'revoke'),
            );
        });

        // Token service (singleton — no mutable state after construction).
        $this->app->singleton(TokenServiceInterface::class, function (): JwtTokenService {
            return new JwtTokenService(
                revocationService: $this->app->make(RevocationServiceInterface::class),
            );
        });

        // Auth service (transient — new instance per resolution).
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
    }

    /**
     * Bootstrap application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Override Laravel's default bcrypt hasher with Argon2id
        // for all password_hash() calls going through Hash::make().
        Hash::setRounds(4); // used only if bcrypt fallback is ever invoked

        // Force Argon2id as the application-wide hashing driver.
        config(['hashing.driver' => 'argon2id']);
    }
}
