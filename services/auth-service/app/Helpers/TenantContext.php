<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Domain\Tenant\Entities\Tenant;

/**
 * Tenant Context.
 *
 * Thread-local storage for the current request's tenant.
 * Provides static access throughout the request lifecycle.
 */
final class TenantContext
{
    private static ?Tenant $tenant = null;

    /**
     * Set the current tenant context.
     *
     * @param  Tenant $tenant
     * @return void
     */
    public static function set(Tenant $tenant): void
    {
        self::$tenant = $tenant;
    }

    /**
     * Get the current tenant context.
     *
     * @return Tenant|null
     */
    public static function get(): ?Tenant
    {
        return self::$tenant;
    }

    /**
     * Get the current tenant ID.
     *
     * @return string|null
     */
    public static function getId(): ?string
    {
        return self::$tenant?->id;
    }

    /**
     * Check if a tenant context is active.
     *
     * @return bool
     */
    public static function isActive(): bool
    {
        return self::$tenant !== null;
    }

    /**
     * Clear the current tenant context.
     *
     * @return void
     */
    public static function clear(): void
    {
        self::$tenant = null;
    }

    /**
     * Get the current tenant or throw if not set.
     *
     * @return Tenant
     * @throws \RuntimeException
     */
    public static function getOrFail(): Tenant
    {
        return self::$tenant
            ?? throw new \RuntimeException('No tenant context is active.');
    }
}
