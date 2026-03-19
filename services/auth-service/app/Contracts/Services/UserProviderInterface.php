<?php

declare(strict_types=1);

namespace App\Contracts\Services;

/**
 * Contract for fetching enriched user claims from the User Service.
 *
 * The Auth Service uses this abstraction to delegate user-domain lookups
 * (roles, permissions, tenant hierarchy) to the User microservice via its
 * internal API. This enables the Auth Service to issue fully populated
 * JWT tokens without any direct database access to the User Service.
 *
 * Implementations may call the User Service over HTTP, gRPC, or any other
 * transport — the Auth Service only depends on this interface.
 */
interface UserProviderInterface
{
    /**
     * Retrieve enriched JWT claims for a user from the User Service.
     *
     * Returns an array containing role/permission slugs and tenant hierarchy
     * data (organization_id, branch_id, location_id, department_id) that will
     * be embedded into the JWT access token.
     *
     * Returns null when no profile is found or the User Service is unavailable.
     *
     * @param  string  $authUserId  UUID of the user in the Auth Service's users table.
     * @param  string  $tenantId    Tenant UUID scoping the lookup.
     * @return array<string, mixed>|null  Enriched claims or null on failure.
     */
    public function getClaimsForUser(string $authUserId, string $tenantId): ?array;
}
