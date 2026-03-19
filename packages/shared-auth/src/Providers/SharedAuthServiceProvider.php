<?php

declare(strict_types=1);

namespace KvEnterprise\SharedAuth\Providers;

use Illuminate\Support\ServiceProvider;
use KvEnterprise\SharedAuth\Contracts\JwtVerifierInterface;
use KvEnterprise\SharedAuth\Contracts\TenantContextInterface;
use KvEnterprise\SharedAuth\Middleware\RequirePermission;
use KvEnterprise\SharedAuth\Middleware\RequireRole;
use KvEnterprise\SharedAuth\Middleware\VerifyMicroserviceToken;
use KvEnterprise\SharedAuth\Services\JwtVerifier;
use KvEnterprise\SharedAuth\Services\TenantContext;

class SharedAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/shared_auth.php', 'shared_auth');

        // JwtVerifier is stateless — shared singleton is safe
        $this->app->singleton(JwtVerifierInterface::class, JwtVerifier::class);

        // TenantContext is request-scoped: a new instance per request
        $this->app->scoped(TenantContextInterface::class, TenantContext::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/shared_auth.php' => config_path('shared_auth.php'),
            ], 'shared-auth-config');
        }

        $this->registerMiddlewareAliases();
    }

    private function registerMiddlewareAliases(): void
    {
        $router = $this->app['router'];

        $router->aliasMiddleware('auth.jwt', VerifyMicroserviceToken::class);
        $router->aliasMiddleware('require.permission', RequirePermission::class);
        $router->aliasMiddleware('require.role', RequireRole::class);
    }
}
