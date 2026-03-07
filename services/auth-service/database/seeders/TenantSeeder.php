<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = [
            [
                'id'     => (string) Str::uuid(),
                'name'   => 'Acme Corporation',
                'slug'   => 'acme',
                'status' => 'active',
                'plan'   => 'enterprise',
                'config' => [
                    'mail' => [
                        'from_address' => 'noreply@acme.example.com',
                        'from_name'    => 'Acme Corporation',
                        'driver'       => 'smtp',
                        'host'         => 'smtp.mailgun.org',
                        'port'         => 587,
                        'encryption'   => 'tls',
                    ],
                    'payment' => [
                        'gateway'    => 'stripe',
                        'currency'   => 'USD',
                        'tax_rate'   => 0.08,
                        'trial_days' => 14,
                    ],
                    'notifications' => [
                        'slack_webhook'  => null,
                        'email_alerts'   => true,
                        'sms_alerts'     => false,
                        'webhook_url'    => 'https://acme.example.com/webhooks/notifications',
                    ],
                    'features' => [
                        'multi_warehouse' => true,
                        'advanced_reports' => true,
                        'api_access'      => true,
                        'custom_roles'    => true,
                    ],
                ],
            ],
            [
                'id'     => (string) Str::uuid(),
                'name'   => 'Beta Startup',
                'slug'   => 'beta',
                'status' => 'active',
                'plan'   => 'starter',
                'config' => [
                    'mail' => [
                        'from_address' => 'hello@beta.example.com',
                        'from_name'    => 'Beta Startup',
                        'driver'       => 'smtp',
                        'host'         => 'smtp.sendgrid.net',
                        'port'         => 587,
                        'encryption'   => 'tls',
                    ],
                    'payment' => [
                        'gateway'    => 'paypal',
                        'currency'   => 'USD',
                        'tax_rate'   => 0.05,
                        'trial_days' => 7,
                    ],
                    'notifications' => [
                        'slack_webhook'  => 'https://hooks.slack.com/services/FAKE/BETA/WEBHOOK',
                        'email_alerts'   => true,
                        'sms_alerts'     => false,
                        'webhook_url'    => null,
                    ],
                    'features' => [
                        'multi_warehouse' => false,
                        'advanced_reports' => false,
                        'api_access'      => true,
                        'custom_roles'    => false,
                    ],
                ],
            ],
        ];

        foreach ($tenants as $data) {
            Tenant::firstOrCreate(['slug' => $data['slug']], $data);
        }

        $this->command->info('Tenants seeded: acme, beta');
    }
}
