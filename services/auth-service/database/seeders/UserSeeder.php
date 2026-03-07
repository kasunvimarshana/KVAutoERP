<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all()->keyBy('slug');

        if ($tenants->isEmpty()) {
            $this->command->warn('No tenants found. Run TenantSeeder first.');
            return;
        }

        $usersPerTenant = [
            'acme' => [
                [
                    'name'        => 'Acme Admin',
                    'email'       => 'admin@acme.example.com',
                    'password'    => 'Admin@1234!',
                    'role'        => 'admin',
                    'status'      => 'active',
                    'permissions' => [],
                ],
                [
                    'name'        => 'Acme Manager',
                    'email'       => 'manager@acme.example.com',
                    'password'    => 'Manager@1234!',
                    'role'        => 'manager',
                    'status'      => 'active',
                    'permissions' => [
                        'users.view',
                        'users.create',
                        'users.update',
                        'inventory.view',
                        'inventory.create',
                        'inventory.update',
                        'reports.view',
                    ],
                ],
                [
                    'name'        => 'Acme User',
                    'email'       => 'user@acme.example.com',
                    'password'    => 'User@1234!',
                    'role'        => 'user',
                    'status'      => 'active',
                    'permissions' => [
                        'inventory.view',
                        'reports.view',
                    ],
                ],
            ],
            'beta' => [
                [
                    'name'        => 'Beta Admin',
                    'email'       => 'admin@beta.example.com',
                    'password'    => 'Admin@1234!',
                    'role'        => 'admin',
                    'status'      => 'active',
                    'permissions' => [],
                ],
                [
                    'name'        => 'Beta User',
                    'email'       => 'user@beta.example.com',
                    'password'    => 'User@1234!',
                    'role'        => 'user',
                    'status'      => 'active',
                    'permissions' => [
                        'inventory.view',
                    ],
                ],
            ],
        ];

        foreach ($usersPerTenant as $slug => $users) {
            $tenant = $tenants->get($slug);

            if ($tenant === null) {
                $this->command->warn("Tenant '{$slug}' not found, skipping.");
                continue;
            }

            foreach ($users as $userData) {
                User::firstOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'email'     => $userData['email'],
                    ],
                    [
                        'id'          => (string) Str::uuid(),
                        'tenant_id'   => $tenant->id,
                        'name'        => $userData['name'],
                        'email'       => $userData['email'],
                        'password'    => Hash::make($userData['password']),
                        'role'        => $userData['role'],
                        'permissions' => $userData['permissions'],
                        'status'      => $userData['status'],
                    ]
                );

                $this->command->info("User created: {$userData['email']} [{$tenant->name}]");
            }
        }
    }
}
