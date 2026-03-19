<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Repositories\OrderRepository;
use App\Services\OrderService;
use Shared\Core\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends BaseController
{
    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var OrderService
     */
    protected $orderService;

    public function __construct(OrderRepository $orderRepository, OrderService $orderService)
    {
        $this->orderRepository = $orderRepository;
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of orders with cross-service filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderRepository->searchOrders($request->all());

        return $this->success(OrderResource::collection($orders)->response()->getData(true));
    }

    /**
     * Display the specified order.
     */
    public function show($id): JsonResponse
    {
        $order = $this->orderRepository->with(['items'])->find($id);

        if (!$order) {
            return $this->error('Order not found', 404);
        }

        return $this->success(new OrderResource($order));
    }

    /**
     * Create a new order using the Saga pattern.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $order = $this->orderService->createOrder($request->all());

            if (!$order) {
                return $this->error('Order creation failed. Rolling back distributed transaction.', 500);
            }

            return $this->success(new OrderResource($order), 'Order created successfully with Saga coordination.', 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
