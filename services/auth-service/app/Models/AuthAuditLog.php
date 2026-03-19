<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Immutable, append-only authentication audit log.
 *
 * This model intentionally:
 *   - Has no updated_at timestamp (audit records never change).
 *   - Has no soft-deletes (audit records must never be deleted except
 *     by a scheduled purge job governed by retention policy).
 *   - Has a bigint auto-increment PK for fast sequential writes.
 *   - Has no $guarded — the repository controls what is written.
 *
 * @property int                 $id
 * @property string|null         $user_id
 * @property string|null         $tenant_id
 * @property string              $event_type
 * @property string|null         $ip_address
 * @property string|null         $user_agent
 * @property string|null         $device_id
 * @property array<string,mixed>|null $metadata
 * @property \Carbon\Carbon      $created_at
 */
class AuthAuditLog extends Model
{
    /** @var string */
    protected $table = 'auth_audit_logs';

    /** @var bool — bigint auto-increment */
    public $incrementing = true;

    /** @var string */
    protected $keyType = 'int';

    /**
     * Only created_at; no updated_at because audit records are immutable.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Boot and automatically set created_at on insert.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(static function (self $model): void {
            if (empty($model->created_at)) {
                $model->created_at = now();
            }
        });

        // Prevent any update — audit records are immutable.
        static::updating(static function (): bool {
            return false;
        });
    }

    /**
     * Prevent deletion of individual audit records.
     *
     * Only the scheduled retention purge job may delete records in bulk.
     *
     * @return bool  Always false.
     */
    public function delete(): bool
    {
        return false;
    }

    /**
     * Return the user relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
