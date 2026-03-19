<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'id'           => $this->faker->uuid(),
            'tenant_id'    => Tenant::factory(),
            'name'         => $this->faker->unique()->word(),
            'display_name' => $this->faker->words(2, true),
            'description'  => $this->faker->sentence(),
            'guard_name'   => 'api',
            'is_system'    => false,
            'is_active'    => true,
        ];
    }
}
