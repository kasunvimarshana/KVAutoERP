<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class ModuleRegistryDto
{
    public function __construct(
        public string $tenantId,
        public string $moduleName,
        public string $moduleKey,
        public bool $isEnabled = true,
        public ?array $configuration = null,
        public ?array $dependencies = null,
        public ?string $version = null,
        public ?array $metadata = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            tenantId: $data['tenant_id'],
            moduleName: $data['module_name'],
            moduleKey: $data['module_key'],
            isEnabled: (bool) ($data['is_enabled'] ?? true),
            configuration: $data['configuration'] ?? null,
            dependencies: $data['dependencies'] ?? null,
            version: $data['version'] ?? null,
            metadata: $data['metadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'tenant_id'     => $this->tenantId,
            'module_name'   => $this->moduleName,
            'module_key'    => $this->moduleKey,
            'is_enabled'    => $this->isEnabled,
            'configuration' => $this->configuration,
            'dependencies'  => $this->dependencies,
            'version'       => $this->version,
            'metadata'      => $this->metadata,
        ];
    }
}
