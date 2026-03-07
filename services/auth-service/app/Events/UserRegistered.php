<?php

namespace App\Events;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserRegistered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly Tenant $tenant,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('tenant.' . $this->tenant->id . '.events'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'user.registered';
    }

    public function broadcastWith(): array
    {
        return [
            'user' => [
                'id'         => $this->user->id,
                'name'       => $this->user->name,
                'email'      => $this->user->email,
                'role'       => $this->user->role,
                'tenant_id'  => $this->user->tenant_id,
                'created_at' => $this->user->created_at?->toIso8601String(),
            ],
            'tenant' => [
                'id'   => $this->tenant->id,
                'name' => $this->tenant->name,
                'slug' => $this->tenant->slug,
            ],
        ];
    }
}
