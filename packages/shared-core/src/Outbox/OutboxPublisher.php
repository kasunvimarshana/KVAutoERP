<?php

namespace Shared\Core\Outbox;

use Illuminate\Support\Facades\DB;
use Shared\Core\Outbox\OutboxEntry;

class OutboxPublisher
{
    /**
     * Publishes an event to the outbox within the current transaction.
     */
    public function publish(string $eventName, array $payload, string $tenantId): void
    {
        OutboxEntry::create([
            'event_name' => $eventName,
            'payload' => $payload,
            'status' => 'pending',
            'attempts' => 0,
            'tenant_id' => $tenantId,
        ]);
    }
}
