<?php

namespace App\Modules\Product\DTOs;

class ProductDTO
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $sku,
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly ?string $category = null,
        public readonly ?string $brand = null,
        public readonly string $unit = 'piece',
        public readonly float $price = 0.00,
        public readonly float $cost = 0.00,
        public readonly bool $isActive = true,
        public readonly ?array $attributes = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            tenantId:    $data['tenant_id'],
            sku:         $data['sku'],
            name:        $data['name'],
            description: $data['description'] ?? null,
            category:    $data['category'] ?? null,
            brand:       $data['brand'] ?? null,
            unit:        $data['unit'] ?? 'piece',
            price:       (float) ($data['price'] ?? 0.00),
            cost:        (float) ($data['cost'] ?? 0.00),
            isActive:    $data['is_active'] ?? true,
            attributes:  $data['attributes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'tenant_id'   => $this->tenantId,
            'sku'         => $this->sku,
            'name'        => $this->name,
            'description' => $this->description,
            'category'    => $this->category,
            'brand'       => $this->brand,
            'unit'        => $this->unit,
            'price'       => $this->price,
            'cost'        => $this->cost,
            'is_active'   => $this->isActive,
            'attributes'  => $this->attributes,
        ];
    }
}
