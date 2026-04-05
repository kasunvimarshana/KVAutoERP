<?php declare(strict_types=1);
namespace Modules\Configuration\Domain\Entities;
class Setting {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $key,
        private readonly mixed $value,
        private readonly string $group,
        private readonly string $type, // string|integer|boolean|json|array
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getKey(): string { return $this->key; }
    public function getValue(): mixed { return $this->value; }
    public function getGroup(): string { return $this->group; }
    public function getType(): string { return $this->type; }
}
