<?php

namespace App\Modules\Product\DTOs;

class ProductDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?string $sku = null,
        public readonly ?float $price = null,
        public readonly ?string $category = null,
        public readonly ?int $tenantId = null,
        public readonly ?array $attributes = null,
        public readonly ?bool $isActive = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            sku: $data['sku'] ?? null,
            price: $data['price'] ?? null,
            category: $data['category'] ?? null,
            tenantId: $data['tenant_id'] ?? null,
            attributes: $data['attributes'] ?? null,
            isActive: $data['is_active'] ?? null,
        );
    }
}
