<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Controllers;

use App\Modules\Order\Application\Services\OrderService;
use App\Modules\Order\Http\Requests\StoreOrderRequest;
use App\Modules\Order\Http\Resources\OrderCollection;
use App\Modules\Order\Http\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * OrderController
 *
 * Thin controller that delegates to OrderService.
 * Order creation uses the Saga orchestrator internally.
 */
class OrderController
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    /**
     * GET /api/v1/orders
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->has('per_page') ? (int) $request->input('per_page') : null;
        $page    = max(1, (int) $request->input('page', 1));

        $orders = $this->orderService->list(
            filters: $request->only(['status', 'customer_id']),
            sort:    [$request->input('sort_by', 'created_at') => $request->input('sort_dir', 'desc')],
            perPage: $perPage,
            page:    $page
        );

        return response()->json([
            'success' => true,
            'data'    => new OrderCollection($orders),
        ]);
    }

    /**
     * GET /api/v1/orders/{id}
     */
    public function show(int|string $id): JsonResponse
    {
        $order = $this->orderService->findById($id);

        return response()->json([
            'success' => true,
            'data'    => new OrderResource($order),
        ]);
    }

    /**
     * POST /api/v1/orders
     *
     * Triggers the CreateOrder Saga.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data              = $request->validated();
        $data['tenant_id'] = $request->user()->tenant_id;

        $order = $this->orderService->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully.',
            'data'    => new OrderResource($order),
        ], 201);
    }

    /**
     * POST /api/v1/orders/{id}/cancel
     */
    public function cancel(int|string $id): JsonResponse
    {
        $order = $this->orderService->cancel($id);

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully.',
            'data'    => new OrderResource($order),
        ]);
    }
}
