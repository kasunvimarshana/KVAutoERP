<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'id'        => Uuid::uuid4()->toString(),
            'tenant_id' => Uuid::uuid4()->toString(),
            'name'      => $this->faker->name(),
            'email'     => $this->faker->unique()->safeEmail(),
            'password'  => Hash::make('password'),
            'phone'     => $this->faker->optional()->phoneNumber(),
            'avatar'    => $this->faker->optional()->imageUrl(200, 200, 'people'),
            'is_active' => true,
            'metadata'  => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function withMetadata(array $metadata): static
    {
        return $this->state(['metadata' => $metadata]);
    }

    public function forTenant(string $tenantId): static
    {
        return $this->state(['tenant_id' => $tenantId]);
    }
}
