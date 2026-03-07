<?php

namespace App\Application\Services;

use App\Domain\Auth\Entities\Permission;
use App\Domain\Auth\Entities\Role;
use App\Domain\Auth\Entities\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RBACService
{
    /**
     * Pre-defined role → permission mappings.
     * Roles are ordered from most to least privileged.
     */
    const ROLE_PERMISSIONS = [
        'super-admin' => ['*'],          // Wildcard – all permissions

        'tenant-admin' => [
            'orders.view', 'orders.create', 'orders.cancel',
            'inventory.view', 'inventory.create', 'inventory.update', 'inventory.delete', 'inventory.reserve',
            'users.view', 'users.create', 'users.update', 'users.delete',
            'roles.manage', 'permissions.manage',
            'tenant.manage',
            'reports.view', 'reports.export',
        ],

        'manager' => [
            'orders.view', 'orders.create', 'orders.cancel',
            'inventory.view', 'inventory.create', 'inventory.update', 'inventory.reserve',
            'users.view',
            'reports.view', 'reports.export',
        ],

        'staff' => [
            'orders.view', 'orders.create',
            'inventory.view', 'inventory.reserve',
            'reports.view',
        ],

        'viewer' => [
            'orders.view',
            'inventory.view',
            'reports.view',
        ],
    ];

    // -------------------------------------------------------------------------
    // Role assignment
    // -------------------------------------------------------------------------

    public function assignRole(User $user, string $roleName): void
    {
        $role = Role::where('name', $roleName)->firstOrFail();
        $user->assignRole($role);
        $this->clearUserPermissionCache($user);
        Log::info("Role [{$roleName}] assigned to user [{$user->id}]");
    }

    public function revokeRole(User $user, string $roleName): void
    {
        $user->removeRole($roleName);
        $this->clearUserPermissionCache($user);
        Log::info("Role [{$roleName}] revoked from user [{$user->id}]");
    }

    public function getUserRoles(User $user): array
    {
        return $user->getRoleNames()->toArray();
    }

    // -------------------------------------------------------------------------
    // Permission assignment
    // -------------------------------------------------------------------------

    public function assignPermission(User $user, string $permissionName): void
    {
        $permission = Permission::where('name', $permissionName)->firstOrFail();
        $user->givePermissionTo($permission);
        $this->clearUserPermissionCache($user);
    }

    public function revokePermission(User $user, string $permissionName): void
    {
        $user->revokePermissionTo($permissionName);
        $this->clearUserPermissionCache($user);
    }

    public function getUserPermissions(User $user): array
    {
        return $user->getAllPermissions()->pluck('name')->toArray();
    }

    // -------------------------------------------------------------------------
    // Checks
    // -------------------------------------------------------------------------

    public function hasRole(User $user, string $roleName): bool
    {
        return $user->hasRole($roleName);
    }

    public function hasPermission(User $user, string $permission): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }
        return $user->hasPermissionTo($permission);
    }

    // -------------------------------------------------------------------------
    // Role management
    // -------------------------------------------------------------------------

    public function createRole(string $name, string $guardName = 'api', ?string $tenantId = null, ?string $description = null): Role
    {
        $role = Role::create([
            'name'        => $name,
            'guard_name'  => $guardName,
            'tenant_id'   => $tenantId,
            'description' => $description,
        ]);

        // Sync default permissions for known roles
        if (isset(self::ROLE_PERMISSIONS[$name]) && self::ROLE_PERMISSIONS[$name] !== ['*']) {
            $permissions = Permission::whereIn('name', self::ROLE_PERMISSIONS[$name])->get();
            $role->syncPermissions($permissions);
        }

        return $role;
    }

    public function deleteRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->firstOrFail();

        // Prevent deletion of roles that still have active users to avoid disrupting sessions
        $activeUserCount = $role->users()->where('is_active', true)->count();
        if ($activeUserCount > 0) {
            throw new \RuntimeException(
                "Cannot delete role [{$roleName}]: it is currently assigned to {$activeUserCount} active user(s). Revoke the role from all users first."
            );
        }

        // Detach remaining (inactive) users and delete
        $role->users()->detach();
        $role->delete();

        Log::info("Role [{$roleName}] deleted");
    }

    // -------------------------------------------------------------------------
    // Seed default roles and permissions (called from seeder/install command)
    // -------------------------------------------------------------------------

    public function seedDefaults(): void
    {
        // Create all permissions
        foreach (Permission::catalogue() as $name => $meta) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'api'],
                ['group' => $meta['group'], 'description' => $meta['description']]
            );
        }

        // Create global roles
        foreach (array_keys(self::ROLE_PERMISSIONS) as $roleName) {
            $this->createRole($roleName, 'api');
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function clearUserPermissionCache(User $user): void
    {
        Cache::forget("user_{$user->id}_permissions");
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
