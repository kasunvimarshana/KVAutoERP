<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class TenantConfigurationDto
{
    public function __construct(
        public string $tenantId,
        public string $serviceName,
        public string $configKey,
        public array $configValue,
        public string $configType,
        public bool $isEncrypted = false,
        public bool $isActive = true,
        public ?string $description = null,
        public ?array $metadata = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            tenantId: $data['tenant_id'],
            serviceName: $data['service_name'],
            configKey: $data['config_key'],
            configValue: $data['config_value'],
            configType: $data['config_type'],
            isEncrypted: (bool) ($data['is_encrypted'] ?? false),
            isActive: (bool) ($data['is_active'] ?? true),
            description: $data['description'] ?? null,
            metadata: $data['metadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'tenant_id'    => $this->tenantId,
            'service_name' => $this->serviceName,
            'config_key'   => $this->configKey,
            'config_value' => $this->configValue,
            'config_type'  => $this->configType,
            'is_encrypted' => $this->isEncrypted,
            'is_active'    => $this->isActive,
            'description'  => $this->description,
            'metadata'     => $this->metadata,
        ];
    }
}
