<?php

namespace App\Modules\Product\DTOs;

class ProductDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $sku,
        public readonly string $description,
        public readonly float $price,
        public readonly string $category,
        public readonly string $status = 'active',
        public readonly ?float $weight = null,
        public readonly ?array $dimensions = null,
        public readonly ?array $metadata = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name:        $data['name'],
            sku:         $data['sku'],
            description: $data['description'],
            price:       (float) $data['price'],
            category:    $data['category'],
            status:      $data['status'] ?? 'active',
            weight:      isset($data['weight']) ? (float) $data['weight'] : null,
            dimensions:  $data['dimensions'] ?? null,
            metadata:    $data['metadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name'        => $this->name,
            'sku'         => $this->sku,
            'description' => $this->description,
            'price'       => $this->price,
            'category'    => $this->category,
            'status'      => $this->status,
            'weight'      => $this->weight,
            'dimensions'  => $this->dimensions,
            'metadata'    => $this->metadata,
        ], fn ($value) => $value !== null);
    }
}
