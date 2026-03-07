<?php

namespace App\Modules\Order\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Order\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->orderService->index($request->query()));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.quantity'    => 'required|integer|min:1',
            'currency'            => 'sometimes|string|size:3',
            'notes'               => 'nullable|string',
        ]);

        $result = $this->orderService->placeOrder(array_merge(
            $request->all(),
            ['user_id' => $request->user()->id]
        ));

        return response()->json($result, 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(
            $this->orderService->show($id)->load('items.product', 'user')
        );
    }

    public function cancel(int $id): JsonResponse
    {
        return response()->json($this->orderService->cancelOrder($id));
    }
}
