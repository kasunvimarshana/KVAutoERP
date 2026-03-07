<?php

namespace App\Modules\Inventory\Webhooks;

use App\Modules\Inventory\DTOs\InventoryWebhookDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class InventoryWebhookHandler extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $dto = InventoryWebhookDTO::fromPayload($request->all());

        Log::info('Inventory webhook received', [
            'event'        => $dto->event,
            'inventory_id' => $dto->inventoryId,
        ]);

        return response()->json(['status' => 'processed']);
    }
}
