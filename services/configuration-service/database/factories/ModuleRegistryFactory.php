<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ModuleRegistry;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModuleRegistryFactory extends Factory
{
    protected $model = ModuleRegistry::class;

    public function definition(): array
    {
        $moduleKey = 'module.' . $this->faker->unique()->slug(2, '.');

        return [
            'id'            => $this->faker->uuid(),
            'tenant_id'     => $this->faker->uuid(),
            'module_name'   => $this->faker->words(2, true),
            'module_key'    => $moduleKey,
            'is_enabled'    => true,
            'configuration' => null,
            'dependencies'  => [],
            'version'       => '1.0.0',
            'metadata'      => null,
        ];
    }

    public function enabled(): static
    {
        return $this->state(['is_enabled' => true]);
    }

    public function disabled(): static
    {
        return $this->state(['is_enabled' => false]);
    }

    public function withDependencies(array $deps): static
    {
        return $this->state(['dependencies' => $deps]);
    }

    public function forTenant(string $tenantId): static
    {
        return $this->state(['tenant_id' => $tenantId]);
    }
}
