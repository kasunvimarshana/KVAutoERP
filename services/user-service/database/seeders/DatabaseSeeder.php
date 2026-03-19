<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
        ]);
    }
}

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = Uuid::uuid4()->toString();

        // Super admin user
        $admin = User::firstOrCreate(
            ['tenant_id' => $tenantId, 'email' => 'admin@acme-corp.com'],
            [
                'id'        => Uuid::uuid4()->toString(),
                'name'      => 'Super Admin',
                'password'  => Hash::make('Admin@12345!'),
                'is_active' => true,
                'metadata'  => ['seeded' => true],
            ],
        );

        UserProfile::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'id'         => Uuid::uuid4()->toString(),
                'tenant_id'  => $tenantId,
                'first_name' => 'Super',
                'last_name'  => 'Admin',
                'timezone'   => 'UTC',
                'language'   => 'en',
            ],
        );

        // Regular user
        $regularUser = User::firstOrCreate(
            ['tenant_id' => $tenantId, 'email' => 'user@acme-corp.com'],
            [
                'id'        => Uuid::uuid4()->toString(),
                'name'      => 'Regular User',
                'password'  => Hash::make('User@12345!'),
                'is_active' => true,
            ],
        );

        UserProfile::firstOrCreate(
            ['user_id' => $regularUser->id],
            [
                'id'         => Uuid::uuid4()->toString(),
                'tenant_id'  => $tenantId,
                'first_name' => 'Regular',
                'last_name'  => 'User',
                'timezone'   => 'UTC',
                'language'   => 'en',
            ],
        );

        // Additional demo users
        User::factory()
            ->count(10)
            ->forTenant($tenantId)
            ->create()
            ->each(function (User $user) use ($tenantId): void {
                UserProfile::factory()->forUser($user)->create();
            });

        $this->command->info('✓ Demo users seeded successfully.');
        $this->command->info('  Admin: admin@acme-corp.com / Admin@12345!');
        $this->command->info('  User:  user@acme-corp.com / User@12345!');
        $this->command->info('  Tenant ID: ' . $tenantId);
    }
}
