<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Persistence\Models\Tenant;
use App\Infrastructure\Persistence\Models\TenantConfiguration;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

/**
 * Tenant Seeder.
 *
 * Creates a single demo tenant with a complete set of configuration entries.
 * Use for local development and CI environments only.
 */
class TenantSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $tenantId = Uuid::uuid4()->toString();

        /** @var Tenant $tenant */
        $tenant = Tenant::query()->updateOrCreate(
            ['slug' => 'demo-tenant'],
            [
                'id'            => $tenantId,
                'name'          => 'Demo Tenant',
                'slug'          => 'demo-tenant',
                'domain'        => null,
                'database_name' => 'tenant_demo_tenant',
                'settings'      => [
                    'timezone'         => 'UTC',
                    'locale'           => 'en',
                    'currency'         => 'USD',
                    'max_users'        => 10,
                    'features'         => ['inventory', 'reports'],
                    'queue_connection' => 'sync',
                ],
                'is_active'     => true,
                'plan'          => 'pro',
                'billing_email' => 'billing@demo-tenant.example.com',
            ]
        );

        // Seed configuration entries for the demo tenant.
        $configs = [
            [
                'config_key'   => 'mail.from.address',
                'config_value' => 'no-reply@demo-tenant.example.com',
                'environment'  => 'production',
                'is_secret'    => false,
            ],
            [
                'config_key'   => 'mail.from.name',
                'config_value' => 'Demo Tenant',
                'environment'  => 'production',
                'is_secret'    => false,
            ],
            [
                'config_key'   => 'cache.prefix',
                'config_value' => 'tenant:demo-tenant:',
                'environment'  => 'production',
                'is_secret'    => false,
            ],
            [
                'config_key'   => 'app.timezone',
                'config_value' => 'UTC',
                'environment'  => 'production',
                'is_secret'    => false,
            ],
            [
                'config_key'   => 'stripe.secret_key',
                'config_value' => 'sk_test_demo_secret',
                'environment'  => 'production',
                'is_secret'    => true,
            ],
            [
                'config_key'   => 'inventory.low_stock_threshold',
                'config_value' => '5',
                'environment'  => 'production',
                'is_secret'    => false,
            ],
        ];

        foreach ($configs as $config) {
            TenantConfiguration::query()->updateOrCreate(
                [
                    'tenant_id'   => $tenant->id,
                    'config_key'  => $config['config_key'],
                    'environment' => $config['environment'],
                ],
                array_merge(['id' => Uuid::uuid4()->toString()], $config, ['tenant_id' => $tenant->id])
            );
        }

        $this->command->info('Demo tenant seeded: demo-tenant (ID: ' . $tenant->id . ')');
    }
}
