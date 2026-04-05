<?php declare(strict_types=1);
namespace Modules\Pricing\Domain\Entities;
class PriceListItem {
    public function __construct(
        private readonly ?int $id,
        private readonly int $priceListId,
        private readonly int $productId,
        private readonly string $priceType,   // fixed|percentage
        private readonly float $price,
        private readonly float $minQuantity,  // min qty for this tier
        private readonly ?\DateTimeInterface $validFrom,
        private readonly ?\DateTimeInterface $validTo,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getPriceListId(): int { return $this->priceListId; }
    public function getProductId(): int { return $this->productId; }
    public function getPriceType(): string { return $this->priceType; }
    public function getPrice(): float { return $this->price; }
    public function getMinQuantity(): float { return $this->minQuantity; }
    public function getValidFrom(): ?\DateTimeInterface { return $this->validFrom; }
    public function getValidTo(): ?\DateTimeInterface { return $this->validTo; }
    public function isValidOn(\DateTimeInterface $date): bool {
        if ($this->validFrom && $date < $this->validFrom) return false;
        if ($this->validTo && $date > $this->validTo) return false;
        return true;
    }
}
