<?php

namespace App\Modules\Order\Controllers;

use App\Modules\Order\DTOs\OrderDTO;
use App\Modules\Order\Requests\CreateOrderRequest;
use App\Modules\Order\Requests\UpdateOrderRequest;
use App\Modules\Order\Resources\OrderResource;
use App\Modules\Order\Services\OrderSagaService;
use App\Modules\Order\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private OrderSagaService $orderSagaService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $tenantId = app('tenant_id');
        $perPage  = (int) $request->query('per_page', 15);
        $filters  = $request->only(['status', 'user_id', 'from_date', 'to_date']);

        return OrderResource::collection(
            $this->orderService->list($tenantId, $perPage, $filters)
        );
    }

    public function show(string $id): OrderResource
    {
        $tenantId = app('tenant_id');
        return new OrderResource($this->orderService->findById($id, $tenantId));
    }

    public function store(CreateOrderRequest $request): JsonResponse
    {
        $tenantId = app('tenant_id');
        $authUser = $request->get('auth_user');
        $data     = $request->validated();

        $data['tenant_id'] = $tenantId;
        $data['user_id']   = $data['user_id'] ?? ($authUser->sub ?? '');

        $order = $this->orderService->create(OrderDTO::fromRequest($data));

        // Kick off saga processing (synchronous; use a queue job in production)
        $this->orderSagaService->processOrder($order);

        // Re-fetch the order to get updated status after saga processing
        try {
            $order = $this->orderService->findById($order->id, $tenantId);
        } catch (\Exception) {
            // Order may have been returned from mock; use as-is
        }

        return (new OrderResource($order))->response()->setStatusCode(201);
    }

    public function update(UpdateOrderRequest $request, string $id): OrderResource
    {
        $tenantId = app('tenant_id');
        $order    = $this->orderService->updateStatus($id, $tenantId, $request->validated()['status']);

        return new OrderResource($order);
    }

    public function cancel(string $id): OrderResource
    {
        $tenantId = app('tenant_id');
        return new OrderResource($this->orderService->cancel($id, $tenantId));
    }

    public function complete(string $id): OrderResource
    {
        $tenantId = app('tenant_id');
        return new OrderResource($this->orderService->complete($id, $tenantId));
    }

    public function destroy(string $id): JsonResponse
    {
        $tenantId = app('tenant_id');
        $this->orderService->delete($id, $tenantId);

        return response()->json(['message' => 'Order deleted successfully']);
    }
}
