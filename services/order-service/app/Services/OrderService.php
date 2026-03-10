<?php
namespace App\Services;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Saga\SagaOrchestrator;
use App\Infrastructure\ProductServiceClient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
class OrderService {
    public function __construct(private readonly OrderRepositoryInterface $orderRepository, private readonly SagaOrchestrator $sagaOrchestrator, private readonly ProductServiceClient $productClient) {}
    public function list(string $tenantId, array $params = []): LengthAwarePaginator|Collection {
        $filters = ['tenant_id'=>$tenantId];
        if (!empty($params['status'])) $filters['status'] = $params['status'];
        return $this->orderRepository->all($filters, $params);
    }
    public function create(string $tenantId, string $userId, array $data): array {
        $productIds = array_column($data['items'], 'product_id');
        $products = collect($this->productClient->getProductsByIds($tenantId, $productIds))->keyBy('id');
        $items = []; $subtotal = 0;
        foreach ($data['items'] as $itemData) {
            $product = $products->get($itemData['product_id']);
            if (!$product) throw new \RuntimeException("Product {$itemData['product_id']} not found.", 422);
            $unitPrice = $itemData['unit_price'] ?? $product['price'];
            $total = $unitPrice * $itemData['quantity']; $subtotal += $total;
            $items[] = ['product_id'=>$product['id'],'product_code'=>$product['code'],'product_name'=>$product['name'],'quantity'=>$itemData['quantity'],'unit_price'=>$unitPrice,'discount'=>$itemData['discount']??0,'total'=>$total];
        }
        $total = $subtotal + ($data['tax']??0) - ($data['discount']??0);
        $order = $this->orderRepository->createWithItems(['tenant_id'=>$tenantId,'user_id'=>$userId,'order_number'=>$this->generateOrderNumber(),'status'=>'pending','saga_status'=>'started','subtotal'=>$subtotal,'tax'=>$data['tax']??0,'discount'=>$data['discount']??0,'total'=>$total,'shipping_address'=>$data['shipping_address']??null,'notes'=>$data['notes']??null], $items);
        $sagaResult = $this->sagaOrchestrator->executeCreateOrder($order, $tenantId);
        if (!$sagaResult['success']) {
            $this->orderRepository->update($order->id, ['status'=>'failed','saga_status'=>'compensated','metadata'=>['failure_reason'=>$sagaResult['message'],'failed_step'=>$sagaResult['step']??'unknown']]);
            return ['success'=>false,'order'=>$order->fresh('items'),'message'=>$sagaResult['message']];
        }
        $order = $this->orderRepository->update($order->id, ['status'=>'confirmed','saga_status'=>'completed','confirmed_at'=>now()]);
        return ['success'=>true,'order'=>$order->load('items'),'message'=>'Order created successfully.'];
    }
    public function get(string $id, string $tenantId): Order {
        $order = $this->orderRepository->findById($id);
        if (!$order || $order->tenant_id !== $tenantId) throw new \RuntimeException('Order not found.', 404);
        return $order->load('items');
    }
    public function update(string $id, string $tenantId, array $data): Order {
        $order = $this->get($id, $tenantId);
        if ($order->isConfirmed() || $order->isCancelled()) throw new \RuntimeException('Cannot update a confirmed or cancelled order.', 422);
        return $this->orderRepository->update($order->id, $data)->load('items');
    }
    public function delete(string $id, string $tenantId): void {
        $order = $this->get($id, $tenantId);
        if ($order->isConfirmed()) throw new \RuntimeException('Cannot delete a confirmed order. Cancel it first.', 422);
        $this->orderRepository->delete($order->id);
    }
    public function cancel(string $id, string $tenantId): array {
        $order = $this->get($id, $tenantId);
        if ($order->isCancelled()) throw new \RuntimeException('Order is already cancelled.', 422);
        $sagaResult = $this->sagaOrchestrator->executeCancelOrder($order, $tenantId);
        $this->orderRepository->update($order->id, ['status'=>'cancelled','saga_status'=>'compensated','cancelled_at'=>now()]);
        return ['success'=>$sagaResult['success'],'order'=>$order->fresh('items'),'message'=>$sagaResult['message']];
    }
    public function confirm(string $id, string $tenantId): Order {
        $order = $this->get($id, $tenantId);
        if (!$order->isPending()) throw new \RuntimeException('Only pending orders can be confirmed.', 422);
        return $this->orderRepository->update($order->id, ['status'=>'confirmed','confirmed_at'=>now()])->load('items');
    }
    private function generateOrderNumber(): string { return 'ORD-'.strtoupper(Str::random(8)).'-'.date('Ymd'); }
}
