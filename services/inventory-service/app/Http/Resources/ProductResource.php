<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full product representation including category, stock info, and pricing.
 */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->resource['id'] ?? $this['id'],
            'tenant_id'          => $this->resource['tenant_id'] ?? $this['tenant_id'],
            'sku'                => $this->resource['sku'] ?? $this['sku'],
            'name'               => $this->resource['name'] ?? $this['name'],
            'description'        => $this->resource['description'] ?? $this['description'] ?? '',
            'category'           => $this->whenLoaded('category', fn () => new CategoryResource($this->category))
                ?? (isset($this->resource['category']) ? new CategoryResource($this->resource['category']) : null),
            'category_id'        => $this->resource['category_id'] ?? $this['category_id'] ?? null,
            'price'              => [
                'amount'   => (float) ($this->resource['price'] ?? $this['price'] ?? 0),
                'currency' => $this->resource['currency'] ?? $this['currency'] ?? 'USD',
                'formatted'=> number_format((float) ($this->resource['price'] ?? $this['price'] ?? 0), 2) . ' ' . ($this->resource['currency'] ?? $this['currency'] ?? 'USD'),
            ],
            'cost_price'         => [
                'amount'   => (float) ($this->resource['cost_price'] ?? $this['cost_price'] ?? 0),
                'currency' => $this->resource['currency'] ?? $this['currency'] ?? 'USD',
            ],
            'stock'              => [
                'quantity'          => (int) ($this->resource['stock_quantity'] ?? $this['stock_quantity'] ?? 0),
                'reserved'          => (int) ($this->resource['reserved_quantity'] ?? $this['reserved_quantity'] ?? 0),
                'available'         => (int) ($this->resource['available_quantity'] ?? $this['available_quantity'] ?? 0),
                'min_level'         => (int) ($this->resource['min_stock_level'] ?? $this['min_stock_level'] ?? 0),
                'max_level'         => (int) ($this->resource['max_stock_level'] ?? $this['max_stock_level'] ?? 0),
                'is_low_stock'      => (bool) ($this->resource['is_low_stock'] ?? $this['is_low_stock'] ?? false),
                'is_out_of_stock'   => (bool) ($this->resource['is_out_of_stock'] ?? $this['is_out_of_stock'] ?? false),
            ],
            'unit'               => $this->resource['unit'] ?? $this['unit'] ?? 'unit',
            'barcode'            => $this->resource['barcode'] ?? $this['barcode'] ?? null,
            'status'             => $this->resource['status'] ?? $this['status'] ?? 'active',
            'is_active'          => (bool) ($this->resource['is_active'] ?? $this['is_active'] ?? true),
            'tags'               => $this->resource['tags'] ?? $this['tags'] ?? [],
            'attributes'         => $this->resource['attributes'] ?? $this['attributes'] ?? [],
            'created_at'         => $this->resource['created_at'] ?? $this['created_at'] ?? null,
            'updated_at'         => $this->resource['updated_at'] ?? $this['updated_at'] ?? null,
        ];
    }
}
