<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\WorkflowDefinition;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkflowDefinitionFactory extends Factory
{
    protected $model = WorkflowDefinition::class;

    public function definition(): array
    {
        return [
            'id'          => $this->faker->uuid(),
            'tenant_id'   => $this->faker->uuid(),
            'name'        => $this->faker->unique()->words(3, true) . ' Workflow',
            'entity_type' => $this->faker->randomElement(['order', 'invoice', 'purchase_request', 'leave_request']),
            'states'      => [
                ['name' => 'draft',    'label' => 'Draft',    'initial' => true,  'final' => false],
                ['name' => 'pending',  'label' => 'Pending',  'initial' => false, 'final' => false],
                ['name' => 'approved', 'label' => 'Approved', 'initial' => false, 'final' => true],
                ['name' => 'rejected', 'label' => 'Rejected', 'initial' => false, 'final' => true],
            ],
            'transitions' => [
                ['from' => 'draft',   'to' => 'pending',  'event' => 'submit'],
                ['from' => 'pending', 'to' => 'approved', 'event' => 'approve'],
                ['from' => 'pending', 'to' => 'rejected', 'event' => 'reject'],
            ],
            'guards'    => null,
            'actions'   => null,
            'is_active' => true,
            'version'   => 1,
            'metadata'  => null,
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
