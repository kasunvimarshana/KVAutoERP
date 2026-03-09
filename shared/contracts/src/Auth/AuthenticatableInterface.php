<?php

declare(strict_types=1);

namespace Saas\Contracts\Auth;

/**
 * Defines the contract for an authenticated principal within the platform.
 *
 * Any object that represents a logged-in user (JWT claims, session data,
 * API token principal, etc.) must implement this interface so that downstream
 * services can perform authorisation checks without coupling to a concrete model.
 */
interface AuthenticatableInterface
{
    /**
     * Returns the unique identifier of the authenticated user.
     */
    public function getUserId(): string;

    /**
     * Returns the identifier of the tenant the user belongs to.
     */
    public function getTenantId(): string;

    /**
     * Returns the identifier of the organisation within the tenant, if applicable.
     *
     * Some tenants are structured into sub-organisations; returns `null` when
     * the user is not scoped to a specific organisation.
     */
    public function getOrganizationId(): ?string;

    /**
     * Returns the full list of role slugs assigned to the user.
     *
     * @return string[]
     */
    public function getRoles(): array;

    /**
     * Returns the full list of permission slugs assigned to the user,
     * including those inherited from their roles.
     *
     * @return string[]
     */
    public function getPermissions(): array;

    /**
     * Checks whether the user holds the given role.
     *
     * @param string $role Role slug, e.g. `"admin"` or `"inventory-manager"`.
     */
    public function hasRole(string $role): bool;

    /**
     * Checks whether the user holds the given permission.
     *
     * @param string $permission Permission slug, e.g. `"inventory.write"`.
     */
    public function hasPermission(string $permission): bool;

    /**
     * General-purpose ability check (Gate-style).
     *
     * @param string     $ability The ability to check, e.g. `"update"`.
     * @param mixed|null $subject The optional subject against which the ability is evaluated
     *                            (e.g. a model instance or class name).
     */
    public function can(string $ability, mixed $subject = null): bool;
}
