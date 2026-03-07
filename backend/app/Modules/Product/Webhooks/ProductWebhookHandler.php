<?php

namespace App\Modules\Product\Webhooks;

use App\Modules\Product\DTOs\ProductWebhookDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class ProductWebhookHandler extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $dto = ProductWebhookDTO::fromPayload($request->all());

        Log::info('Product webhook received', [
            'event'      => $dto->event,
            'product_id' => $dto->productId,
        ]);

        return response()->json(['status' => 'processed']);
    }
}
