<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $userId,
        public readonly string $tenantId,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('tenant.' . $this->tenantId . '.users')];
    }

    public function broadcastAs(): string
    {
        return 'user.deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'id'        => $this->userId,
            'tenant_id' => $this->tenantId,
        ];
    }
}
