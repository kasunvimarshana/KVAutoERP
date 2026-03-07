<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                 => $this->id,
            'tenant_id'          => $this->tenant_id,
            'sku'                => $this->sku,
            'name'               => $this->name,
            'description'        => $this->description,
            'quantity'           => $this->quantity,
            'reserved_quantity'  => $this->reserved_quantity,
            'available_quantity' => $this->getAvailableQuantity(),
            'unit_cost'          => (float) $this->unit_cost,
            'unit_price'         => (float) $this->unit_price,
            'category'           => $this->category,
            'location'           => $this->location,
            'min_stock_level'    => $this->min_stock_level,
            'max_stock_level'    => $this->max_stock_level,
            'is_low_stock'       => $this->isLowStock(),
            'is_out_of_stock'    => $this->isOutOfStock(),
            'status'             => $this->status,
            'metadata'           => $this->metadata ?? [],
            'created_at'         => $this->created_at?->toISOString(),
            'updated_at'         => $this->updated_at?->toISOString(),
        ];
    }
}
