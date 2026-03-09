<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Role & Permission Seeder.
 *
 * Seeds the canonical set of roles and permissions used across all tenants.
 * Roles use the 'api' guard to align with the Passport-backed auth guard.
 */
final class RolePermissionSeeder extends Seeder
{
    private const GUARD = 'api';

    /**
     * All system-wide permissions.
     *
     * @var array<string>
     */
    private const PERMISSIONS = [
        // Inventory
        'view-inventory',
        'manage-inventory',
        'export-inventory',

        // Orders
        'view-orders',
        'manage-orders',
        'approve-orders',
        'export-orders',

        // Reports
        'view-reports',
        'export-reports',
        'create-reports',

        // Users
        'view-users',
        'manage-users',
        'invite-users',

        // Tenant
        'manage-tenant',
        'view-billing',
        'manage-billing',

        // Audit
        'view-audit-log',
    ];

    /**
     * Role → permission assignments.
     *
     * @var array<string, array<string>>
     */
    private const ROLE_PERMISSIONS = [
        'super-admin' => [], // super-admin bypasses all permission checks via Gate

        'admin' => [
            'view-inventory', 'manage-inventory', 'export-inventory',
            'view-orders', 'manage-orders', 'approve-orders', 'export-orders',
            'view-reports', 'export-reports', 'create-reports',
            'view-users', 'manage-users', 'invite-users',
            'manage-tenant', 'view-billing',
            'view-audit-log',
        ],

        'manager' => [
            'view-inventory', 'manage-inventory', 'export-inventory',
            'view-orders', 'manage-orders', 'approve-orders', 'export-orders',
            'view-reports', 'export-reports', 'create-reports',
            'view-users',
            'view-audit-log',
        ],

        'staff' => [
            'view-inventory', 'manage-inventory',
            'view-orders', 'manage-orders',
            'view-reports',
        ],

        'viewer' => [
            'view-inventory',
            'view-orders',
            'view-reports',
        ],
    ];

    /**
     * Run the seeder.
     */
    public function run(): void
    {
        // Reset cached roles & permissions so changes take effect immediately.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // ── Create permissions ────────────────────────────────────────────
        $permissionMap = [];

        foreach (self::PERMISSIONS as $name) {
            /** @var Permission $permission */
            $permission = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => self::GUARD],
                ['description' => $this->permissionDescription($name)],
            );

            $permissionMap[$name] = $permission;
        }

        // ── Create roles and assign permissions ──────────────────────────
        foreach (self::ROLE_PERMISSIONS as $roleName => $permissionNames) {
            /** @var Role $role */
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => self::GUARD],
                ['description' => $this->roleDescription($roleName)],
            );

            if (!empty($permissionNames)) {
                $role->syncPermissions(
                    array_map(
                        fn (string $p) => $permissionMap[$p],
                        $permissionNames,
                    )
                );
            }
        }

        $this->command->info('Roles and permissions seeded successfully.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────

    private function permissionDescription(string $name): string
    {
        return ucwords(str_replace('-', ' ', $name));
    }

    private function roleDescription(string $name): string
    {
        return match ($name) {
            'super-admin' => 'Platform super-administrator — unrestricted access.',
            'admin'       => 'Tenant administrator — full tenant access.',
            'manager'     => 'Team manager — operational and reporting access.',
            'staff'       => 'General staff — day-to-day operational access.',
            'viewer'      => 'Read-only viewer — no write access.',
            default       => ucwords(str_replace('-', ' ', $name)),
        };
    }
}
