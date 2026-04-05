<?php declare(strict_types=1);
namespace Modules\Product\Domain\Entities;
/** Bill of Materials component for combo products */
class ProductComponent {
    public function __construct(
        private readonly ?int $id,
        private readonly int $parentProductId,
        private readonly int $componentProductId,
        private readonly float $quantity,
        private readonly string $unit,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getParentProductId(): int { return $this->parentProductId; }
    public function getComponentProductId(): int { return $this->componentProductId; }
    public function getQuantity(): float { return $this->quantity; }
    public function getUnit(): string { return $this->unit; }
}
