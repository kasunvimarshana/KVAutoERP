<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final class ProductDTO
{
    public function __construct(
        public readonly string|int $tenantId,
        public readonly string $name,
        public readonly string $code,
        public readonly ?int $categoryId,
        public readonly float $price,
        public readonly ?float $costPrice,
        public readonly ?float $comparePrice,
        public readonly ?string $sku,
        public readonly ?string $barcode,
        public readonly ?string $description,
        public readonly ?string $shortDescription,
        public readonly string $unit = 'pcs',
        public readonly ?float $weight = null,
        public readonly array $dimensions = [],
        public readonly array $images = [],
        public readonly array $attributes = [],
        public readonly array $tags = [],
        public readonly bool $isActive = true,
        public readonly bool $isFeatured = false,
        public readonly array $metadata = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            tenantId: $data['tenant_id'],
            name: $data['name'],
            code: $data['code'],
            categoryId: $data['category_id'] ?? null,
            price: (float) $data['price'],
            costPrice: isset($data['cost_price']) ? (float) $data['cost_price'] : null,
            comparePrice: isset($data['compare_price']) ? (float) $data['compare_price'] : null,
            sku: $data['sku'] ?? null,
            barcode: $data['barcode'] ?? null,
            description: $data['description'] ?? null,
            shortDescription: $data['short_description'] ?? null,
            unit: $data['unit'] ?? 'pcs',
            weight: isset($data['weight']) ? (float) $data['weight'] : null,
            dimensions: $data['dimensions'] ?? [],
            images: $data['images'] ?? [],
            attributes: $data['attributes'] ?? [],
            tags: $data['tags'] ?? [],
            isActive: (bool) ($data['is_active'] ?? true),
            isFeatured: (bool) ($data['is_featured'] ?? false),
            metadata: $data['metadata'] ?? [],
        );
    }
}
