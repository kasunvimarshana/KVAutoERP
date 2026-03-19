<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'id'              => $this->faker->uuid(),
            'tenant_id'       => Tenant::factory(),
            'name'            => $this->faker->name(),
            'email'           => $this->faker->unique()->safeEmail(),
            'password'        => Hash::make('password'),
            'token_version'   => 1,
            'is_active'       => true,
            'is_locked'       => false,
            'failed_login_attempts' => 0,
            'email_verified_at' => now(),
            'password_changed_at' => now(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function locked(): static
    {
        return $this->state([
            'is_locked'    => true,
            'locked_until' => now()->addMinutes(30),
        ]);
    }

    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }
}
