<?php

namespace App\Listeners;

use App\Events\UserCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyUserCreated implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';
    public int $tries = 3;
    public int $backoff = 10;

    public function handle(UserCreated $event): void
    {
        $user = $event->user;

        Log::info('User created notification', [
            'user_id'   => $user->id,
            'email'     => $user->email,
            'tenant_id' => $user->tenant_id,
        ]);

        // In production: dispatch email notification via mail service
        // Mail::to($user->email)->send(new WelcomeUserMail($user));
    }

    public function failed(UserCreated $event, \Throwable $exception): void
    {
        Log::error('NotifyUserCreated listener failed', [
            'user_id' => $event->user->id,
            'error'   => $exception->getMessage(),
        ]);
    }
}
