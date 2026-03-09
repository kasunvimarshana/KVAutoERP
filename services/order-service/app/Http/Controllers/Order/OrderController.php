<?php

declare(strict_types=1);

namespace App\Http\Controllers\Order;

use App\Domain\Order\Services\OrderService;
use App\Http\Requests\Order\CancelOrderRequest;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\ListOrdersRequest;
use App\Http\Resources\Order\OrderCollection;
use App\Http\Resources\Order\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Order Controller.
 *
 * Thin controller: delegates to OrderService.
 * Handles only request ingestion and response formatting.
 */
class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    /**
     * List orders.
     *
     * GET /api/v1/orders
     */
    public function index(ListOrdersRequest $request): JsonResponse
    {
        $orders = $this->orderService->list(
            tenantId: $request->attributes->get('tenant_id'),
            params: $request->validated(),
        );

        return (new OrderCollection($orders))->response();
    }

    /**
     * Get a single order with saga status.
     *
     * GET /api/v1/orders/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $order = $this->orderService->getById(
            id: $id,
            tenantId: $request->attributes->get('tenant_id'),
        );

        return (new OrderResource($order->load(['items', 'sagaLog'])))->response();
    }

    /**
     * Create an order and execute the distributed Saga.
     *
     * POST /api/v1/orders
     *
     * Returns the order and Saga execution details (completed/compensated steps).
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        $result = $this->orderService->createAndProcess(
            tenantId: $request->attributes->get('tenant_id'),
            data: $request->validated(),
        );

        $sagaResult = $result['saga_result'];
        $statusCode = $sagaResult->isSuccess()
            ? Response::HTTP_CREATED
            : Response::HTTP_UNPROCESSABLE_ENTITY;

        return response()->json([
            'success' => $sagaResult->isSuccess(),
            'data'    => new OrderResource($result['order']),
            'saga'    => [
                'transaction_id'    => $sagaResult->getTransactionId(),
                'success'           => $sagaResult->isSuccess(),
                'completed_steps'   => $sagaResult->getCompletedSteps(),
                'failed_steps'      => $sagaResult->getFailedSteps(),
                'compensated_steps' => $sagaResult->getCompensatedSteps(),
                'error'             => $sagaResult->getError()?->getMessage(),
            ],
        ], $statusCode);
    }

    /**
     * Cancel an order.
     *
     * POST /api/v1/orders/{id}/cancel
     */
    public function cancel(CancelOrderRequest $request, string $id): JsonResponse
    {
        $order = $this->orderService->cancel(
            id: $id,
            tenantId: $request->attributes->get('tenant_id'),
            reason: $request->validated('reason', ''),
        );

        return (new OrderResource($order))->response();
    }
}
