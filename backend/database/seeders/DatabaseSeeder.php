<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ----------------------------------------------------------------
        // Permissions
        // ----------------------------------------------------------------
        $permissionNames = [
            'user.view', 'user.create', 'user.update', 'user.delete',
            'product.view', 'product.create', 'product.update', 'product.delete',
            'inventory.view', 'inventory.create', 'inventory.update', 'inventory.delete',
            'order.view', 'order.create', 'order.cancel',
            'tenant.config.view', 'tenant.config.manage',
        ];

        foreach ($permissionNames as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'api']);
        }

        // ----------------------------------------------------------------
        // Roles
        // ----------------------------------------------------------------
        $adminRole   = Role::firstOrCreate(['name' => 'admin',   'guard_name' => 'api']);
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'api']);
        $userRole    = Role::firstOrCreate(['name' => 'user',    'guard_name' => 'api']);

        $adminRole->syncPermissions(Permission::all());

        $managerRole->syncPermissions([
            'user.view', 'user.create', 'user.update',
            'product.view', 'product.create', 'product.update',
            'inventory.view', 'inventory.create', 'inventory.update',
            'order.view', 'order.create', 'order.cancel',
            'tenant.config.view',
        ]);

        $userRole->syncPermissions([
            'product.view',
            'inventory.view',
            'order.view', 'order.create',
        ]);

        // ----------------------------------------------------------------
        // Demo tenant
        // ----------------------------------------------------------------
        $tenant = Tenant::firstOrCreate(
            ['key' => 'demo'],
            [
                'name'      => 'Demo Tenant',
                'domain'    => 'demo.localhost',
                'is_active' => true,
            ]
        );

        // Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Admin User',
                'password'  => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $admin->syncRoles(['admin']);

        // Manager user
        $manager = User::firstOrCreate(
            ['email' => 'manager@demo.com'],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Manager User',
                'password'  => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $manager->syncRoles(['manager']);

        // Regular user
        $user = User::firstOrCreate(
            ['email' => 'user@demo.com'],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Regular User',
                'password'  => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $user->syncRoles(['user']);
    }
}

