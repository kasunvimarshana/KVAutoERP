<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class FeatureFlagDto
{
    public function __construct(
        public string $tenantId,
        public string $flagKey,
        public bool $isEnabled,
        public int $rolloutPercentage = 100,
        public ?array $conditions = null,
        public ?string $description = null,
        public ?array $metadata = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            tenantId: $data['tenant_id'],
            flagKey: $data['flag_key'],
            isEnabled: (bool) ($data['is_enabled'] ?? true),
            rolloutPercentage: (int) ($data['rollout_percentage'] ?? 100),
            conditions: $data['conditions'] ?? null,
            description: $data['description'] ?? null,
            metadata: $data['metadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'tenant_id'          => $this->tenantId,
            'flag_key'           => $this->flagKey,
            'is_enabled'         => $this->isEnabled,
            'rollout_percentage' => $this->rolloutPercentage,
            'conditions'         => $this->conditions,
            'description'        => $this->description,
            'metadata'           => $this->metadata,
        ];
    }
}
