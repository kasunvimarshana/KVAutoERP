<?php

namespace App\Modules\Order\Saga\Steps;

use App\Core\Saga\SagaStep;
use App\Models\Inventory;
use App\Models\Order;

class ConfirmOrderStep extends SagaStep
{
    public function getName(): string
    {
        return 'confirm_order';
    }

    public function execute(array $context): array
    {
        $order = Order::findOrFail($context['order_id']);
        $order->update(['status' => 'confirmed']);

        // Deduct actual stock and release the reservation
        foreach ($context['reservations'] as $reservation) {
            $inventory = Inventory::find($reservation['inventory_id']);

            if ($inventory) {
                $inventory->decrement('quantity', $reservation['quantity']);
                $inventory->decrement('reserved_quantity', $reservation['quantity']);
            }
        }

        return ['order_status' => 'confirmed'];
    }

    public function compensate(array $context): void
    {
        if (isset($context['order_id'])) {
            Order::find($context['order_id'])?->update(['status' => 'failed']);
        }
    }
}
