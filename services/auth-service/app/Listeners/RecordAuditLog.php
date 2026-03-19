<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TokenRefreshed;
use App\Events\UserLoggedIn;
use App\Events\UserLoggedOut;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Writes an immutable audit log entry for every auth event.
 * The audit_logs table uses no updated_at column to remain append-only.
 */
class RecordAuditLog
{
    public function handleUserLoggedIn(UserLoggedIn $event): void
    {
        $this->write('auth.login', $event->userId, $event->tenantId, [
            'device_id'  => $event->deviceId,
            'ip_address' => $event->ipAddress,
        ]);
    }

    public function handleUserLoggedOut(UserLoggedOut $event): void
    {
        $this->write('auth.logout', $event->userId, null, [
            'device_id'  => $event->deviceId,
            'all_devices' => $event->allDevices,
        ]);
    }

    public function handleTokenRefreshed(TokenRefreshed $event): void
    {
        $this->write('auth.token_refresh', $event->userId, null, [
            'device_id' => $event->deviceId,
        ]);
    }

    private function write(string $action, string $actorId, ?string $tenantId, array $context): void
    {
        try {
            DB::table('audit_logs')->insert([
                'id'          => (string) Str::uuid(),
                'action'      => $action,
                'entity_type' => 'user',
                'entity_id'   => $actorId,
                'actor_id'    => $actorId,
                'tenant_id'   => $tenantId,
                'context'     => json_encode($context),
                'created_at'  => now(),
            ]);
        } catch (\Throwable) {
            // Audit log failures must never break the main flow
        }
    }
}
