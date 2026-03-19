<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Contracts\Services\PermissionServiceInterface;
use Illuminate\Support\Facades\Cache;

class PermissionService implements PermissionServiceInterface
{
    private const CACHE_TTL_SECONDS = 300;

    public function __construct(
        private readonly RoleRepositoryInterface $roleRepository,
        private readonly PermissionRepositoryInterface $permissionRepository,
    ) {}

    public function hasPermission(
        string $userId,
        string $permission,
        string $tenantId,
        array $context = [],
    ): bool {
        $permissions = $this->getUserPermissions($userId, $tenantId);

        // RBAC check
        if (in_array($permission, $permissions, true)) {
            return true;
        }

        // Wildcard check (e.g. 'inventory.*' grants 'inventory.view')
        foreach ($permissions as $userPermission) {
            if (str_ends_with($userPermission, '.*')) {
                $prefix = rtrim($userPermission, '.*');
                if (str_starts_with($permission, $prefix . '.')) {
                    return true;
                }
            }
        }

        // ABAC evaluation if context is provided
        if (! empty($context)) {
            return $this->evaluatePolicy($userId, $tenantId, $permission, $context['resource_type'] ?? '', $context);
        }

        return false;
    }

    public function hasRole(string $userId, string|array $roles, string $tenantId): bool
    {
        $userRoles = $this->getUserRoles($userId, $tenantId);
        $checkRoles = is_array($roles) ? $roles : [$roles];

        return ! empty(array_intersect($checkRoles, $userRoles));
    }

    public function hasAllRoles(string $userId, array $roles, string $tenantId): bool
    {
        $userRoles = $this->getUserRoles($userId, $tenantId);
        return empty(array_diff($roles, $userRoles));
    }

    public function getUserPermissions(string $userId, string $tenantId): array
    {
        return Cache::remember(
            $this->permissionCacheKey($userId, $tenantId),
            self::CACHE_TTL_SECONDS,
            function () use ($userId) {
                $roles = $this->roleRepository->getUserRoles($userId);
                $rolePermissions = $roles->flatMap(fn ($role) => $role->permissions->pluck('name'));
                $directPermissions = $this->permissionRepository->getUserPermissions($userId)
                    ->pluck('name');

                return $rolePermissions->merge($directPermissions)->unique()->values()->toArray();
            },
        );
    }

    public function getUserRoles(string $userId, string $tenantId): array
    {
        return Cache::remember(
            $this->roleCacheKey($userId, $tenantId),
            self::CACHE_TTL_SECONDS,
            function () use ($userId) {
                return $this->roleRepository->getUserRoles($userId)->pluck('name')->toArray();
            },
        );
    }

    public function evaluatePolicy(
        string $userId,
        string $tenantId,
        string $action,
        string $resourceType,
        array $resourceAttributes = [],
    ): bool {
        // ABAC policy evaluation — extensible rule engine
        // Roles provide the base (RBAC); attributes add context (ABAC).
        // Example: a user with 'inventory.manager' role can modify inventory
        // only within their assigned branch.
        $userBranch = $resourceAttributes['user_branch_id'] ?? null;
        $resourceBranch = $resourceAttributes['branch_id'] ?? null;

        if ($userBranch && $resourceBranch && $userBranch !== $resourceBranch) {
            return false;
        }

        return $this->hasPermission($userId, $action, $tenantId);
    }

    public function assignRole(string $userId, string $roleId, string $tenantId): void
    {
        $this->roleRepository->assignToUser($roleId, $userId);
        $this->invalidateCache($userId, $tenantId);
    }

    public function revokeRole(string $userId, string $roleId, string $tenantId): void
    {
        $this->roleRepository->revokeFromUser($roleId, $userId);
        $this->invalidateCache($userId, $tenantId);
    }

    public function createRole(string $name, string $tenantId, array $permissions = [], string $description = ''): array
    {
        $role = $this->roleRepository->create([
            'tenant_id'   => $tenantId,
            'name'        => $name,
            'description' => $description,
            'guard_name'  => 'api',
        ]);

        if (! empty($permissions)) {
            $permissionIds = $this->permissionRepository->findByTenant($tenantId)
                ->whereIn('name', $permissions)
                ->pluck('id')
                ->toArray();

            if (! empty($permissionIds)) {
                $this->permissionRepository->syncRolePermissions($role->id, $permissionIds);
            }
        }

        return $role->toArray();
    }

    public function createPermission(string $name, string $guard = 'api', string $description = ''): array
    {
        $permission = $this->permissionRepository->create([
            'name'        => $name,
            'description' => $description,
            'guard_name'  => $guard,
        ]);

        return $permission->toArray();
    }

    public function invalidateCache(string $userId, string $tenantId): void
    {
        Cache::forget($this->permissionCacheKey($userId, $tenantId));
        Cache::forget($this->roleCacheKey($userId, $tenantId));
    }

    private function permissionCacheKey(string $userId, string $tenantId): string
    {
        return "tenant:{$tenantId}:user:{$userId}:permissions";
    }

    private function roleCacheKey(string $userId, string $tenantId): string
    {
        return "tenant:{$tenantId}:user:{$userId}:roles";
    }
}
