<?php

namespace App\Modules\Order\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Order\DTOs\OrderDTO;
use App\Modules\Order\Requests\CreateOrderRequest;
use App\Modules\Order\Resources\OrderResource;
use App\Modules\Order\Resources\OrderCollection;
use App\Modules\Order\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    public function index(Request $request): OrderCollection
    {
        $filters = $request->only([
            'user_id',
            'status',
            'search',
            'from_date',
            'to_date',
            'sort_by',
            'sort_direction',
        ]);

        $perPage = min((int) $request->input('per_page', 15), 100);
        $orders  = $this->orderService->listOrders($filters, $perPage);

        return new OrderCollection($orders);
    }

    public function show(int $id): JsonResponse
    {
        $order = $this->orderService->getOrder($id);
        return response()->json(new OrderResource($order));
    }

    public function store(CreateOrderRequest $request): JsonResponse
    {
        $dto   = OrderDTO::fromRequest($request->validated());
        $order = $this->orderService->createOrder($dto);
        return response()->json(new OrderResource($order), 201);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $request->validate(['reason' => ['sometimes', 'string', 'max:500']]);
        $order = $this->orderService->cancelOrder($id, $request->input('reason', ''));
        return response()->json(new OrderResource($order));
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:confirmed,processing,shipped,delivered,cancelled'],
        ]);

        $order = $this->orderService->updateOrderStatus($id, $request->input('status'));
        return response()->json(new OrderResource($order));
    }
}
