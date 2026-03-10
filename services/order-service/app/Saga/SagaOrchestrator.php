<?php
namespace App\Saga;
use App\Models\Order;
use App\Infrastructure\InventoryServiceClient;
use Illuminate\Support\Facades\Log;
class SagaOrchestrator {
    private array $reservedItems = [];
    public function __construct(private readonly InventoryServiceClient $inventoryClient) {}
    public function executeCreateOrder(Order $order, string $tenantId): array {
        Log::info('[Saga] Starting CreateOrder saga', ['order_id'=>$order->id]);
        $this->reservedItems = [];
        foreach ($order->items as $item) {
            $result = $this->inventoryClient->reserveStock($tenantId, $item->product_id, $item->quantity, $order->id);
            if (!$result['success']) {
                $this->compensate($order, $tenantId);
                return ['success'=>false,'step'=>'reserve_stock','message'=>"Stock reservation failed for product {$item->product_code}: {$result['message']}"];
            }
            $this->reservedItems[] = ['product_id'=>$item->product_id,'quantity'=>$item->quantity];
        }
        $paymentResult = $this->processPayment($order);
        if (!$paymentResult['success']) {
            $this->compensate($order, $tenantId);
            return ['success'=>false,'step'=>'payment','message'=>"Payment failed: {$paymentResult['message']}"];
        }
        foreach ($order->items as $item) {
            $this->inventoryClient->confirmStock($tenantId, $item->product_id, $item->quantity, $order->id);
        }
        return ['success'=>true,'message'=>'Order saga completed successfully.'];
    }
    public function executeCancelOrder(Order $order, string $tenantId): array {
        $errors = [];
        foreach ($order->items as $item) {
            $result = $this->inventoryClient->releaseStock($tenantId, $item->product_id, $item->quantity, $order->id);
            if (!$result['success']) $errors[] = "Failed to release stock for product {$item->product_code}";
        }
        return empty($errors) ? ['success'=>true,'message'=>'Order cancelled and stock released.'] : ['success'=>false,'message'=>implode('; ',$errors)];
    }
    private function compensate(Order $order, string $tenantId): void {
        foreach ($this->reservedItems as $item) { $this->inventoryClient->releaseStock($tenantId, $item['product_id'], $item['quantity'], $order->id); }
        $this->reservedItems = [];
    }
    private function processPayment(Order $order): array {
        if ($order->total <= 0) return ['success'=>false,'message'=>'Invalid order total.'];
        return ['success'=>true,'transaction_id'=>'PAY-'.strtoupper(substr($order->id,0,8))];
    }
}
