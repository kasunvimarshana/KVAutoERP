<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FeatureFlag;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeatureFlagFactory extends Factory
{
    protected $model = FeatureFlag::class;

    public function definition(): array
    {
        return [
            'id'                 => $this->faker->uuid(),
            'tenant_id'          => $this->faker->uuid(),
            'flag_key'           => 'feature.' . $this->faker->unique()->slug(2, '.'),
            'is_enabled'         => true,
            'rollout_percentage' => 100,
            'conditions'         => null,
            'description'        => $this->faker->optional()->sentence(),
            'metadata'           => null,
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

    public function withRollout(int $percentage): static
    {
        return $this->state(['rollout_percentage' => $percentage]);
    }

    public function forTenant(string $tenantId): static
    {
        return $this->state(['tenant_id' => $tenantId]);
    }
}
