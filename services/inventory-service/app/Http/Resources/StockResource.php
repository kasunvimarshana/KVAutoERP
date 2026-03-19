<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'warehouse' => $this->whenLoaded('warehouse', function() {
                return [
                    'id' => $this->warehouse->id,
                    'name' => $this->warehouse->name,
                ];
            }),
            'quantity' => $this->quantity,
            'reserved_quantity' => $this->reserved_quantity,
            'lot' => $this->whenLoaded('lot', function() {
                return [
                    'id' => $this->lot->id,
                    'lot_number' => $this->lot->lot_number,
                    'expiry_date' => $this->lot->expiry_date,
                ];
            }),
            'serial' => $this->whenLoaded('serial', function() {
                return [
                    'id' => $this->serial->id,
                    'serial_number' => $this->serial->serial_number,
                ];
            }),
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
