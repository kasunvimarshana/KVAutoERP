<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Models\Tenant;
use App\Domain\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Database Seeder - Creates demo tenants and users.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create default tenant
        $defaultTenant = Tenant::updateOrCreate(
            ['slug' => 'default'],
            [
                'name' => 'Default Tenant',
                'slug' => 'default',
                'plan' => 'enterprise',
                'is_active' => true,
                'feature_flags' => [
                    '2fa' => false,
                    'social_login' => false,
                    'advanced_reports' => true,
                    'webhook' => true,
                ],
                'settings' => [
                    'max_users' => 1000,
                    'timezone' => 'UTC',
                    'locale' => 'en',
                ],
            ]
        );

        // Create demo tenant
        $demoTenant = Tenant::updateOrCreate(
            ['slug' => 'demo'],
            [
                'name' => 'Demo Tenant',
                'slug' => 'demo',
                'plan' => 'starter',
                'is_active' => true,
                'feature_flags' => [
                    '2fa' => false,
                    'social_login' => false,
                    'advanced_reports' => false,
                    'webhook' => false,
                ],
                'settings' => [
                    'max_users' => 50,
                    'timezone' => 'UTC',
                    'locale' => 'en',
                ],
            ]
        );

        // Create super admin for default tenant
        User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'admin@example.com', 'tenant_id' => $defaultTenant->id],
            [
                'tenant_id' => $defaultTenant->id,
                'name' => 'Super Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create regular user for default tenant
        User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'user@example.com', 'tenant_id' => $defaultTenant->id],
            [
                'tenant_id' => $defaultTenant->id,
                'name' => 'Test User',
                'email' => 'user@example.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create admin for demo tenant
        User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'admin@demo.example.com', 'tenant_id' => $demoTenant->id],
            [
                'tenant_id' => $demoTenant->id,
                'name' => 'Demo Admin',
                'email' => 'admin@demo.example.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Database seeded successfully!');
        $this->command->info('Default tenant super admin: admin@example.com / password123');
        $this->command->info('Demo tenant admin: admin@demo.example.com / password123');
    }
}
