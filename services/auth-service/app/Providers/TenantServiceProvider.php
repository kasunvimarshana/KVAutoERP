<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\TenantRepositoryInterface;
use App\Domain\Tenant\Entities\Tenant;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Throwable;

/**
 * Registers tenant resolution strategies and makes the current tenant
 * available throughout the application lifecycle.
 */
class TenantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Deferred binding – resolved on first use, not on every boot
        $this->app->bind(Tenant::class, function (): ?Tenant {
            return null; // will be overridden by TenantMiddleware at request time
        });
    }

    public function boot(): void
    {
        // Register the tenant middleware alias so it can be used in routes
        $router = $this->app['router'];
        $router->aliasMiddleware('tenant', \App\Http\Middleware\TenantMiddleware::class);

        // In console context, try to resolve tenant from TENANT_ID env var
        if ($this->app->runningInConsole()) {
            $this->resolveConsoleTenant();
        }
    }

    /**
     * When running Artisan commands, allow targeting a specific tenant via
     * the TENANT_ID environment variable (useful for scheduled commands).
     */
    private function resolveConsoleTenant(): void
    {
        $tenantId = env('TENANT_ID');

        if (empty($tenantId)) {
            return;
        }

        try {
            /** @var TenantRepositoryInterface $repository */
            $repository = $this->app->make(TenantRepositoryInterface::class);
            $tenant     = $repository->findById($tenantId);

            if ($tenant !== null) {
                $this->app->instance(Tenant::class, $tenant);

                Log::info("Console running in tenant context: {$tenant->slug} ({$tenant->id})");
            }
        } catch (Throwable $e) {
            Log::warning('Could not resolve console tenant', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
        }
    }
}
