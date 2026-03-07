<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Message;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'emails';
    public int $tries    = 3;
    public int $timeout  = 60;

    public function handle(UserRegistered $event): void
    {
        $user   = $event->user;
        $tenant = $event->tenant;

        try {
            $mailConfig = $tenant->getMailConfig();
            $fromAddress = $mailConfig['from_address'] ?? config('mail.from.address');
            $fromName    = $mailConfig['from_name']    ?? $tenant->name;

            Mail::send(
                'emails.welcome',
                [
                    'user'   => $user,
                    'tenant' => $tenant,
                ],
                function (Message $message) use ($user, $fromAddress, $fromName, $tenant): void {
                    $message->to($user->email, $user->name)
                            ->from($fromAddress, $fromName)
                            ->subject('Welcome to ' . $tenant->name . '!');
                }
            );

            Log::info('Welcome email sent', [
                'user_id'   => $user->id,
                'email'     => $user->email,
                'tenant_id' => $tenant->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send welcome email', [
                'user_id'   => $user->id,
                'tenant_id' => $tenant->id,
                'error'     => $e->getMessage(),
            ]);

            $this->fail($e);
        }
    }

    public function failed(UserRegistered $event, \Throwable $exception): void
    {
        Log::critical('Welcome email listener permanently failed', [
            'user_id'   => $event->user->id,
            'tenant_id' => $event->tenant->id,
            'error'     => $exception->getMessage(),
        ]);
    }
}
