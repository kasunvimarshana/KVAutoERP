<?php

namespace App\Domain\Auth\Entities;

use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * Extends Spatie's Permission to support optional tenant scoping and grouping.
 */
class Permission extends SpatiePermission
{
    protected $fillable = [
        'name',
        'guard_name',
        'group',
        'description',
    ];

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeForGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    // -------------------------------------------------------------------------
    // Pre-defined permission catalogue
    // -------------------------------------------------------------------------

    public static function catalogue(): array
    {
        return [
            // Orders
            'orders.view'    => ['group' => 'orders',    'description' => 'View orders'],
            'orders.create'  => ['group' => 'orders',    'description' => 'Create orders'],
            'orders.cancel'  => ['group' => 'orders',    'description' => 'Cancel orders'],

            // Inventory
            'inventory.view'   => ['group' => 'inventory', 'description' => 'View inventory'],
            'inventory.create' => ['group' => 'inventory', 'description' => 'Create inventory items'],
            'inventory.update' => ['group' => 'inventory', 'description' => 'Update inventory items'],
            'inventory.delete' => ['group' => 'inventory', 'description' => 'Delete inventory items'],
            'inventory.reserve'=> ['group' => 'inventory', 'description' => 'Reserve inventory'],

            // Users
            'users.view'   => ['group' => 'users', 'description' => 'View users'],
            'users.create' => ['group' => 'users', 'description' => 'Create users'],
            'users.update' => ['group' => 'users', 'description' => 'Update users'],
            'users.delete' => ['group' => 'users', 'description' => 'Delete users'],

            // Roles & permissions
            'roles.manage'       => ['group' => 'rbac', 'description' => 'Manage roles'],
            'permissions.manage' => ['group' => 'rbac', 'description' => 'Manage permissions'],

            // Tenant
            'tenant.manage' => ['group' => 'tenant', 'description' => 'Manage tenant settings'],

            // Reports
            'reports.view'   => ['group' => 'reports', 'description' => 'View reports'],
            'reports.export' => ['group' => 'reports', 'description' => 'Export reports'],
        ];
    }
}
