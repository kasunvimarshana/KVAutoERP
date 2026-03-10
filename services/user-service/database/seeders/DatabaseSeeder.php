<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Models\Role;
use App\Domain\Models\UserProfile;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = 1;

        $adminRole = Role::create([
            'tenant_id' => $tenantId,
            'name' => 'Administrator',
            'slug' => 'admin',
            'description' => 'Full system access',
            'permissions' => ['*'],
            'is_active' => true,
        ]);

        $managerRole = Role::create([
            'tenant_id' => $tenantId,
            'name' => 'Manager',
            'slug' => 'manager',
            'description' => 'Manage products, inventory, and orders',
            'permissions' => ['products:read', 'products:write', 'inventory:read', 'inventory:write', 'orders:read', 'orders:write'],
            'is_active' => true,
        ]);

        Role::create([
            'tenant_id' => $tenantId,
            'name' => 'Viewer',
            'slug' => 'viewer',
            'description' => 'Read-only access',
            'permissions' => ['products:read', 'inventory:read', 'orders:read'],
            'is_active' => true,
        ]);

        $adminProfile = UserProfile::create([
            'user_id' => 1,
            'tenant_id' => $tenantId,
            'timezone' => 'UTC',
            'locale' => 'en',
            'theme' => 'light',
            'is_active' => true,
        ]);

        $adminProfile->roles()->attach($adminRole->id);

        $managerProfile = UserProfile::create([
            'user_id' => 2,
            'tenant_id' => $tenantId,
            'timezone' => 'America/New_York',
            'locale' => 'en',
            'theme' => 'dark',
            'is_active' => true,
        ]);

        $managerProfile->roles()->attach($managerRole->id);

        $this->command->info('User database seeded!');
    }
}
