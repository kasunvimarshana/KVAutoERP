<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'order_number'      => $this->order_number,
            'tenant_id'         => $this->tenant_id,

            'customer' => [
                'id'    => $this->customer_id,
                'name'  => $this->customer_name,
                'email' => $this->customer_email,
            ],

            'items'             => $this->items,

            'pricing' => [
                'subtotal'  => (float) $this->subtotal,
                'tax'       => (float) $this->tax,
                'discount'  => (float) $this->discount,
                'total'     => (float) $this->total,
                'formatted' => $this->formatted_total,
            ],

            'status'          => $this->status,
            'payment_status'  => $this->payment_status,
            'payment_method'  => $this->payment_method,
            'payment_reference'=> $this->payment_reference,

            'shipping_address' => $this->shipping_address,
            'billing_address'  => $this->billing_address,

            'notes'     => $this->notes,
            'saga_id'   => $this->saga_id,
            'metadata'  => $this->metadata,

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
