<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class WorkflowDefinitionDto
{
    public function __construct(
        public string $tenantId,
        public string $name,
        public string $entityType,
        public array $states,
        public array $transitions,
        public ?array $guards = null,
        public ?array $actions = null,
        public bool $isActive = true,
        public ?array $metadata = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            tenantId: $data['tenant_id'],
            name: $data['name'],
            entityType: $data['entity_type'],
            states: $data['states'],
            transitions: $data['transitions'],
            guards: $data['guards'] ?? null,
            actions: $data['actions'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
            metadata: $data['metadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'tenant_id'   => $this->tenantId,
            'name'        => $this->name,
            'entity_type' => $this->entityType,
            'states'      => $this->states,
            'transitions' => $this->transitions,
            'guards'      => $this->guards,
            'actions'     => $this->actions,
            'is_active'   => $this->isActive,
            'metadata'    => $this->metadata,
        ];
    }
}
