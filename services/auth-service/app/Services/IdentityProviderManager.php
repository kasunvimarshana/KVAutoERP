<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\IdentityProviderContract;
use App\Exceptions\AuthenticationException;
use Illuminate\Contracts\Container\Container;

/**
 * Factory + Registry for IAM provider adapters.
 *
 * New providers are registered via register() without touching core code,
 * following the Open/Closed Principle.
 *
 * Tenant-specific runtime configuration can be injected via
 * registerTenantConfig() so each tenant can use its own credentials
 * without requiring environment variable changes or redeployment.
 */
class IdentityProviderManager
{
    /** @var array<string, class-string<IdentityProviderContract>> */
    private array $providers = [];

    /** @var array<string, IdentityProviderContract> */
    private array $resolved = [];

    /** @var array<string, array<string, mixed>> Runtime per-tenant config overrides: "provider:tenantId" => config */
    private array $tenantConfigs = [];

    /**
     * @param Container $container Laravel IoC container used to resolve providers
     *                             with typed service-contract constructor parameters.
     */
    public function __construct(private readonly Container $container) {}

    public function register(string $name, string $providerClass): void
    {
        $this->providers[$name] = $providerClass;
    }

    /**
     * Store per-tenant runtime configuration for an IAM provider.
     *
     * This is called dynamically at login time when the Auth service fetches
     * tenant IAM config from the User service.  It overrides static env-var
     * config for that specific tenant without touching global config.
     *
     * @param array<string, mixed> $config
     */
    public function registerTenantConfig(string $providerName, string $tenantId, array $config): void
    {
        $this->tenantConfigs["{$providerName}:{$tenantId}"] = $config;

        // Invalidate any previously resolved instance for this tenant
        unset($this->resolved["{$providerName}:{$tenantId}"]);
    }

    /**
     * Resolve an IAM provider for the given tenant, injecting tenant config.
     *
     * Uses the injected IoC container so that providers declaring service-contract
     * constructor dependencies (e.g. LocalIdentityProvider needs
     * UserServiceClientContract, TokenServiceContract) are resolved correctly,
     * while config-only providers (Okta, Keycloak, etc.) continue to work unchanged.
     *
     * @throws AuthenticationException when the provider is not registered
     */
    public function resolve(string $providerName, string $tenantId): IdentityProviderContract
    {
        $cacheKey = "{$providerName}:{$tenantId}";

        if (isset($this->resolved[$cacheKey])) {
            return $this->resolved[$cacheKey];
        }

        if (! isset($this->providers[$providerName])) {
            throw new AuthenticationException(
                "IAM provider '{$providerName}' is not registered. Available: "
                . implode(', ', array_keys($this->providers))
            );
        }

        $config   = $this->resolveConfig($providerName, $tenantId);
        $class    = $this->providers[$providerName];

        // Resolve through the IoC container so that providers with typed service
        // contract constructor parameters receive their dependencies automatically.
        $provider = $this->container->make($class, ['config' => $config]);

        $this->resolved[$cacheKey] = $provider;

        return $provider;
    }

    public function supports(string $providerName): bool
    {
        return isset($this->providers[$providerName]);
    }

    /** @return string[] */
    public function getRegisteredProviders(): array
    {
        return array_keys($this->providers);
    }

    /**
     * Merge static config-file settings with any per-tenant runtime overrides.
     *
     * @return array<string, mixed>
     */
    private function resolveConfig(string $providerName, string $tenantId): array
    {
        $staticConfig   = (array) config("iam_providers.{$providerName}", []);
        $tenantOverride = $this->tenantConfigs["{$providerName}:{$tenantId}"] ?? [];

        return array_merge(
            $staticConfig,
            $tenantOverride,
            ['tenant_id' => $tenantId],
        );
    }
}
