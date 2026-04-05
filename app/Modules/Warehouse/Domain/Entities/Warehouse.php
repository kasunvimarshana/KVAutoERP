<?php declare(strict_types=1);
namespace Modules\Warehouse\Domain\Entities;
class Warehouse {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $name,
        private readonly string $code,
        private readonly string $type,  // standard|virtual|transit|dropship
        private readonly ?string $address,
        private readonly bool $isActive,
        private readonly bool $isDefault,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getName(): string { return $this->name; }
    public function getCode(): string { return $this->code; }
    public function getType(): string { return $this->type; }
    public function getAddress(): ?string { return $this->address; }
    public function isActive(): bool { return $this->isActive; }
    public function isDefault(): bool { return $this->isDefault; }
}
