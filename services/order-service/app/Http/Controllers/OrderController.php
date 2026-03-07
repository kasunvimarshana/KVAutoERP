<?php

namespace App\Http\Controllers;

use App\DTOs\OrderDTO;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId  = $request->attributes->get('tenant_id');
        $perPage   = (int) $request->query('per_page', 15);
        $page      = (int) $request->query('page', 1);
        $filters   = array_filter(['status' => $request->query('status'), 'user_id' => $request->query('user_id')]);
        $paginator = $this->orderService->listOrders($tenantId, $filters, $perPage, $page);

        return response()->json([
            'success' => true,
            'data'    => collect($paginator->items())->map(fn ($o) => OrderDTO::fromModel($o)->toArray()),
            'message' => 'Orders retrieved successfully.',
            'meta'    => ['current_page' => $paginator->currentPage(), 'per_page' => $paginator->perPage(), 'total' => $paginator->total(), 'last_page' => $paginator->lastPage()],
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $dto = $this->orderService->getOrder($request->attributes->get('tenant_id'), $id);

        if ($dto === null) {
            return response()->json(['success' => false, 'data' => null, 'message' => 'Order not found.', 'meta' => []], 404);
        }

        return response()->json(['success' => true, 'data' => $dto->toArray(), 'message' => 'Order retrieved.', 'meta' => []]);
    }

    public function store(CreateOrderRequest $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');
        $userId   = $request->user()?->id ?? $request->input('user_id');

        try {
            $dto = $this->orderService->createOrder($tenantId, $userId, $request->validated());
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'data' => null, 'message' => $e->getMessage(), 'meta' => []], 422);
        }

        return response()->json(['success' => true, 'data' => $dto->toArray(), 'message' => 'Order created successfully.', 'meta' => []], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate(['shipping_address' => ['sometimes', 'array'], 'billing_address' => ['sometimes', 'array'], 'notes' => ['sometimes', 'nullable', 'string']]);
        $dto       = $this->orderService->updateOrder($request->attributes->get('tenant_id'), $id, $validated);

        if ($dto === null) {
            return response()->json(['success' => false, 'data' => null, 'message' => 'Order not found.', 'meta' => []], 404);
        }

        return response()->json(['success' => true, 'data' => $dto->toArray(), 'message' => 'Order updated.', 'meta' => []]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $deleted = $this->orderService->deleteOrder($request->attributes->get('tenant_id'), $id);

        return response()->json(['success' => $deleted, 'data' => null, 'message' => $deleted ? 'Order deleted.' : 'Order not found.', 'meta' => []], $deleted ? 200 : 404);
    }

    public function cancel(Request $request, string $id): JsonResponse
    {
        try {
            $dto = $this->orderService->cancelOrder($request->attributes->get('tenant_id'), $id);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'data' => null, 'message' => $e->getMessage(), 'meta' => []], 422);
        }

        if ($dto === null) {
            return response()->json(['success' => false, 'data' => null, 'message' => 'Order not found.', 'meta' => []], 404);
        }

        return response()->json(['success' => true, 'data' => $dto->toArray(), 'message' => 'Order cancelled.', 'meta' => []]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, string $id): JsonResponse
    {
        $dto = $this->orderService->updateStatus($request->attributes->get('tenant_id'), $id, $request->input('status'));

        if ($dto === null) {
            return response()->json(['success' => false, 'data' => null, 'message' => 'Order not found.', 'meta' => []], 404);
        }

        return response()->json(['success' => true, 'data' => $dto->toArray(), 'message' => 'Order status updated.', 'meta' => []]);
    }
}
