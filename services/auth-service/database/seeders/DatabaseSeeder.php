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
        // Permissions
        $permissions = [
            'users.read', 'users.write', 'users.delete',
            'tenants.read', 'tenants.write', 'tenants.delete',
            'roles.read', 'roles.write',
        ];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'api']);
        }

        // Roles
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'api']);
        $admin      = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'api']);

        $superAdmin->syncPermissions(Permission::all());
        $admin->syncPermissions(Permission::whereIn('name', ['users.read', 'users.write', 'tenants.read'])->get());

        // Default tenant
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'default'],
            ['name' => 'Default Tenant', 'status' => 'active', 'plan' => 'enterprise']
        );

        // Super admin user
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Super Admin',
                'password'  => env('ADMIN_PASSWORD', 'Admin@1234!'),
                'status'    => 'active',
            ]
        );
        $user->assignRole('super-admin');
    }
}
