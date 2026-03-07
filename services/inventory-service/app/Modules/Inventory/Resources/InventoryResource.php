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
            'product_id'          => $this->product_id,
            'product_sku'         => $this->product_sku,
            'quantity'            => $this->quantity,
            'reserved_quantity'   => $this->reserved_quantity,
            'available_quantity'  => $this->available_quantity,
            'warehouse_location'  => $this->warehouse_location,
            'reorder_level'       => $this->reorder_level,
            'reorder_quantity'    => $this->reorder_quantity,
            'unit_cost'           => $this->unit_cost ? (float) $this->unit_cost : null,
            'needs_reorder'       => $this->needsReorder(),
            'notes'               => $this->notes,
            'created_at'          => $this->created_at?->toISOString(),
            'updated_at'          => $this->updated_at?->toISOString(),
        ];
    }
}
