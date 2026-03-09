<?php

declare(strict_types=1);

namespace Saas\Contracts\Tenant;

/**
 * Defines the contract for a tenant entity within the multi-tenant SaaS platform.
 *
 * Every tenant implementation must satisfy this interface so that services can
 * resolve and work with tenant data without coupling to a concrete model.
 */
interface TenantInterface
{
    /**
     * Returns the tenant's unique identifier (UUID or ULID).
     */
    public function getId(): string;

    /**
     * Returns the human-readable display name of the tenant.
     */
    public function getName(): string;

    /**
     * Returns the primary domain (or sub-domain) associated with the tenant.
     *
     * Example: `acme.example.com`
     */
    public function getDomain(): string;

    /**
     * Returns the URL-safe slug that uniquely identifies the tenant.
     *
     * Example: `acme-corp`
     */
    public function getSlug(): string;

    /**
     * Returns the tenant's current lifecycle status.
     *
     * Known values: `active`, `suspended`, `trial`, `cancelled`.
     */
    public function getStatus(): string;

    /**
     * Returns the tenant's configuration settings as a key-value map.
     *
     * @return array<string, mixed>
     */
    public function getSettings(): array;

    /**
     * Returns the name of the database connection that should be used for this tenant.
     *
     * The returned value must correspond to a key defined in `config/database.php`
     * (or the equivalent connection configuration within the service).
     */
    public function getDatabaseConnection(): string;

    /**
     * Retrieves a single tenant-level configuration value by dot-notation key.
     *
     * @param string $key     Dot-notation path into {@see getSettings()}, e.g. `"features.inventory"`.
     * @param mixed  $default Value returned when the key is absent.
     */
    public function getConfig(string $key, mixed $default = null): mixed;
}
