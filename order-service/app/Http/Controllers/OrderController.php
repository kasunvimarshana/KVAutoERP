<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\SagaOrchestrator;

class OrderController extends Controller
{
    public function __construct(private SagaOrchestrator $saga) {}

    /**
     * List orders for the authenticated user.
     */
    public function index(Request $request)
    {
        $userId = $request->user_id;
        $orders = Order::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['orders' => $orders]);
    }

    /**
     * Create a new order and kick off the Saga.
     *
     * POST /api/orders
     * {
     *   "product_id": "...",
     *   "quantity": 2
     * }
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'product_id' => 'required|string',
            'quantity'   => 'required|integer|min:1',
        ]);

        $userId = $request->user_id;

        // Create order in PENDING state
        $order = Order::create([
            'user_id'       => $userId,
            'product_id'    => $request->product_id,
            'quantity'      => $request->quantity,
            'unit_price'    => 0,
            'total_price'   => 0,
            'status'        => Order::STATUS_PENDING,
            'saga_state'    => 'order_created',
        ]);

        // Publish event to start the Saga
        $this->saga->startOrderSaga([
            'order_id'   => $order->id,
            'user_id'    => $userId,
            'product_id' => $request->product_id,
            'quantity'   => $request->quantity,
        ]);

        return response()->json([
            'message' => 'Order created, processing...',
            'order'   => $order,
        ], 202);
    }

    /**
     * Get order details.
     */
    public function show(Request $request, int $id)
    {
        $order = Order::findOrFail($id);

        if ($order->user_id != $request->user_id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return response()->json(['order' => $order]);
    }

    /**
     * Cancel an order (only if PENDING).
     */
    public function cancel(Request $request, int $id)
    {
        $order = Order::findOrFail($id);

        if ($order->user_id != $request->user_id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        if ($order->status !== Order::STATUS_PENDING) {
            return response()->json(['error' => 'Only pending orders can be cancelled'], 422);
        }

        $order->update(['status' => Order::STATUS_CANCELLED]);

        return response()->json(['message' => 'Order cancelled', 'order' => $order]);
    }
}
