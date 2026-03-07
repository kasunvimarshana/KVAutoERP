<?php

namespace App\Modules\Product\Webhooks;

use Illuminate\Http\Request;

class ProductWebhookHandler
{
    public function handle(Request $request): array
    {
        $event = $request->input('event', '');
        $data = $request->input('data', []);

        \Illuminate\Support\Facades\Log::info("Product webhook: {$event}", $data);

        return ['status' => 'processed', 'event' => $event];
    }
}
