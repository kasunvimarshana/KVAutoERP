<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        $resources = ['inventory', 'products', 'orders', 'users', 'reports', 'finance'];
        $actions   = ['view', 'create', 'edit', 'delete', 'export'];

        return [
            'id'           => $this->faker->uuid(),
            'name'         => $this->faker->randomElement($resources) . '.' . $this->faker->unique()->word(),
            'display_name' => $this->faker->words(3, true),
            'description'  => $this->faker->sentence(),
            'guard_name'   => 'api',
            'group'        => $this->faker->randomElement($resources),
            'is_system'    => false,
        ];
    }
}
