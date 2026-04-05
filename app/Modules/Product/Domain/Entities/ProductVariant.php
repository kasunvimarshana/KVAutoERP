<?php declare(strict_types=1);
namespace Modules\Product\Domain\Entities;
class ProductVariant {
    public function __construct(
        private readonly ?int $id,
        private readonly int $productId,
        private readonly string $sku,
        private readonly string $name,
        private readonly array $attributes, // e.g. ['color'=>'red','size'=>'M']
        private readonly ?float $priceOverride,
        private readonly ?float $costOverride,
        private readonly bool $isActive,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getProductId(): int { return $this->productId; }
    public function getSku(): string { return $this->sku; }
    public function getName(): string { return $this->name; }
    public function getAttributes(): array { return $this->attributes; }
    public function getPriceOverride(): ?float { return $this->priceOverride; }
    public function getCostOverride(): ?float { return $this->costOverride; }
    public function isActive(): bool { return $this->isActive; }
}
