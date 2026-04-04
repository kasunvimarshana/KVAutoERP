<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'tenant_id'        => $this->tenantId ?? $this->tenant_id ?? null,
            'product_id'       => $this->productId ?? $this->product_id ?? null,
            'name'             => $this->name,
            'sku'              => $this->sku,
            'barcode'          => $this->barcode,
            'attributes'       => $this->attributes,
            'price'            => $this->price,
            'cost'             => $this->cost,
            'weight'           => $this->weight,
            'is_active'        => $this->isActive ?? $this->is_active ?? true,
            'stock_management' => $this->stockManagement ?? $this->stock_management ?? true,
            'created_by'       => $this->createdBy ?? $this->created_by ?? null,
            'updated_by'       => $this->updatedBy ?? $this->updated_by ?? null,
            'created_at'       => isset($this->created_at) ? (string) $this->created_at : null,
            'updated_at'       => isset($this->updated_at) ? (string) $this->updated_at : null,
        ];
    }
}
