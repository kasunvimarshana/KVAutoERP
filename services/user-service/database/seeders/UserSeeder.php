<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    private const TENANT_1 = '11111111-1111-1111-1111-111111111111';
    private const TENANT_2 = '22222222-2222-2222-2222-222222222222';

    public function run(): void
    {
        $users = [
            // Tenant 1
            [
                'id'          => Str::uuid()->toString(),
                'tenant_id'   => self::TENANT_1,
                'name'        => 'Alice Admin',
                'email'       => 'alice@tenant1.com',
                'password'    => Hash::make('password'),
                'role'        => 'admin',
                'permissions' => json_encode(['users:read', 'users:write', 'users:delete']),
                'status'      => 'active',
            ],
            [
                'id'          => Str::uuid()->toString(),
                'tenant_id'   => self::TENANT_1,
                'name'        => 'Bob Manager',
                'email'       => 'bob@tenant1.com',
                'password'    => Hash::make('password'),
                'role'        => 'manager',
                'permissions' => json_encode(['users:read', 'users:write']),
                'status'      => 'active',
            ],
            [
                'id'          => Str::uuid()->toString(),
                'tenant_id'   => self::TENANT_1,
                'name'        => 'Carol User',
                'email'       => 'carol@tenant1.com',
                'password'    => Hash::make('password'),
                'role'        => 'user',
                'permissions' => json_encode(['users:read']),
                'status'      => 'active',
            ],
            // Tenant 2
            [
                'id'          => Str::uuid()->toString(),
                'tenant_id'   => self::TENANT_2,
                'name'        => 'Dave Admin',
                'email'       => 'dave@tenant2.com',
                'password'    => Hash::make('password'),
                'role'        => 'admin',
                'permissions' => json_encode(['users:read', 'users:write', 'users:delete']),
                'status'      => 'active',
            ],
            [
                'id'          => Str::uuid()->toString(),
                'tenant_id'   => self::TENANT_2,
                'name'        => 'Eve Manager',
                'email'       => 'eve@tenant2.com',
                'password'    => Hash::make('password'),
                'role'        => 'manager',
                'permissions' => json_encode(['users:read', 'users:write']),
                'status'      => 'active',
            ],
            [
                'id'          => Str::uuid()->toString(),
                'tenant_id'   => self::TENANT_2,
                'name'        => 'Frank User',
                'email'       => 'frank@tenant2.com',
                'password'    => Hash::make('password'),
                'role'        => 'user',
                'permissions' => json_encode(['users:read']),
                'status'      => 'active',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['tenant_id' => $user['tenant_id'], 'email' => $user['email']],
                $user
            );
        }
    }
}
