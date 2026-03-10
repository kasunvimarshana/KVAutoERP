<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class InventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'tenant_id'          => $this->tenant_id,
            'product_id'         => $this->product_id,
            'product_code'       => $this->product_code,
            'product_name'       => $this->product_name,
            'category_id'        => $this->category_id,
            'quantity_on_hand'   => $this->quantity_on_hand,
            'quantity_reserved'  => $this->quantity_reserved,
            'quantity_available' => $this->quantity_available,
            'reorder_point'      => $this->reorder_point,
            'reorder_quantity'   => $this->reorder_quantity,
            'location'           => $this->location,
            'status'             => $this->status,
            'is_low_stock'       => $this->isLowStock(),
            'created_at'         => $this->created_at->toIso8601String(),
            'updated_at'         => $this->updated_at->toIso8601String(),
        ];
    }
}
