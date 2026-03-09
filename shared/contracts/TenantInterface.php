<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

/**
 * Multi-Tenant Context Contract.
 *
 * Defines how the application resolves, switches, and reads tenant-specific
 * configuration at runtime.  Implementations must be registered as singletons
 * in the service container so that the tenant context is shared across all
 * services within a single request lifecycle.
 */
interface TenantInterface
{
    /**
     * Return the identifier of the currently active tenant.
     *
     * @return string  Opaque tenant ID (UUID or slug).
     */
    public function getTenantId(): string;

    /**
     * Return the Laravel DB connection name configured for this tenant.
     *
     * @return string  E.g. "tenant_acme" or "mysql" for the default.
     */
    public function getConnectionName(): string;

    /**
     * Return the database name for the current tenant.
     *
     * @return string  E.g. "kv_tenant_acme".
     */
    public function getDatabaseName(): string;

    /**
     * Read a tenant-specific configuration value.
     *
     * Values are sourced first from the tenant's runtime config (set via
     * {@see switchTenant()}), then from the application config, then $default.
     *
     * @param  string  $key      Dot-notation config key.
     * @param  mixed   $default  Fallback if the key is not found.
     * @return mixed
     */
    public function getConfiguration(string $key, mixed $default = null): mixed;

    /**
     * Switch the application context to the given tenant.
     *
     * This method MUST:
     *  1. Load tenant settings from persistent storage (DB / cache).
     *  2. Reconfigure the default DB connection to point at the tenant DB.
     *  3. Update cache prefixes, mail settings, queue names, etc.
     *  4. Store the tenant ID so subsequent calls to getTenantId() return it.
     *
     * @param  string  $tenantId  Identifier of the tenant to activate.
     * @return void
     *
     * @throws \RuntimeException  If the tenant cannot be found.
     */
    public function switchTenant(string $tenantId): void;

    /**
     * Return all persisted settings for the current tenant.
     *
     * @return array<string,mixed>
     */
    public function getTenantSettings(): array;
}
