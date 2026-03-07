<?php

namespace App\Http\Controllers;

use App\Application\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    /**
     * Internal endpoint: receive a domain event from another microservice and
     * route it to the appropriate notification channel(s).
     */
    public function handle(Request $request): JsonResponse
    {
        $request->validate([
            'event'   => ['required', 'string'],
            'payload' => ['required', 'array'],
        ]);

        $event   = $request->input('event');
        $payload = $request->input('payload');

        try {
            $this->notificationService->handleEvent($event, $payload);
            return response()->json(['message' => 'Event handled successfully', 'event' => $event]);
        } catch (\Throwable $e) {
            Log::error('Event handling failed', ['event' => $event, 'error' => $e->getMessage()]);
            return response()->json([
                'error'   => 'Event handling failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
