<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TenantConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;

class TenantConfigurationFactory extends Factory
{
    protected $model = TenantConfiguration::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['string', 'json', 'boolean', 'integer']);

        return [
            'id'           => $this->faker->uuid(),
            'tenant_id'    => $this->faker->uuid(),
            'service_name' => $this->faker->randomElement(['inventory', 'orders', 'finance', 'crm', 'products']),
            'config_key'   => $this->faker->slug(3, '.'),
            'config_value' => ['value' => $this->generateValueForType($type)],
            'config_type'  => $type,
            'is_encrypted' => false,
            'is_active'    => true,
            'description'  => $this->faker->optional()->sentence(),
            'metadata'     => null,
        ];
    }

    public function active(): static
    {
        return $this->state(['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function encrypted(): static
    {
        return $this->state(['is_encrypted' => true]);
    }

    public function forService(string $serviceName): static
    {
        return $this->state(['service_name' => $serviceName]);
    }

    public function forTenant(string $tenantId): static
    {
        return $this->state(['tenant_id' => $tenantId]);
    }

    private function generateValueForType(string $type): mixed
    {
        return match ($type) {
            'boolean' => $this->faker->boolean(),
            'integer' => $this->faker->numberBetween(1, 1000),
            'json'    => ['key' => $this->faker->word(), 'value' => $this->faker->word()],
            default   => $this->faker->word(),
        };
    }
}
