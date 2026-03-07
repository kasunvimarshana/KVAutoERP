<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly User $user) {}

    public function broadcastOn(): array
    {
        return [new Channel('tenant.' . $this->user->tenant_id . '.users')];
    }

    public function broadcastAs(): string
    {
        return 'user.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id'        => $this->user->id,
            'name'      => $this->user->name,
            'email'     => $this->user->email,
            'role'      => $this->user->role,
            'tenant_id' => $this->user->tenant_id,
        ];
    }
}
