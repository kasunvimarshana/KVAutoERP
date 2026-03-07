<?php

namespace App\Application\Services;

use App\Domain\Notification\Entities\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class NotificationService
{
    public function __construct(private WebhookService $webhookService) {}

    // -------------------------------------------------------------------------
    // Channel senders
    // -------------------------------------------------------------------------

    /**
     * Send an email notification using a named Blade template.
     */
    public function sendEmail(string $to, string $subject, string $template, array $data): void
    {
        $notification = $this->createRecord([
            'channel'    => Notification::CHANNEL_EMAIL,
            'recipient'  => $to,
            'subject'    => $subject,
            'template'   => $template,
            'payload'    => $data,
            'tenant_id'  => $data['tenant_id'] ?? null,
            'event_type' => $data['event_type'] ?? null,
        ]);

        try {
            Mail::send($template, $data, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });

            $this->markSent($notification);
            Log::info('Email sent', ['to' => $to, 'subject' => $subject]);
        } catch (\Throwable $e) {
            $this->markFailed($notification, $e->getMessage());
            Log::error('Email send failed', ['to' => $to, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Post a message to a Slack Incoming Webhook.
     */
    public function sendSlack(string $webhookUrl, string $message, array $attachments = []): void
    {
        $notification = $this->createRecord([
            'channel'   => Notification::CHANNEL_SLACK,
            'recipient' => $webhookUrl,
            'subject'   => 'Slack notification',
            'template'  => 'slack',
            'payload'   => ['text' => $message, 'attachments' => $attachments],
        ]);

        try {
            $body = ['text' => $message];
            if (!empty($attachments)) {
                $body['attachments'] = $attachments;
            }

            $response = Http::post($webhookUrl, $body);

            if ($response->failed()) {
                throw new \RuntimeException('Slack API returned ' . $response->status());
            }

            $this->markSent($notification);
            Log::info('Slack notification sent');
        } catch (\Throwable $e) {
            $this->markFailed($notification, $e->getMessage());
            Log::error('Slack notification failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Dispatch a signed HTTP webhook to an arbitrary URL.
     */
    public function sendWebhook(string $url, array $payload, string $secret = ''): void
    {
        $notification = $this->createRecord([
            'channel'   => Notification::CHANNEL_WEBHOOK,
            'recipient' => $url,
            'subject'   => 'Webhook dispatch',
            'template'  => 'webhook',
            'payload'   => $payload,
        ]);

        try {
            $jsonPayload = json_encode($payload);
            $headers     = ['Content-Type' => 'application/json'];

            if ($secret !== '') {
                $signature = $this->webhookService->generateSignature($jsonPayload, $secret);
                $headers['X-Webhook-Signature'] = 'sha256=' . $signature;
            }

            $response = Http::withHeaders($headers)->post($url, $payload);

            if ($response->failed()) {
                throw new \RuntimeException('Webhook target returned ' . $response->status());
            }

            $this->markSent($notification);
        } catch (\Throwable $e) {
            $this->markFailed($notification, $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send a push notification via a generic push provider.
     * Extend this method to integrate FCM / APNs as needed.
     */
    public function sendPushNotification(string $deviceToken, string $title, string $body): void
    {
        $notification = $this->createRecord([
            'channel'   => Notification::CHANNEL_PUSH,
            'recipient' => $deviceToken,
            'subject'   => $title,
            'template'  => 'push',
            'payload'   => ['title' => $title, 'body' => $body],
        ]);

        try {
            // Placeholder: integrate your push provider SDK here.
            // e.g. FCM HTTP v1 API call via Http::post(...)
            Log::info('Push notification dispatched', [
                'device' => substr($deviceToken, 0, 8) . '...',
                'title'  => $title,
            ]);

            $this->markSent($notification);
        } catch (\Throwable $e) {
            $this->markFailed($notification, $e->getMessage());
            throw $e;
        }
    }

    // -------------------------------------------------------------------------
    // Event routing
    // -------------------------------------------------------------------------

    /**
     * Route an incoming domain event to the correct notification handler.
     */
    public function handleEvent(string $event, array $payload): void
    {
        Log::info('Handling notification event', ['event' => $event]);

        match ($event) {
            'inventory.low'       => $this->onInventoryLow($payload),
            'order.created'       => $this->onOrderCreated($payload),
            'order.completed'     => $this->onOrderCompleted($payload),
            'order.failed'        => $this->onOrderFailed($payload),
            'tenant.activated'    => $this->onTenantActivated($payload),
            'tenant.suspended'    => $this->onTenantSuspended($payload),
            default               => Log::warning('Unhandled event', ['event' => $event]),
        };

        // Always dispatch to registered tenant webhooks
        if (!empty($payload['tenant_id'])) {
            $this->webhookService->dispatch($payload['tenant_id'], $event, $payload);
        }
    }

    // -------------------------------------------------------------------------
    // Event handlers
    // -------------------------------------------------------------------------

    private function onInventoryLow(array $payload): void
    {
        $email     = $payload['contact_email'] ?? null;
        $itemName  = $payload['item_name'] ?? 'Unknown item';
        $quantity  = $payload['quantity'] ?? 0;

        if ($email) {
            $this->sendEmail(
                $email,
                "Low inventory alert: {$itemName}",
                'emails.inventory.low',
                array_merge($payload, ['item_name' => $itemName, 'quantity' => $quantity])
            );
        }
    }

    private function onOrderCreated(array $payload): void
    {
        $email   = $payload['customer_email'] ?? null;
        $orderId = $payload['order_id'] ?? 'N/A';

        if ($email) {
            $this->sendEmail(
                $email,
                "Order #{$orderId} confirmed",
                'emails.orders.created',
                $payload
            );
        }
    }

    private function onOrderCompleted(array $payload): void
    {
        $email   = $payload['customer_email'] ?? null;
        $orderId = $payload['order_id'] ?? 'N/A';

        if ($email) {
            $this->sendEmail(
                $email,
                "Order #{$orderId} has been completed",
                'emails.orders.completed',
                $payload
            );
        }
    }

    private function onOrderFailed(array $payload): void
    {
        $email   = $payload['customer_email'] ?? null;
        $orderId = $payload['order_id'] ?? 'N/A';
        $reason  = $payload['reason'] ?? 'Unknown reason';

        if ($email) {
            $this->sendEmail(
                $email,
                "Order #{$orderId} failed: {$reason}",
                'emails.orders.failed',
                $payload
            );
        }
    }

    private function onTenantActivated(array $payload): void
    {
        $email = $payload['email'] ?? null;
        if ($email) {
            $this->sendEmail($email, 'Your account is now active', 'emails.tenant.activated', $payload);
        }
    }

    private function onTenantSuspended(array $payload): void
    {
        $email = $payload['email'] ?? null;
        if ($email) {
            $this->sendEmail($email, 'Your account has been suspended', 'emails.tenant.suspended', $payload);
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function createRecord(array $data): Notification
    {
        return Notification::create(array_merge(['status' => Notification::STATUS_PENDING, 'attempts' => 0], $data));
    }

    private function markSent(Notification $notification): void
    {
        $notification->update([
            'status'   => Notification::STATUS_SENT,
            'sent_at'  => now(),
            'attempts' => $notification->attempts + 1,
        ]);
    }

    private function markFailed(Notification $notification, string $error): void
    {
        $notification->update([
            'status'        => Notification::STATUS_FAILED,
            'error_message' => $error,
            'attempts'      => $notification->attempts + 1,
        ]);
    }
}
