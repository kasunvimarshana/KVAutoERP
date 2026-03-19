<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_number' => $this->order_number,
            'customer_id' => $this->customer_id,
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'currency_code' => $this->currency_code,
            'items' => $this->whenLoaded('items'),
            'shipping_address' => $this->shipping_address,
            'billing_address' => $this->billing_address,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
