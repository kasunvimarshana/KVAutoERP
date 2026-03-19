<?php

declare(strict_types=1);

namespace App\IdentityProviders;

use App\Contracts\IdentityProvider\IdentityProviderInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Log;

/**
 * Tenant-aware identity provider factory.
 *
 * Implements the Factory + Strategy design patterns to dynamically resolve
 * the correct IAM provider for a given tenant at runtime. New providers
 * can be registered via `registerProvider()` without any code changes to
 * the factory itself (Open/Closed Principle).
 *
 * Resolution order:
 *   1. Registered providers are iterated in registration order.
 *   2. The first provider that returns `true` for `supports($tenantId)` is used.
 *   3. The `LocalIdentityProvider` is always registered as the final fallback.
 */
final class IdentityProviderFactory
{
    /** @var array<string, class-string<IdentityProviderInterface>> */
    private array $providerMap = [];

    /** @var array<string, IdentityProviderInterface> Resolved provider instances keyed by tenant. */
    private array $resolvedCache = [];

    public function __construct(
        private readonly Container $container,
    ) {}

    /**
     * Register an identity provider class under a given name.
     *
     * @param  string                                           $name         Unique provider name (e.g. "local", "oauth2").
     * @param  class-string<IdentityProviderInterface>          $providerClass Fully qualified class name.
     * @return self
     */
    public function registerProvider(string $name, string $providerClass): self
    {
        $this->providerMap[$name] = $providerClass;

        return $this;
    }

    /**
     * Resolve the identity provider for the given tenant.
     *
     * Iterates all registered providers and returns the first one that
     * declares support for the tenant. Falls back to `LocalIdentityProvider`
     * if no other provider matches. Resolved instances are cached per tenant
     * to avoid repeated container resolutions within the same request.
     *
     * @param  string  $tenantId  Tenant UUID.
     * @return IdentityProviderInterface
     */
    public function resolveForTenant(string $tenantId): IdentityProviderInterface
    {
        if (isset($this->resolvedCache[$tenantId])) {
            return $this->resolvedCache[$tenantId];
        }

        $provider = $this->doResolve($tenantId);

        $this->resolvedCache[$tenantId] = $provider;

        return $provider;
    }

    /**
     * Return all registered provider names.
     *
     * @return array<string>
     */
    public function getRegisteredProviders(): array
    {
        return array_keys($this->providerMap);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Perform the actual provider resolution for a tenant without caching.
     *
     * @param  string  $tenantId
     * @return IdentityProviderInterface
     */
    private function doResolve(string $tenantId): IdentityProviderInterface
    {
        // Check the tenant's configured provider name from config/cache first.
        $configuredName = $this->getTenantProviderName($tenantId);

        if ($configuredName !== null && isset($this->providerMap[$configuredName])) {
            $provider = $this->container->make($this->providerMap[$configuredName]);

            if ($provider->supports($tenantId)) {
                return $provider;
            }
        }

        // Iterate all registered providers to find one that supports this tenant.
        foreach ($this->providerMap as $name => $providerClass) {
            if ($name === 'local') {
                continue; // Local is the final fallback — skip in the first pass.
            }

            $provider = $this->container->make($providerClass);

            if ($provider->supports($tenantId)) {
                Log::debug('IdentityProviderFactory: resolved provider', [
                    'provider'  => $name,
                    'tenant_id' => $tenantId,
                ]);

                return $provider;
            }
        }

        // Default fallback — always available.
        return $this->container->make(
            $this->providerMap['local'] ?? LocalIdentityProvider::class,
        );
    }

    /**
     * Look up the configured IAM provider name for a tenant.
     *
     * Reads from the `auth_service.tenant_providers` config key which maps
     * tenant UUIDs to provider names. This can be extended to load from a
     * cache or database for fully runtime-dynamic resolution.
     *
     * @param  string  $tenantId
     * @return string|null
     */
    private function getTenantProviderName(string $tenantId): ?string
    {
        /** @var array<string, string> $tenantProviders */
        $tenantProviders = (array) config('auth_service.tenant_providers', []);

        return $tenantProviders[$tenantId] ?? null;
    }
}
