<?php

namespace App\Modules\Order\Webhooks;

use App\Modules\Order\DTOs\OrderWebhookDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class OrderWebhookHandler extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $dto = OrderWebhookDTO::fromPayload($request->all());

        Log::info('Order webhook received', [
            'event'    => $dto->event,
            'order_id' => $dto->orderId,
        ]);

        return response()->json(['status' => 'processed']);
    }
}
