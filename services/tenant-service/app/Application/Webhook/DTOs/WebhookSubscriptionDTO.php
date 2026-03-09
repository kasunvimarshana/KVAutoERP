<?php

declare(strict_types=1);

namespace App\Application\Webhook\DTOs;

use App\Domain\Webhook\Entities\WebhookSubscription;

final class WebhookSubscriptionDTO
{
    public function __construct(
        public readonly string  $id,
        public readonly string  $tenantId,
        public readonly string  $url,
        public readonly array   $events,
        public readonly bool    $isActive,
        public readonly int     $retryCount,
        public readonly ?string $lastTriggeredAt,
        public readonly string  $createdAt,
        public readonly string  $updatedAt,
    ) {}

    public static function fromEntity(WebhookSubscription $subscription): self
    {
        return new self(
            id:              $subscription->id,
            tenantId:        $subscription->tenant_id,
            url:             $subscription->url,
            events:          $subscription->events ?? [],
            isActive:        $subscription->is_active,
            retryCount:      $subscription->retry_count,
            lastTriggeredAt: $subscription->last_triggered_at?->toIso8601String(),
            createdAt:       $subscription->created_at->toIso8601String(),
            updatedAt:       $subscription->updated_at->toIso8601String(),
        );
    }
}
