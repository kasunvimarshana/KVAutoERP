<?php

declare(strict_types=1);

namespace Shared\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * HasAuditLog Trait
 * 
 * Automatically logs create, update, and delete operations.
 */
trait HasAuditLog
{
    public static function bootHasAuditLog(): void
    {
        static::created(fn($model) => static::logAudit('created', $model));
        static::updated(fn($model) => static::logAudit('updated', $model));
        static::deleted(fn($model) => static::logAudit('deleted', $model));
    }

    protected static function logAudit(string $event, $model): void
    {
        Log::channel('audit')->info("Model {$event}", [
            'model' => get_class($model),
            'id' => $model->getKey(),
            'user_id' => Auth::id() ?? 'system',
            'tenant_id' => $model->tenant_id ?? null,
            'changes' => $event === 'updated' ? $model->getChanges() : null,
            'timestamp' => now()->toISOString(),
        ]);
    }
}
