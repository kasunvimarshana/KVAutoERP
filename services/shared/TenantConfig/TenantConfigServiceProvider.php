<?php

namespace App\Shared\TenantConfig;

use Illuminate\Support\ServiceProvider;

class TenantConfigServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantConfigManager::class);
    }

    public function boot(): void
    {
        // Apply tenant config overrides on each request
        $this->app->booted(function () {
            $request = $this->app['request'];

            $tenantId = $request->header('X-Tenant-ID')
                ?? $request->query('tenant_id')
                ?? $this->extractTenantFromSubdomain($request->getHost());

            if ($tenantId) {
                /** @var TenantConfigManager $manager */
                $manager = $this->app->make(TenantConfigManager::class);
                $manager->overrideConfig($tenantId);
            }
        });
    }

    private function extractTenantFromSubdomain(string $host): ?string
    {
        $appDomain = config('app.domain', 'localhost');
        if (str_ends_with($host, '.' . $appDomain)) {
            return explode('.', $host)[0];
        }
        return null;
    }
}
