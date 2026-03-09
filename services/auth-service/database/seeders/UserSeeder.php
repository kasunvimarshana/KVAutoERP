<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\User\Entities\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Create roles ---------------------------------------------------
        $roles = ['super-admin', 'admin', 'manager', 'user', 'viewer'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'api']);
        }

        // ---- Create permissions ---------------------------------------------
        $permissions = [
            'read',
            'write',
            'delete',
            'manage-users',
            'manage-tenants',
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'api']);
        }

        // Assign permissions to roles
        Role::findByName('super-admin', 'api')->givePermissionTo(Permission::all());
        Role::findByName('admin', 'api')->givePermissionTo(['read', 'write', 'delete', 'manage-users']);
        Role::findByName('manager', 'api')->givePermissionTo(['read', 'write', 'manage-users']);
        Role::findByName('user', 'api')->givePermissionTo(['read', 'write']);
        Role::findByName('viewer', 'api')->givePermissionTo(['read']);

        // ---- Create super-admin user ----------------------------------------
        $adminUser = User::updateOrCreate(
            [
                'email'     => 'admin@example.com',
                'tenant_id' => '01000000-0000-0000-0000-000000000001',
            ],
            [
                'name'              => 'Super Admin',
                'password'          => Hash::make('Admin@12345!'),
                'status'            => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'metadata'          => ['seeded' => true],
            ]
        );

        $adminUser->syncRoles(['super-admin']);

        // ---- Create a regular user for tenant "default" ---------------------
        $regularUser = User::updateOrCreate(
            [
                'email'     => 'user@example.com',
                'tenant_id' => '01000000-0000-0000-0000-000000000001',
            ],
            [
                'name'              => 'Regular User',
                'password'          => Hash::make('User@12345!'),
                'status'            => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'metadata'          => ['seeded' => true],
            ]
        );

        $regularUser->syncRoles(['user']);

        // ---- Create an admin user for tenant "acme" -------------------------
        $acmeAdmin = User::updateOrCreate(
            [
                'email'     => 'admin@acme.com',
                'tenant_id' => '01000000-0000-0000-0000-000000000002',
            ],
            [
                'name'              => 'Acme Admin',
                'password'          => Hash::make('AcmeAdmin@12345!'),
                'status'            => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'metadata'          => ['seeded' => true],
            ]
        );

        $acmeAdmin->syncRoles(['admin']);

        $this->command->info('Users and roles seeded successfully.');
        $this->command->table(
            ['Email', 'Role', 'Tenant'],
            [
                ['admin@example.com', 'super-admin', 'default'],
                ['user@example.com',  'user',        'default'],
                ['admin@acme.com',    'admin',        'acme'],
            ]
        );
    }
}
