<?php declare(strict_types=1);
namespace Modules\Inventory\Domain\Entities;
class CycleCountLine {
    public function __construct(
        private readonly ?int $id,
        private readonly int $cycleCountId,
        private readonly int $productId,
        private readonly ?int $locationId,
        private readonly float $systemQuantity,
        private readonly ?float $countedQuantity,
        private readonly ?float $variance,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getCycleCountId(): int { return $this->cycleCountId; }
    public function getProductId(): int { return $this->productId; }
    public function getLocationId(): ?int { return $this->locationId; }
    public function getSystemQuantity(): float { return $this->systemQuantity; }
    public function getCountedQuantity(): ?float { return $this->countedQuantity; }
    public function getVariance(): ?float { return $this->variance; }
    public function hasVariance(): bool {
        return $this->variance !== null && abs($this->variance) >= PHP_FLOAT_EPSILON;
    }
}
