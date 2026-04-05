<?php declare(strict_types=1);
namespace Modules\POS\Domain\Entities;
class Terminal {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $name,
        private readonly string $code,
        private readonly int $warehouseId,
        private readonly bool $isActive,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getName(): string { return $this->name; }
    public function getCode(): string { return $this->code; }
    public function getWarehouseId(): int { return $this->warehouseId; }
    public function isActive(): bool { return $this->isActive; }
}
