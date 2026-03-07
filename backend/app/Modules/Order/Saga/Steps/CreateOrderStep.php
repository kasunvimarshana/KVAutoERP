<?php

namespace App\Modules\Order\Saga\Steps;

use App\Core\Saga\SagaStep;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Str;

class CreateOrderStep extends SagaStep
{
    protected ?int $orderId = null;

    public function getName(): string
    {
        return 'create_order';
    }

    public function execute(array $context): array
    {
        $order = Order::create([
            'tenant_id'    => $context['tenant_id'],
            'user_id'      => $context['user_id'],
            'order_number' => 'ORD-'.strtoupper(Str::random(10)),
            'status'       => 'pending',
            'total_amount' => $context['total_amount'],
            'currency'     => $context['currency'] ?? 'USD',
            'notes'        => $context['notes']    ?? null,
            'saga_id'      => $context['saga_id'],
        ]);

        foreach ($context['validated_items'] as $item) {
            OrderItem::create([
                'order_id'    => $order->id,
                'product_id'  => $item['product_id'],
                'quantity'    => $item['quantity'],
                'unit_price'  => $item['unit_price'],
                'total_price' => $item['total_price'],
            ]);
        }

        $this->orderId = $order->id;

        return [
            'order_id'     => $order->id,
            'order_number' => $order->order_number,
        ];
    }

    public function compensate(array $context): void
    {
        if ($this->orderId) {
            Order::find($this->orderId)?->forceDelete();
        }
    }
}
