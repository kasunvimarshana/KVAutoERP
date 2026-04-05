<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\Entities;

final class Setting
{
    public function __construct(
        private readonly string $id,
        private readonly string $tenantId,
        private readonly string $key,
        private readonly string $value,
        private readonly string $type,
        private readonly ?string $module = null,
        private readonly ?string $description = null,
    ) {}

    public function getId(): string { return $this->id; }
    public function getTenantId(): string { return $this->tenantId; }
    public function getKey(): string { return $this->key; }
    public function getRawValue(): string { return $this->value; }
    public function getType(): string { return $this->type; }
    public function getModule(): ?string { return $this->module; }
    public function getDescription(): ?string { return $this->description; }

    public function getCastedValue(): mixed
    {
        return match ($this->type) {
            'integer' => (int) $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json', 'array' => json_decode($this->value, true),
            default => $this->value,
        };
    }
}
