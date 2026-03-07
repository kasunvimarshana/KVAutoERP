<?php

namespace App\Modules\Inventory\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'tenant_id'           => $this->tenant_id,
            'product_id'          => $this->product_id,
            'warehouse_location'  => $this->warehouse_location,
            'quantity'            => $this->quantity,
            'reserved_quantity'   => $this->reserved_quantity,
            'available_quantity'  => $this->available_quantity,
            'minimum_quantity'    => $this->minimum_quantity,
            'maximum_quantity'    => $this->maximum_quantity,
            'status'              => $this->status,
            'last_restocked_at'   => $this->last_restocked_at?->toIso8601String(),
            'metadata'            => $this->metadata,
            'product'             => $this->whenLoaded('product'),
            'created_at'          => $this->created_at?->toIso8601String(),
            'updated_at'          => $this->updated_at?->toIso8601String(),
        ];
    }
}
