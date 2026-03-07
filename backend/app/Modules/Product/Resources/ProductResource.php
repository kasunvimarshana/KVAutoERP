<?php

namespace App\Modules\Product\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'tenant_id'   => $this->tenant_id,
            'sku'         => $this->sku,
            'name'        => $this->name,
            'description' => $this->description,
            'category'    => $this->category,
            'brand'       => $this->brand,
            'unit'        => $this->unit,
            'price'       => (float) $this->price,
            'cost'        => (float) $this->cost,
            'is_active'   => $this->is_active,
            'attributes'  => $this->attributes,
            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),
        ];
    }
}
