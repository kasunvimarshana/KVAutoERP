<?php

namespace App\Application\Saga\Steps;

use App\Application\Saga\SagaState;
use App\Application\Saga\SagaStep;
use App\Domain\Order\Entities\Order;
use Illuminate\Support\Facades\Log;

class CreateOrderStep extends SagaStep
{
    public function getName(): string
    {
        return 'create_order';
    }

    public function execute(SagaState $state): SagaState
    {
        $payload = $state->getPayload();
        $context = $state->getContext();

        $subtotal = collect($payload['items'])->sum(function (array $item) {
            return ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 1);
        });

        $tax      = $payload['tax']      ?? round($subtotal * 0.1, 2);
        $discount = $payload['discount'] ?? 0;
        $total    = $subtotal + $tax - $discount;

        $order = Order::create([
            'tenant_id'        => $payload['tenant_id'],
            'order_number'     => $payload['order_number'] ?? Order::generateOrderNumber(),
            'customer_id'      => $payload['customer_id'],
            'customer_name'    => $payload['customer_name'],
            'customer_email'   => $payload['customer_email'],
            'items'            => $payload['items'],
            'subtotal'         => $subtotal,
            'tax'              => $tax,
            'discount'         => $discount,
            'total'            => $total,
            'status'           => Order::STATUS_CONFIRMED,
            'payment_status'   => $context['payment_status'] ?? Order::PAYMENT_STATUS_PAID,
            'payment_method'   => $payload['payment_method'],
            'payment_reference'=> $context['transaction_id'] ?? null,
            'shipping_address' => $payload['shipping_address'] ?? null,
            'billing_address'  => $payload['billing_address']  ?? null,
            'notes'            => $payload['notes']            ?? null,
            'saga_id'          => $state->getSagaId(),
            'metadata'         => [
                'payment_id'     => $context['payment_id']     ?? null,
                'reservations'   => $context['reservations']   ?? [],
                'created_via'    => 'saga',
            ],
        ]);

        Log::info("Saga [{$state->getSagaId()}]: Order created [{$order->id}] – [{$order->order_number}]");

        $state = $this->setContextValue($state, 'order_id', $order->id);
        $state = $this->setContextValue($state, 'order_number', $order->order_number);

        return $state;
    }

    public function compensate(SagaState $state): SagaState
    {
        $context = $state->getContext();
        $orderId = $context['order_id'] ?? null;

        if (!$orderId) {
            return $state;
        }

        try {
            $order = Order::find($orderId);
            if ($order) {
                $order->update(['status' => Order::STATUS_CANCELLED]);
                Log::info("Saga [{$state->getSagaId()}]: Order [{$orderId}] marked as cancelled");
            }
        } catch (\Throwable $e) {
            Log::error("Saga [{$state->getSagaId()}]: Failed to cancel order [{$orderId}]", [
                'error' => $e->getMessage(),
            ]);
        }

        return $state;
    }
}
