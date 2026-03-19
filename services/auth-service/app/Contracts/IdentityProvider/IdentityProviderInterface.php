<?php

declare(strict_types=1);

namespace App\Contracts\IdentityProvider;

/**
 * Contract for pluggable identity providers (IAM backends).
 *
 * Allows the Auth Service to support multiple authentication backends —
 * local database, Okta, Keycloak, Active Directory, OAuth2/OIDC, SAML —
 * without coupling to any specific provider. Implementations are resolved
 * per-tenant via the IdentityProviderFactory using the Strategy pattern.
 *
 * New providers can be added by:
 *   1. Implementing this interface.
 *   2. Registering the provider type in the `iam_providers` config.
 *   3. No existing code needs to change (Open/Closed Principle).
 */
interface IdentityProviderInterface
{
    /**
     * Validate user credentials against this identity provider.
     *
     * @param  string  $email     The user's email address.
     * @param  string  $password  The plain-text password or token.
     * @param  string  $tenantId  Tenant UUID for scoped lookups.
     * @return array<string, mixed>|null  Identity data (user_id, email, …) on success, null on failure.
     */
    public function authenticate(string $email, string $password, string $tenantId): ?array;

    /**
     * Return a unique, machine-readable identifier for this provider type.
     *
     * Used by the factory and config system to resolve providers by name.
     * Must be URL-safe and lower-case (e.g. "local", "okta", "keycloak").
     *
     * @return string
     */
    public function getProviderName(): string;

    /**
     * Determine whether this provider supports the given tenant.
     *
     * @param  string  $tenantId  Tenant UUID.
     * @return bool
     */
    public function supports(string $tenantId): bool;
}
