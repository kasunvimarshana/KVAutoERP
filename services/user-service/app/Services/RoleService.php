<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\RoleServiceContract;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleService implements RoleServiceContract
{
    public function findById(string $roleId): ?array
    {
        $role = Role::with('permissions')->find($roleId);

        return $role ? $this->toArray($role) : null;
    }

    public function create(array $data): array
    {
        $role = Role::create([
            'id'          => (string) Str::uuid(),
            'tenant_id'   => $data['tenant_id'] ?? null,
            'name'        => $data['name'],
            'slug'        => $data['slug'],
            'description' => $data['description'] ?? null,
            'is_system'   => $data['is_system'] ?? false,
        ]);

        return $this->toArray($role);
    }

    public function update(string $roleId, array $data): array
    {
        $role = Role::findOrFail($roleId);
        $role->update($data);
        $role->load('permissions');

        return $this->toArray($role->fresh(['permissions']));
    }

    public function delete(string $roleId): void
    {
        Role::findOrFail($roleId)->delete();
    }

    public function assignRole(string $userId, string $roleId, ?string $tenantId = null): void
    {
        User::findOrFail($userId);
        Role::findOrFail($roleId);

        DB::table('role_user')->insertOrIgnore([
            'user_id'    => $userId,
            'role_id'    => $roleId,
            'tenant_id'  => $tenantId,
            'created_at' => now(),
        ]);
    }

    public function revokeRole(string $userId, string $roleId, ?string $tenantId = null): void
    {
        $query = DB::table('role_user')
            ->where('user_id', $userId)
            ->where('role_id', $roleId);

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        $query->delete();
    }

    public function getUserRoles(string $userId, ?string $tenantId = null): array
    {
        $query = DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('role_user.user_id', $userId)
            ->select(['roles.id', 'roles.name', 'roles.slug', 'roles.is_system', 'role_user.tenant_id']);

        if ($tenantId !== null) {
            $query->where(function ($q) use ($tenantId): void {
                $q->where('role_user.tenant_id', $tenantId)
                    ->orWhereNull('role_user.tenant_id');
            });
        }

        return $query->get()->map(fn ($row) => (array) $row)->all();
    }

    public function listForTenant(string $tenantId): array
    {
        return Role::with('permissions')
            ->where(function ($q) use ($tenantId): void {
                $q->where('tenant_id', $tenantId)
                    ->orWhereNull('tenant_id');
            })
            ->get()
            ->map(fn (Role $role) => $this->toArray($role))
            ->all();
    }

    public function syncPermissions(string $roleId, array $permissionIds): void
    {
        $role = Role::findOrFail($roleId);
        $role->permissions()->sync($permissionIds);
    }

    // ──────────────────────────────────────────────────────────
    // Internal helpers
    // ──────────────────────────────────────────────────────────

    private function toArray(Role $role): array
    {
        return [
            'id'          => $role->id,
            'tenant_id'   => $role->tenant_id,
            'name'        => $role->name,
            'slug'        => $role->slug,
            'description' => $role->description,
            'is_system'   => $role->is_system,
            'permissions' => $role->permissions->pluck('slug')->all(),
            'created_at'  => $role->created_at?->toIso8601String(),
            'updated_at'  => $role->updated_at?->toIso8601String(),
        ];
    }
}
