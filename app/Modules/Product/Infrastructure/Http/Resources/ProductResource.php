<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'tenant_id'         => $this->tenantId ?? $this->tenant_id ?? null,
            'name'              => $this->name,
            'sku'               => $this->sku,
            'barcode'           => $this->barcode,
            'type'              => $this->type,
            'status'            => $this->status,
            'category_id'       => $this->categoryId ?? $this->category_id ?? null,
            'description'       => $this->description,
            'short_description' => $this->shortDescription ?? $this->short_description ?? null,
            'weight'            => $this->weight,
            'dimensions'        => $this->dimensions,
            'images'            => $this->images,
            'tags'              => $this->tags,
            'is_taxable'        => $this->isTaxable ?? $this->is_taxable ?? true,
            'tax_class'         => $this->taxClass ?? $this->tax_class ?? null,
            'has_serial'        => $this->hasSerial ?? $this->has_serial ?? false,
            'has_batch'         => $this->hasBatch ?? $this->has_batch ?? false,
            'has_lot'           => $this->hasLot ?? $this->has_lot ?? false,
            'is_serialized'     => $this->isSerialized ?? $this->is_serialized ?? false,
            'created_by'        => $this->createdBy ?? $this->created_by ?? null,
            'updated_by'        => $this->updatedBy ?? $this->updated_by ?? null,
            'created_at'        => isset($this->created_at) ? (string) $this->created_at : null,
            'updated_at'        => isset($this->updated_at) ? (string) $this->updated_at : null,
        ];
    }
}
