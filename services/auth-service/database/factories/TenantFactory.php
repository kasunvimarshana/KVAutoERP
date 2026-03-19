<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $name = $this->faker->company();
        return [
            'id'           => $this->faker->uuid(),
            'name'         => $name,
            'slug'         => Str::slug($name) . '-' . $this->faker->randomNumber(4),
            'domain'       => null,
            'is_active'    => true,
            'plan'         => 'standard',
            'feature_flags' => [
                'sso_enabled'                => true,
                'multi_device_sessions'      => true,
                'suspicious_activity_alerts' => true,
                'audit_logging'              => true,
                'rate_limiting'              => true,
            ],
            'configurations' => [],
            'token_lifetimes' => [
                'access'  => 15,
                'refresh' => 43200,
            ],
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function withCustomTokenLifetimes(int $accessMinutes, int $refreshMinutes): static
    {
        return $this->state([
            'token_lifetimes' => [
                'access'  => $accessMinutes,
                'refresh' => $refreshMinutes,
            ],
        ]);
    }
}
