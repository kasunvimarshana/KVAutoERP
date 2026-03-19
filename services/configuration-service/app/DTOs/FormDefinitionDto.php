<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class FormDefinitionDto
{
    public function __construct(
        public string $tenantId,
        public string $serviceName,
        public string $entityType,
        public array $fields,
        public ?array $validations = null,
        public bool $isActive = true,
        public ?array $metadata = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            tenantId: $data['tenant_id'],
            serviceName: $data['service_name'],
            entityType: $data['entity_type'],
            fields: $data['fields'],
            validations: $data['validations'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
            metadata: $data['metadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'tenant_id'    => $this->tenantId,
            'service_name' => $this->serviceName,
            'entity_type'  => $this->entityType,
            'fields'       => $this->fields,
            'validations'  => $this->validations,
            'is_active'    => $this->isActive,
            'metadata'     => $this->metadata,
        ];
    }
}
