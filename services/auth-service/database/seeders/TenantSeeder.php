<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Tenant\Entities\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = [
            [
                'id'     => '01000000-0000-0000-0000-000000000001',
                'name'   => 'Default Tenant',
                'slug'   => 'default',
                'domain' => 'default.app.example.com',
                'status' => Tenant::STATUS_ACTIVE,
                'settings' => [
                    'theme'    => 'light',
                    'timezone' => 'UTC',
                    'locale'   => 'en',
                ],
                'config' => [
                    'sso_enabled'       => false,
                    'mfa_required'      => false,
                    'password_policy'   => 'standard',
                ],
                'max_users' => 500,
            ],
            [
                'id'     => '01000000-0000-0000-0000-000000000002',
                'name'   => 'Acme Corp',
                'slug'   => 'acme',
                'domain' => 'acme.app.example.com',
                'status' => Tenant::STATUS_ACTIVE,
                'settings' => [
                    'theme'    => 'dark',
                    'timezone' => 'America/New_York',
                    'locale'   => 'en-US',
                ],
                'config' => [
                    'sso_enabled'       => true,
                    'mfa_required'      => true,
                    'password_policy'   => 'strict',
                ],
                'max_users' => 1000,
            ],
        ];

        foreach ($tenants as $data) {
            Tenant::updateOrCreate(['slug' => $data['slug']], $data);
        }

        $this->command->info('Tenants seeded successfully.');
    }
}
