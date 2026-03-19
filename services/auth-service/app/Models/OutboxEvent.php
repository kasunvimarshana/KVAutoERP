<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Outbox Pattern: Stores events that must be published to the message broker.
 * A background job processes pending events and publishes them, ensuring
 * at-least-once delivery with idempotency keys.
 */
class OutboxEvent extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'aggregate_type',
        'aggregate_id',
        'event_type',
        'payload',
        'tenant_id',
        'idempotency_key',
        'status',
        'attempts',
        'last_attempted_at',
        'published_at',
        'error_message',
    ];

    protected $casts = [
        'payload'           => 'array',
        'last_attempted_at' => 'datetime',
        'published_at'      => 'datetime',
        'attempts'          => 'integer',
    ];

    public const STATUS_PENDING   = 'pending';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_FAILED    = 'failed';
    public const STATUS_DEAD      = 'dead';

    public function scopePending(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeForTenant(\Illuminate\Database\Eloquent\Builder $query, string $tenantId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function markPublished(): void
    {
        $this->update([
            'status'       => self::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
    }

    public function markFailed(string $error): void
    {
        $maxAttempts = config('outbox.max_retry_attempts', 3);

        $this->update([
            'status'            => $this->attempts >= $maxAttempts ? self::STATUS_DEAD : self::STATUS_FAILED,
            'error_message'     => $error,
            'last_attempted_at' => now(),
            'attempts'          => $this->attempts + 1,
        ]);
    }
}
