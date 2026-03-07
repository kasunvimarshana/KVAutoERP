<?php

namespace App\DTOs;

use App\Models\Order;

class OrderDTO
{
    public function __construct(
        public readonly string  $id,
        public readonly string  $tenantId,
        public readonly string  $userId,
        public readonly string  $orderNumber,
        public readonly string  $status,
        public readonly string  $subtotal,
        public readonly string  $tax,
        public readonly string  $discount,
        public readonly string  $total,
        public readonly string  $currency,
        public readonly ?array  $shippingAddress,
        public readonly ?array  $billingAddress,
        public readonly ?string $notes,
        public readonly ?array  $metadata,
        public readonly array   $items,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function fromModel(Order $order): self
    {
        return new self(
            id:              $order->id,
            tenantId:        $order->tenant_id,
            userId:          $order->user_id,
            orderNumber:     $order->order_number,
            status:          $order->status,
            subtotal:        (string) $order->subtotal,
            tax:             (string) $order->tax,
            discount:        (string) $order->discount,
            total:           (string) $order->total,
            currency:        $order->currency,
            shippingAddress: $order->shipping_address,
            billingAddress:  $order->billing_address,
            notes:           $order->notes,
            metadata:        $order->metadata,
            items:           $order->relationLoaded('items')
                ? $order->items->map(fn ($item) => [
                    'id'           => $item->id,
                    'product_id'   => $item->product_id,
                    'product_name' => $item->product_name,
                    'sku'          => $item->sku,
                    'quantity'     => $item->quantity,
                    'unit_price'   => (string) $item->unit_price,
                    'total_price'  => (string) $item->total_price,
                    'metadata'     => $item->metadata,
                ])->all()
                : [],
            createdAt: $order->created_at?->toIso8601String(),
            updatedAt: $order->updated_at?->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'id'               => $this->id,
            'tenant_id'        => $this->tenantId,
            'user_id'          => $this->userId,
            'order_number'     => $this->orderNumber,
            'status'           => $this->status,
            'subtotal'         => $this->subtotal,
            'tax'              => $this->tax,
            'discount'         => $this->discount,
            'total'            => $this->total,
            'currency'         => $this->currency,
            'shipping_address' => $this->shippingAddress,
            'billing_address'  => $this->billingAddress,
            'notes'            => $this->notes,
            'metadata'         => $this->metadata,
            'items'            => $this->items,
            'created_at'       => $this->createdAt,
            'updated_at'       => $this->updatedAt,
        ];
    }
}
