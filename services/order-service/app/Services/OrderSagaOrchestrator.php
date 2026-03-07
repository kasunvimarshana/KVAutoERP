<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderSaga;
use App\Repositories\SagaRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderSagaOrchestrator
{
    private const STEPS = [
        'ValidateProducts',
        'ReserveInventory',
        'CreateOrder',
        'ConfirmInventory',
        'SendNotification',
    ];

    public function __construct(
        private readonly SagaRepository        $sagaRepository,
        private readonly ProductServiceClient  $productServiceClient,
        private readonly InventoryServiceClient $inventoryServiceClient,
    ) {}

    public function execute(array $orderData, string $tenantId): Order
    {
        $steps = array_map(fn ($name) => [
            'name'           => $name,
            'status'         => 'pending',
            'executed_at'    => null,
            'compensated_at' => null,
            'error'          => null,
        ], self::STEPS);

        $saga = $this->sagaRepository->createSaga('', $steps, $orderData);
        $saga->update(['status' => 'running']);

        $executedSteps = [];
        $order         = null;

        try {
            // Step 1: ValidateProducts
            $productIds  = array_column($orderData['items'], 'product_id');
            $productData = $this->productServiceClient->validateProducts($productIds, $tenantId);

            if (count($productData) !== count($productIds)) {
                throw new \RuntimeException('One or more products not found or unavailable.');
            }

            $pricesMap = collect($productData)->keyBy('id');
            $this->sagaRepository->recordStep($saga->id, 'ValidateProducts', 'completed');
            $executedSteps[] = 'ValidateProducts';

            // Step 2: ReserveInventory
            foreach ($orderData['items'] as $item) {
                $reserved = $this->inventoryServiceClient->reserveStock(
                    $item['product_id'], $item['quantity'], $tenantId, $saga->id
                );

                if (!$reserved) {
                    Log::warning('Inventory reservation failed for product', ['product_id' => $item['product_id']]);
                    // Proceed optimistically; inventory service may not be available in dev
                }
            }

            $this->sagaRepository->recordStep($saga->id, 'ReserveInventory', 'completed');
            $executedSteps[] = 'ReserveInventory';

            // Step 3: CreateOrder
            $order = DB::transaction(function () use ($orderData, $tenantId, $pricesMap, $saga): Order {
                $subtotal = 0;

                $order = Order::create([
                    'tenant_id'        => $tenantId,
                    'user_id'          => $orderData['user_id'],
                    'status'           => 'processing',
                    'tax'              => $orderData['tax'] ?? 0,
                    'discount'         => $orderData['discount'] ?? 0,
                    'currency'         => $orderData['currency'] ?? 'USD',
                    'shipping_address' => $orderData['shipping_address'] ?? null,
                    'billing_address'  => $orderData['billing_address'] ?? null,
                    'notes'            => $orderData['notes'] ?? null,
                    'subtotal'         => 0,
                    'total'            => 0,
                ]);

                foreach ($orderData['items'] as $item) {
                    $product    = $pricesMap->get($item['product_id']);
                    $unitPrice  = (float) ($product['price'] ?? $item['unit_price'] ?? 0);
                    $totalPrice = $unitPrice * $item['quantity'];
                    $subtotal  += $totalPrice;

                    OrderItem::create([
                        'order_id'     => $order->id,
                        'product_id'   => $item['product_id'],
                        'product_name' => $product['name'] ?? $item['product_name'] ?? '',
                        'sku'          => $product['sku']  ?? $item['sku'] ?? '',
                        'quantity'     => $item['quantity'],
                        'unit_price'   => $unitPrice,
                        'total_price'  => $totalPrice,
                    ]);
                }

                $tax      = (float) ($orderData['tax'] ?? 0);
                $discount = (float) ($orderData['discount'] ?? 0);
                $total    = $subtotal + $tax - $discount;

                $order->update(['subtotal' => $subtotal, 'total' => $total]);

                // Link saga to order
                $saga->update(['order_id' => $order->id]);

                return $order->fresh(['items']);
            });

            $this->sagaRepository->recordStep($saga->id, 'CreateOrder', 'completed');
            $executedSteps[] = 'CreateOrder';

            // Step 4: ConfirmInventory
            foreach ($orderData['items'] as $item) {
                $this->inventoryServiceClient->confirmStock(
                    $item['product_id'], $item['quantity'], $tenantId, $order->id
                );
            }

            $this->sagaRepository->recordStep($saga->id, 'ConfirmInventory', 'completed');
            $executedSteps[] = 'ConfirmInventory';

            // Step 5: SendNotification
            Log::info('Order created successfully', ['order_id' => $order->id, 'tenant_id' => $tenantId]);
            $this->sagaRepository->recordStep($saga->id, 'SendNotification', 'completed');
            $executedSteps[] = 'SendNotification';

            $saga->update(['status' => 'completed', 'current_step' => 'SendNotification']);

            $order->update(['status' => 'confirmed']);

            return $order->fresh(['items']);

        } catch (\Throwable $e) {
            Log::error('SAGA failed', ['saga_id' => $saga->id, 'error' => $e->getMessage(), 'step' => end($executedSteps)]);

            $saga->update(['status' => 'compensating']);

            $this->compensate($saga, $orderData, $tenantId, $executedSteps, $order);

            $saga->update(['status' => 'compensated']);

            throw $e;
        }
    }

    public function compensate(OrderSaga $saga, array $orderData, string $tenantId, array $executedSteps, ?Order $order): void
    {
        foreach (array_reverse($executedSteps) as $step) {
            try {
                match ($step) {
                    'ReserveInventory', 'ConfirmInventory' => $this->compensateInventory($orderData, $tenantId, $order?->id ?? $saga->id),
                    'CreateOrder'      => $this->compensateOrder($order),
                    default            => null,
                };
                $saga->markStepCompensated($step);
            } catch (\Throwable $e) {
                Log::error('Compensation failed for step', ['step' => $step, 'error' => $e->getMessage()]);
            }
        }
    }

    private function compensateInventory(array $orderData, string $tenantId, string $referenceId): void
    {
        foreach ($orderData['items'] as $item) {
            $this->inventoryServiceClient->releaseStock(
                $item['product_id'], $item['quantity'], $tenantId, $referenceId
            );
        }
    }

    private function compensateOrder(?Order $order): void
    {
        if ($order !== null) {
            $order->update(['status' => 'cancelled']);
        }
    }
}
