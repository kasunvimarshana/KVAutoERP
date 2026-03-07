<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Service B: Inventory Resource
 * Formats an inventory record with its related product data.
 */
class InventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'product_id'         => $this->product_id,
            'product_name'       => $this->product_name,
            'quantity'           => $this->quantity,
            'warehouse_location' => $this->warehouse_location,
            'status'             => $this->status,
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
            // Cross-service relationship: product details from Service A
            'product'            => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
