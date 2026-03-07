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
            'order_number'     => $this->order_number,
            'user_id'          => $this->user_id,
            'status'           => $this->status,
            'total_amount'     => (float) $this->total_amount,
            'currency'         => $this->currency,
            'shipping_address' => $this->shipping_address,
            'billing_address'  => $this->billing_address,
            'notes'            => $this->notes,
            'items'            => $this->items->map(fn ($item) => [
                'id'           => $item->id,
                'product_id'   => $item->product_id,
                'product_sku'  => $item->product_sku,
                'product_name' => $item->product_name,
                'quantity'     => $item->quantity,
                'unit_price'   => (float) $item->unit_price,
                'total_price'  => (float) $item->total_price,
            ]),
            'created_at'       => $this->created_at?->toISOString(),
            'updated_at'       => $this->updated_at?->toISOString(),
        ];
    }
}
