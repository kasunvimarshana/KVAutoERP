<?php

namespace App\Http\Controllers;

use App\Application\Services\OrderService;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    /**
     * GET /api/v1/orders
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $tenantId = $request->header('X-Tenant-ID');
        $filters  = $request->only(['status', 'customer_id', 'from_date', 'to_date']);
        $perPage  = min((int) $request->query('per_page', 15), 100);

        $orders = $this->orderService->listOrders($tenantId, $perPage, $filters);

        return OrderResource::collection($orders);
    }

    /**
     * GET /api/v1/orders/{id}
     */
    public function show(Request $request, int $id): OrderResource
    {
        $order = $this->orderService->getOrder($id, $request->header('X-Tenant-ID'));

        return new OrderResource($order);
    }

    /**
     * POST /api/v1/orders
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        $payload = array_merge($request->validated(), [
            'tenant_id'  => $request->header('X-Tenant-ID'),
            'auth_token' => $request->bearerToken(),
        ]);

        $result = $this->orderService->createOrder($payload);

        return response()->json([
            'message'  => 'Order created successfully',
            'data'     => new OrderResource($result['order']),
            'saga_id'  => $result['saga_id'],
        ], 201);
    }

    /**
     * POST /api/v1/orders/{id}/cancel
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->cancelOrder(
            $id,
            $request->header('X-Tenant-ID'),
            $request->input('reason', '')
        );

        return response()->json([
            'message' => 'Order cancelled successfully',
            'data'    => new OrderResource($order),
        ]);
    }

    /**
     * GET /api/v1/orders/{id}/saga-status
     */
    public function sagaStatus(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->getOrder($id, $request->header('X-Tenant-ID'));

        if (!$order->saga_id) {
            return response()->json(['message' => 'No saga associated with this order'], 404);
        }

        $status = $this->orderService->getSagaStatus(
            $order->saga_id,
            $request->header('X-Tenant-ID')
        );

        return response()->json(['data' => $status]);
    }
}
