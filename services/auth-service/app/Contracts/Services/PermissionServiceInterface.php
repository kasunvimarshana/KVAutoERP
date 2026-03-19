<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use Illuminate\Database\Eloquent\Collection;

interface PermissionServiceInterface
{
    /**
     * Check whether the user has the specified permission within the tenant context.
     * Evaluates both role-based (RBAC) and attribute-based (ABAC) rules.
     */
    public function hasPermission(
        string $userId,
        string $permission,
        string $tenantId,
        array $context = [],
    ): bool;

    /**
     * Check whether the user has any of the specified roles.
     */
    public function hasRole(string $userId, string|array $roles, string $tenantId): bool;

    /**
     * Check whether the user has all of the specified roles.
     */
    public function hasAllRoles(string $userId, array $roles, string $tenantId): bool;

    /**
     * Return all permissions for the user (role-based + direct) within the tenant.
     */
    public function getUserPermissions(string $userId, string $tenantId): array;

    /**
     * Return all roles assigned to the user within the tenant.
     */
    public function getUserRoles(string $userId, string $tenantId): array;

    /**
     * Evaluate ABAC (attribute-based) policies for the given action on the resource.
     */
    public function evaluatePolicy(
        string $userId,
        string $tenantId,
        string $action,
        string $resourceType,
        array $resourceAttributes = [],
    ): bool;

    /**
     * Assign a role to a user within the tenant.
     */
    public function assignRole(string $userId, string $roleId, string $tenantId): void;

    /**
     * Revoke a role from a user within the tenant.
     */
    public function revokeRole(string $userId, string $roleId, string $tenantId): void;

    /**
     * Create a new role at runtime without redeployment.
     */
    public function createRole(string $name, string $tenantId, array $permissions = [], string $description = ''): array;

    /**
     * Create a new permission at runtime without redeployment.
     */
    public function createPermission(string $name, string $guard = 'api', string $description = ''): array;

    /**
     * Invalidate the permission cache for a user.
     */
    public function invalidateCache(string $userId, string $tenantId): void;
}
