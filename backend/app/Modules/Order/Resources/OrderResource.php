<?php

namespace App\Modules\Order\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'tenant_id'        => $this->tenant_id,
            'user_id'          => $this->user_id,
            'order_number'     => $this->order_number,
            'status'           => $this->status,
            'subtotal'         => (float) $this->subtotal,
            'tax'              => (float) $this->tax,
            'discount'         => (float) $this->discount,
            'total'            => (float) $this->total,
            'currency'         => $this->currency,
            'notes'            => $this->notes,
            'shipping_address' => $this->shipping_address,
            'billing_address'  => $this->billing_address,
            'metadata'         => $this->metadata,
            'items'            => $this->whenLoaded('items', function () {
                return $this->items->map(fn ($item) => [
                    'id'           => $item->id,
                    'product_id'   => $item->product_id,
                    'product_sku'  => $item->product_sku,
                    'product_name' => $item->product_name,
                    'quantity'     => $item->quantity,
                    'unit_price'   => (float) $item->unit_price,
                    'discount'     => (float) $item->discount,
                    'total'        => (float) $item->total,
                ]);
            }),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
        ];
    }
}
