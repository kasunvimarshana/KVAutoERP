<?php

namespace App\Modules\Order\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Order\DTOs\OrderDTO;
use App\Modules\Order\Requests\CreateOrderRequest;
use App\Modules\Order\Requests\UpdateOrderStatusRequest;
use App\Modules\Order\Resources\OrderResource;
use App\Modules\Order\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['tenant_id', 'user_id', 'status', 'sort_by', 'sort_dir']);
        $perPage = $request->input('per_page', 15);
        $orders = $this->orderService->list($filters, $perPage);
        return OrderResource::collection($orders);
    }

    public function show(int $id): OrderResource
    {
        $order = $this->orderService->get($id);
        return new OrderResource($order);
    }

    public function store(CreateOrderRequest $request): JsonResponse
    {
        $dto = OrderDTO::fromArray($request->validated());
        $order = $this->orderService->create($dto);
        return (new OrderResource($order))->response()->setStatusCode(201);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, int $id): OrderResource
    {
        $order = $this->orderService->updateStatus($id, $request->input('status'));
        return new OrderResource($order);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->orderService->delete($id);
        return response()->json(['message' => 'Order deleted successfully']);
    }
}
