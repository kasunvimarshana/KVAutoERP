<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Ramsey\Uuid\Uuid;

class UserProfileFactory extends Factory
{
    protected $model = UserProfile::class;

    public function definition(): array
    {
        $user = User::factory()->create();

        return [
            'id'            => Uuid::uuid4()->toString(),
            'user_id'       => $user->id,
            'tenant_id'     => $user->tenant_id,
            'first_name'    => $this->faker->firstName(),
            'last_name'     => $this->faker->lastName(),
            'date_of_birth' => $this->faker->optional()->date('Y-m-d', '-18 years'),
            'gender'        => $this->faker->optional()->randomElement(['male', 'female', 'non_binary', 'prefer_not_to_say']),
            'bio'           => $this->faker->optional()->paragraph(),
            'address'       => $this->faker->optional()->streetAddress(),
            'city'          => $this->faker->optional()->city(),
            'country'       => $this->faker->optional()->country(),
            'timezone'      => $this->faker->optional()->timezone(),
            'language'      => $this->faker->optional()->randomElement(['en', 'fr', 'de', 'es', 'ar']),
            'preferences'   => null,
            'metadata'      => null,
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state([
            'user_id'   => $user->id,
            'tenant_id' => $user->tenant_id,
        ]);
    }
}
