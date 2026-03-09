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
        $this->command->info('Seeding tenants...');

        // ---- Tenant 1: Acme Corp (Professional) --------------------------------
        $acme = Tenant::updateOrCreate(
            ['slug' => 'acme'],
            [
                'id'                 => Str::uuid()->toString(),
                'name'               => 'Acme Corporation',
                'slug'               => 'acme',
                'domain'             => 'acme.example.com',
                'status'             => Tenant::STATUS_ACTIVE,
                'plan'               => Tenant::PLAN_PROFESSIONAL,
                'max_users'          => 500,
                'max_organizations'  => 50,
                'trial_ends_at'      => null,
                'subscription_ends_at' => now()->addYear(),
                'settings'           => [
                    'theme'    => 'light',
                    'language' => 'en',
                    'timezone' => 'America/New_York',
                ],
                'config'             => [
                    'features' => [
                        'sso'           => true,
                        'advanced_rbac' => true,
                        'api_access'    => true,
                        'audit_log'     => true,
                    ],
                    'timezone' => 'America/New_York',
                ],
                'mail_config'        => [
                    'transport'    => 'smtp',
                    'host'         => 'smtp.mailgun.org',
                    'port'         => 587,
                    'encryption'   => 'tls',
                    'username'     => 'postmaster@acme.example.com',
                    'password'     => 'secret', // In production: use encrypted vault
                    'from_address' => 'noreply@acme.example.com',
                    'from_name'    => 'Acme Corp',
                ],
                'cache_config'       => [
                    'driver'     => 'redis',
                    'connection' => 'default',
                    'prefix'     => 'acme_cache_',
                ],
                'metadata'           => [
                    'industry'   => 'Technology',
                    'country'    => 'US',
                    'created_by' => 'seeder',
                ],
            ]
        );

        $this->command->info("  ✓ Tenant created: {$acme->name} (slug: {$acme->slug})");

        // ---- Tenant 2: Beta Startup (Starter / Trial) --------------------------
        $beta = Tenant::updateOrCreate(
            ['slug' => 'beta-startup'],
            [
                'id'                 => Str::uuid()->toString(),
                'name'               => 'Beta Startup Inc.',
                'slug'               => 'beta-startup',
                'domain'             => 'beta.example.com',
                'status'             => Tenant::STATUS_ACTIVE,
                'plan'               => Tenant::PLAN_STARTER,
                'max_users'          => 50,
                'max_organizations'  => 5,
                'trial_ends_at'      => now()->addDays(14),
                'subscription_ends_at' => now()->addDays(14),
                'settings'           => [
                    'theme'    => 'dark',
                    'language' => 'en',
                    'timezone' => 'Europe/London',
                ],
                'config'             => [
                    'features' => [
                        'sso'           => false,
                        'advanced_rbac' => false,
                        'api_access'    => true,
                        'audit_log'     => false,
                    ],
                    'timezone' => 'Europe/London',
                ],
                'metadata'           => [
                    'industry'   => 'E-Commerce',
                    'country'    => 'GB',
                    'created_by' => 'seeder',
                ],
            ]
        );

        $this->command->info("  ✓ Tenant created: {$beta->name} (slug: {$beta->slug})");
        $this->command->info('Tenant seeding complete.');
    }
}
