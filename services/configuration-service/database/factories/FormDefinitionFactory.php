<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FormDefinition;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormDefinitionFactory extends Factory
{
    protected $model = FormDefinition::class;

    public function definition(): array
    {
        return [
            'id'           => $this->faker->uuid(),
            'tenant_id'    => $this->faker->uuid(),
            'service_name' => $this->faker->randomElement(['orders', 'inventory', 'crm', 'products']),
            'entity_type'  => $this->faker->randomElement(['order', 'product', 'customer', 'invoice']),
            'fields'       => [
                [
                    'name'     => 'name',
                    'type'     => 'text',
                    'label'    => 'Name',
                    'required' => true,
                    'order'    => 1,
                ],
                [
                    'name'     => 'description',
                    'type'     => 'textarea',
                    'label'    => 'Description',
                    'required' => false,
                    'order'    => 2,
                ],
            ],
            'validations'  => null,
            'is_active'    => true,
            'version'      => 1,
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

    public function forTenant(string $tenantId): static
    {
        return $this->state(['tenant_id' => $tenantId]);
    }
}
