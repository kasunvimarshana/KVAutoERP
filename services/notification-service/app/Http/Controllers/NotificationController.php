<?php

namespace App\Http\Controllers;

use App\Application\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    /**
     * Manually trigger a notification send (email, slack, webhook, push).
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'channel'   => ['required', 'string', 'in:email,slack,webhook,push'],
            'recipient' => ['required', 'string'],
            'subject'   => ['sometimes', 'string', 'max:255'],
            'template'  => ['sometimes', 'string'],
            'data'      => ['sometimes', 'array'],
        ]);

        try {
            $channel  = $request->input('channel');
            $data     = $request->input('data', []);
            $tenantId = $request->header('X-Tenant-ID');

            if ($tenantId) {
                $data['tenant_id'] = $tenantId;
            }

            match ($channel) {
                'email'   => $this->notificationService->sendEmail(
                    $request->input('recipient'),
                    $request->input('subject', 'Notification'),
                    $request->input('template', 'emails.generic'),
                    $data
                ),
                'slack'   => $this->notificationService->sendSlack(
                    $request->input('recipient'),
                    $request->input('subject', 'Notification'),
                    $data['attachments'] ?? []
                ),
                'webhook' => $this->notificationService->sendWebhook(
                    $request->input('recipient'),
                    $data,
                    $data['secret'] ?? ''
                ),
                'push'    => $this->notificationService->sendPushNotification(
                    $request->input('recipient'),
                    $request->input('subject', 'Notification'),
                    $data['body'] ?? ''
                ),
            };

            return response()->json(['message' => 'Notification sent successfully']);
        } catch (\Throwable $e) {
            return response()->json([
                'error'   => 'Failed to send notification',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
