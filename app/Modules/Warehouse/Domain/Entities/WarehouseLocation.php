<?php declare(strict_types=1);
namespace Modules\Warehouse\Domain\Entities;
class WarehouseLocation {
    public function __construct(
        private readonly ?int $id,
        private readonly int $warehouseId,
        private readonly string $name,
        private readonly string $code,
        private readonly string $type,      // zone|aisle|rack|shelf|bin
        private readonly ?int $parentId,
        private readonly string $path,
        private readonly int $level,
        private readonly bool $isActive,
        private readonly bool $isPickable,
        private readonly bool $isReceivable,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getWarehouseId(): int { return $this->warehouseId; }
    public function getName(): string { return $this->name; }
    public function getCode(): string { return $this->code; }
    public function getType(): string { return $this->type; }
    public function getParentId(): ?int { return $this->parentId; }
    public function getPath(): string { return $this->path; }
    public function getLevel(): int { return $this->level; }
    public function isActive(): bool { return $this->isActive; }
    public function isPickable(): bool { return $this->isPickable; }
    public function isReceivable(): bool { return $this->isReceivable; }
}
