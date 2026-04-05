<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

final class StockItem
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly int $productId,
        public readonly ?int $productVariantId,
        public readonly int $warehouseId,
        public readonly ?int $locationId,
        public readonly float $quantityAvailable,
        public readonly float $quantityReserved,
        public readonly float $quantityOnOrder,
        public readonly string $unitOfMeasure,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}

    public function getQuantityOnHand(): float
    {
        return $this->quantityAvailable + $this->quantityReserved;
    }
}
