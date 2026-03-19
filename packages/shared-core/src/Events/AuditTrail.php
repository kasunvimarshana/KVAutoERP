<?php

namespace Shared\Core\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AuditTrail
{
    /**
     * Record a mutation for audit logging.
     */
    public function log(Model $model, string $action, array $changes, string $tenantId, int $userId): void
    {
        // Immutable, signed hash audit log entry
        $payload = [
            'model' => get_class($model),
            'model_id' => $model->getKey(),
            'action' => $action,
            'changes' => $changes,
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'timestamp' => now(),
            'signature' => hash_hmac('sha256', json_encode($changes), config('app.key')),
        ];

        Log::channel('audit')->info("Mutation recorded", $payload);
        
        // In a real system, stream this to a read-optimised PostgreSQL schema with signed hashes
    }
}
