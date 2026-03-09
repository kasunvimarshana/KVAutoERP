<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Entities;

use App\Domain\Inventory\Enums\ProductStatus;
use App\Domain\Inventory\ValueObjects\Money;
use App\Domain\Inventory\ValueObjects\Sku;
use App\Domain\Inventory\ValueObjects\StockQuantity;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Product domain entity (not Eloquent).
 *
 * Rich domain model encapsulating all product business rules.
 */
final class Product
{
    public function __construct(
        public readonly string $id,
        public readonly string $tenantId,
        public readonly Sku $sku,
        public string $name,
        public string $description,
        public ?Category $category,
        public Money $price,
        public Money $costPrice,
        public StockQuantity $stockQuantity,
        public int $minStockLevel,
        public int $maxStockLevel,
        public string $unit,
        public ?string $barcode,
        public ProductStatus $status,
        public bool $isActive,
        public array $tags,
        public array $attributes,
        public readonly DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
        public int $reservedQuantity = 0,
    ) {
        if (trim($this->name) === '') {
            throw new InvalidArgumentException('Product name cannot be empty.');
        }

        if ($this->minStockLevel < 0) {
            throw new InvalidArgumentException('Minimum stock level cannot be negative.');
        }
    }

    /**
     * Construct a Product entity from a raw array (e.g., database row).
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $price = isset($data['price'])
            ? new Money(
                amount: (float) $data['price'],
                currency: $data['currency'] ?? 'USD',
            )
            : new Money(0.0, 'USD');

        $costPrice = isset($data['cost_price'])
            ? new Money(
                amount: (float) $data['cost_price'],
                currency: $data['currency'] ?? 'USD',
            )
            : new Money(0.0, 'USD');

        $category = null;
        if (!empty($data['category']) && is_array($data['category'])) {
            $category = Category::fromArray($data['category']);
        }

        $tags = $data['tags'] ?? [];
        if (is_string($tags)) {
            $tags = json_decode($tags, true) ?? [];
        }

        $attributes = $data['attributes'] ?? [];
        if (is_string($attributes)) {
            $attributes = json_decode($attributes, true) ?? [];
        }

        return new self(
            id: $data['id'],
            tenantId: $data['tenant_id'],
            sku: new Sku($data['sku']),
            name: $data['name'],
            description: $data['description'] ?? '',
            category: $category,
            price: $price,
            costPrice: $costPrice,
            stockQuantity: new StockQuantity((int) ($data['stock_quantity'] ?? 0)),
            minStockLevel: (int) ($data['min_stock_level'] ?? 0),
            maxStockLevel: (int) ($data['max_stock_level'] ?? 0),
            unit: $data['unit'] ?? 'unit',
            barcode: $data['barcode'] ?? null,
            status: ProductStatus::from($data['status'] ?? 'active'),
            isActive: (bool) ($data['is_active'] ?? true),
            tags: $tags,
            attributes: $attributes,
            createdAt: isset($data['created_at'])
                ? new DateTimeImmutable($data['created_at'])
                : new DateTimeImmutable(),
            updatedAt: isset($data['updated_at'])
                ? new DateTimeImmutable($data['updated_at'])
                : new DateTimeImmutable(),
            reservedQuantity: (int) ($data['reserved_quantity'] ?? 0),
        );
    }

    /**
     * Convert to plain array for persistence or API responses.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'               => $this->id,
            'tenant_id'        => $this->tenantId,
            'sku'              => (string) $this->sku,
            'name'             => $this->name,
            'description'      => $this->description,
            'category_id'      => $this->category?->id,
            'category'         => $this->category?->toArray(),
            'price'            => $this->price->amount,
            'cost_price'       => $this->costPrice->amount,
            'currency'         => $this->price->currency,
            'stock_quantity'   => $this->stockQuantity->value,
            'reserved_quantity'=> $this->reservedQuantity,
            'available_quantity' => $this->getAvailableQuantity(),
            'min_stock_level'  => $this->minStockLevel,
            'max_stock_level'  => $this->maxStockLevel,
            'unit'             => $this->unit,
            'barcode'          => $this->barcode,
            'status'           => $this->status->value,
            'is_active'        => $this->isActive,
            'tags'             => $this->tags,
            'attributes'       => $this->attributes,
            'is_low_stock'     => $this->isLowStock(),
            'is_out_of_stock'  => $this->isOutOfStock(),
            'created_at'       => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at'       => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }

    // ─── Business logic ─────────────────────────────────────────────────────

    /**
     * Whether the current stock is at or below the minimum stock level.
     */
    public function isLowStock(): bool
    {
        return $this->minStockLevel > 0
            && $this->stockQuantity->value <= $this->minStockLevel;
    }

    /**
     * Whether the product has no stock at all.
     */
    public function isOutOfStock(): bool
    {
        return $this->stockQuantity->isZero();
    }

    /**
     * Whether the product can fulfil the requested quantity from available stock.
     */
    public function canFulfill(int $qty): bool
    {
        return $this->getAvailableQuantity() >= $qty;
    }

    /**
     * Available quantity = stock - reserved.
     */
    public function getAvailableQuantity(): int
    {
        return max(0, $this->stockQuantity->value - $this->reservedQuantity);
    }

    /**
     * Reserve the given quantity and return a new Product instance
     * with the updated reserved count.
     *
     * @throws InvalidArgumentException When insufficient stock.
     */
    public function reserve(int $qty): self
    {
        if ($qty <= 0) {
            throw new InvalidArgumentException("Reservation quantity must be positive, got {$qty}.");
        }

        if (!$this->canFulfill($qty)) {
            throw new InvalidArgumentException(
                "Insufficient stock to reserve {$qty} units of product {$this->id}. " .
                "Available: {$this->getAvailableQuantity()}."
            );
        }

        $clone = clone $this;
        $clone->reservedQuantity = $this->reservedQuantity + $qty;
        $clone->updatedAt = new DateTimeImmutable();

        return $clone;
    }

    /**
     * Release previously-reserved quantity.
     *
     * @throws InvalidArgumentException When releasing more than reserved.
     */
    public function release(int $qty): self
    {
        if ($qty <= 0) {
            throw new InvalidArgumentException("Release quantity must be positive, got {$qty}.");
        }

        if ($qty > $this->reservedQuantity) {
            throw new InvalidArgumentException(
                "Cannot release {$qty} — only {$this->reservedQuantity} units are reserved."
            );
        }

        $clone = clone $this;
        $clone->reservedQuantity = $this->reservedQuantity - $qty;
        $clone->updatedAt = new DateTimeImmutable();

        return $clone;
    }

    /**
     * Gross margin as a percentage (requires cost > 0).
     */
    public function grossMarginPercent(): float
    {
        if ($this->costPrice->amount <= 0.0) {
            return 0.0;
        }

        return round(
            (($this->price->amount - $this->costPrice->amount) / $this->price->amount) * 100,
            2
        );
    }
}
