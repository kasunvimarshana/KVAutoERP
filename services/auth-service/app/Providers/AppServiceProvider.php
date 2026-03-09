<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Auth\Handlers\LoginHandler;
use App\Application\Auth\Handlers\LogoutHandler;
use App\Application\Auth\Handlers\RegisterUserHandler;
use App\Contracts\Messaging\MessageBrokerInterface;
use App\Contracts\Repositories\TenantRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Infrastructure\Cache\TenantAwareCache;
use App\Infrastructure\Messaging\RabbitMQBroker;
use App\Infrastructure\Repositories\TenantRepository;
use App\Infrastructure\Repositories\UserRepository;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        UserRepositoryInterface::class   => UserRepository::class,
        TenantRepositoryInterface::class => TenantRepository::class,
    ];

    public function register(): void
    {
        // Tenant-aware cache (singleton to maintain tenant state within request)
        $this->app->singleton(TenantAwareCache::class, function ($app): TenantAwareCache {
            return new TenantAwareCache($app->make(CacheRepository::class));
        });

        // RabbitMQ broker (only instantiated if configured)
        $this->app->singleton(MessageBrokerInterface::class, function (): RabbitMQBroker {
            return new RabbitMQBroker(
                host: config('services.rabbitmq.host', 'rabbitmq'),
                port: (int) config('services.rabbitmq.port', 5672),
                user: config('services.rabbitmq.user', 'guest'),
                password: config('services.rabbitmq.password', 'guest'),
                vhost: config('services.rabbitmq.vhost', '/'),
            );
        });

        // Application handlers
        $this->app->bind(RegisterUserHandler::class, function ($app): RegisterUserHandler {
            return new RegisterUserHandler(
                $app->make(UserRepositoryInterface::class),
                $app->make(TenantRepositoryInterface::class),
            );
        });

        $this->app->bind(LoginHandler::class, function ($app): LoginHandler {
            return new LoginHandler(
                $app->make(UserRepositoryInterface::class),
                $app->make(TenantRepositoryInterface::class),
            );
        });

        $this->app->bind(LogoutHandler::class, function ($app): LogoutHandler {
            return new LogoutHandler(
                $app->make(UserRepositoryInterface::class),
            );
        });
    }

    public function boot(): void
    {
        // Enforce HTTPS in production
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
