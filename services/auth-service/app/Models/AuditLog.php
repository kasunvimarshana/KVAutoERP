<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable, append-only audit log for all authentication events.
 * Records are never updated or deleted to comply with compliance requirements.
 */
class AuditLog extends Model
{
    use HasFactory, HasUuids;

    public const UPDATED_AT = null;

    /**
     * Prevent any updates to audit log records.
     */
    public static function boot(): void
    {
        parent::boot();

        static::updating(function () {
            throw new \RuntimeException('Audit log records are immutable and cannot be updated.');
        });

        static::deleting(function () {
            throw new \RuntimeException('Audit log records are immutable and cannot be deleted.');
        });
    }

    protected $fillable = [
        'id',
        'event',
        'user_id',
        'tenant_id',
        'ip_address',
        'user_agent',
        'metadata',
        'severity',
        'created_at',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // -----------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------

    public function scopeForUser(\Illuminate\Database\Eloquent\Builder $query, string $userId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForTenant(\Illuminate\Database\Eloquent\Builder $query, string $tenantId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForEvent(\Illuminate\Database\Eloquent\Builder $query, string $event): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('event', $event);
    }

    public function scopeWithinWindow(\Illuminate\Database\Eloquent\Builder $query, int $minutes): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('created_at', '>=', now()->subMinutes($minutes));
    }
}
