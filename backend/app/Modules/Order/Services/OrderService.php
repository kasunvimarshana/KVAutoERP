<?php

namespace App\Modules\Order\Services;

use App\Core\MessageBroker\MessageBrokerInterface;
use App\Core\Pagination\PaginationHelper;
use App\Core\Saga\SagaOrchestrator;
use App\Core\Service\BaseService;
use App\Core\Tenant\TenantManager;
use App\Models\Inventory;
use App\Models\Order;
use App\Modules\Order\Repositories\OrderRepository;
use App\Modules\Order\Saga\Steps\ConfirmOrderStep;
use App\Modules\Order\Saga\Steps\CreateOrderStep;
use App\Modules\Order\Saga\Steps\ReserveInventoryStep;
use App\Modules\Order\Saga\Steps\ValidateOrderStep;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class OrderService extends BaseService
{
    public function __construct(
        OrderRepository $repository,
        private MessageBrokerInterface $broker,
        private TenantManager $tenantManager
    ) {
        parent::__construct($repository);
    }

    public function index(array $params = []): array
    {
        $query = $this->repository->query()->with(['items.product', 'user']);
        $this->applyFilters($query, $params);

        return PaginationHelper::paginate($query, $params);
    }

    /**
     * Place an order via the Saga pattern:
     *   Validate → Reserve Inventory → Create Order → Confirm Order
     *
     * On any failure all executed steps are compensated (rolled back).
     */
    public function placeOrder(array $data): array
    {
        $sagaId = Str::uuid()->toString();

        $saga = (new SagaOrchestrator())
            ->addStep(new ValidateOrderStep())
            ->addStep(new ReserveInventoryStep())
            ->addStep(new CreateOrderStep())
            ->addStep(new ConfirmOrderStep());

        $context = array_merge($data, [
            'saga_id'   => $sagaId,
            'tenant_id' => $this->tenantManager->getTenantId(),
        ]);

        $result = $saga->execute($context);

        $order = Order::findOrFail($result['order_id']);

        $this->broker->publish('order.placed', [
            'order_id'     => $order->id,
            'order_number' => $order->order_number,
            'tenant_id'    => $order->tenant_id,
            'total_amount' => $order->total_amount,
            'saga_id'      => $sagaId,
        ]);

        return [
            'order'   => $order->load('items.product', 'user'),
            'saga_id' => $sagaId,
        ];
    }

    public function cancelOrder(int $orderId): Order
    {
        $order = $this->repository->findByIdOrFail($orderId);

        if (!in_array($order->status, ['pending', 'confirmed'], true)) {
            throw new \InvalidArgumentException(
                "Cannot cancel order in status '{$order->status}'."
            );
        }

        $order->update(['status' => 'cancelled']);

        // Restore stock for each item
        foreach ($order->items as $item) {
            $inventory = Inventory::where('product_id', $item->product_id)->first();
            $inventory?->increment('quantity', $item->quantity);
        }

        $this->broker->publish('order.cancelled', [
            'order_id'     => $order->id,
            'order_number' => $order->order_number,
        ]);

        return $order;
    }

    protected function applyFilters(Builder $query, array $params): void
    {
        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (!empty($params['user_id'])) {
            $query->where('user_id', $params['user_id']);
        }
    }
}
