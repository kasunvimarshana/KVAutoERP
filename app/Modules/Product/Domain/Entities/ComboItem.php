<?php
declare(strict_types=1);
namespace Modules\Product\Domain\Entities;

use Modules\Core\Domain\ValueObjects\Money;

class ComboItem
{
    private ?int $id;
    private int $productId;
    private int $tenantId;
    private int $componentProductId;
    private float $quantity;
    private ?Money $priceOverride;
    private int $sortOrder;
    private ?array $metadata;

    public function __construct(
        int $productId,
        int $tenantId,
        int $componentProductId,
        float $quantity,
        ?Money $priceOverride = null,
        int $sortOrder = 0,
        ?array $metadata = null,
        ?int $id = null,
    ) {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be > 0');
        }
        $this->productId          = $productId;
        $this->tenantId           = $tenantId;
        $this->componentProductId = $componentProductId;
        $this->quantity           = $quantity;
        $this->priceOverride      = $priceOverride;
        $this->sortOrder          = $sortOrder;
        $this->metadata           = $metadata;
        $this->id                 = $id;
    }

    public function getId(): ?int { return $this->id; }
    public function getProductId(): int { return $this->productId; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getComponentProductId(): int { return $this->componentProductId; }
    public function getQuantity(): float { return $this->quantity; }
    public function getPriceOverride(): ?Money { return $this->priceOverride; }
    public function getSortOrder(): int { return $this->sortOrder; }
    public function getMetadata(): ?array { return $this->metadata; }

    public function updateDetails(float $quantity, ?Money $priceOverride, int $sortOrder, ?array $metadata): void
    {
        if ($quantity <= 0) throw new \InvalidArgumentException('Quantity must be > 0');
        $this->quantity      = $quantity;
        $this->priceOverride = $priceOverride;
        $this->sortOrder     = $sortOrder;
        $this->metadata      = $metadata;
    }
}
