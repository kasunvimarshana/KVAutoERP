<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\Contracts\Auth;

/**
 * Contract for accessing the authenticated request context.
 *
 * Each microservice binds a concrete implementation that extracts
 * identity and tenant claims from the verified JWT, making them
 * available throughout the request lifecycle without additional
 * calls to the Auth service.
 */
interface AuthContextInterface
{
    /**
     * Return the full authenticated user payload.
     *
     * @return array<string, mixed>|null  User claims array or null when unauthenticated.
     */
    public function getUser(): ?array;

    /**
     * Return the authenticated user's unique identifier.
     *
     * @return string|null  UUID string or null when unauthenticated.
     */
    public function getUserId(): ?string;

    /**
     * Return the tenant identifier from the current auth context.
     *
     * @return string|null  UUID string of the owning tenant or null.
     */
    public function getTenantId(): ?string;

    /**
     * Return the organisation identifier from the current auth context.
     *
     * @return string|null  UUID string of the organisation or null.
     */
    public function getOrganizationId(): ?string;

    /**
     * Return the branch identifier from the current auth context.
     *
     * @return string|null  UUID string of the branch or null.
     */
    public function getBranchId(): ?string;

    /**
     * Return the list of role slugs assigned to the authenticated user.
     *
     * @return array<int, string>  e.g. ['admin', 'warehouse-manager'].
     */
    public function getRoles(): array;

    /**
     * Return the list of permission slugs granted to the authenticated user.
     *
     * @return array<int, string>  e.g. ['inventory.view', 'orders.create'].
     */
    public function getPermissions(): array;

    /**
     * Return the device session identifier from the token claims.
     *
     * @return string|null  Device ID string or null when not present.
     */
    public function getDeviceId(): ?string;

    /**
     * Determine whether the current context is authenticated.
     *
     * @return bool  True when a valid, non-revoked token is present.
     */
    public function isAuthenticated(): bool;

    /**
     * Determine whether the authenticated user has a specific role.
     *
     * @param  string  $role  Role slug to check.
     * @return bool
     */
    public function hasRole(string $role): bool;

    /**
     * Determine whether the authenticated user has a specific permission.
     *
     * @param  string  $permission  Permission slug to check.
     * @return bool
     */
    public function hasPermission(string $permission): bool;

    /**
     * Return the raw token version claim used for revocation checks.
     *
     * @return int|null  Token version integer or null when not present.
     */
    public function getTokenVersion(): ?int;
}
