<?php

namespace App\DTOs;

use App\Models\Product;

class ProductDTO
{
    public function __construct(
        public readonly string  $id,
        public readonly string  $tenantId,
        public readonly string  $name,
        public readonly string  $sku,
        public readonly ?string $description,
        public readonly ?string $category,
        public readonly string  $price,
        public readonly string  $cost,
        public readonly int     $stockQuantity,
        public readonly int     $minStockLevel,
        public readonly ?string $unit,
        public readonly string  $status,
        public readonly ?array  $metadata,
        public readonly bool    $isLowStock,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function fromModel(Product $product): self
    {
        return new self(
            id:            $product->id,
            tenantId:      $product->tenant_id,
            name:          $product->name,
            sku:           $product->sku,
            description:   $product->description,
            category:      $product->category,
            price:         (string) $product->price,
            cost:          (string) $product->cost,
            stockQuantity: $product->stock_quantity,
            minStockLevel: $product->min_stock_level,
            unit:          $product->unit,
            status:        $product->status,
            metadata:      $product->metadata,
            isLowStock:    $product->isLowStock(),
            createdAt:     $product->created_at?->toIso8601String(),
            updatedAt:     $product->updated_at?->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'id'               => $this->id,
            'tenant_id'        => $this->tenantId,
            'name'             => $this->name,
            'sku'              => $this->sku,
            'description'      => $this->description,
            'category'         => $this->category,
            'price'            => $this->price,
            'cost'             => $this->cost,
            'stock_quantity'   => $this->stockQuantity,
            'min_stock_level'  => $this->minStockLevel,
            'unit'             => $this->unit,
            'status'           => $this->status,
            'metadata'         => $this->metadata,
            'is_low_stock'     => $this->isLowStock,
            'created_at'       => $this->createdAt,
            'updated_at'       => $this->updatedAt,
        ];
    }
}
