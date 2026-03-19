<?php

declare(strict_types=1);

namespace App\Models;

use KvEnterprise\SharedKernel\Concerns\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;

/**
 * Refresh token model.
 *
 * Not tenant-aware at the model level (no global scope) because the
 * repository always filters by user_id, which is implicitly tenant-scoped
 * through the User model. This avoids the overhead of tenant-context
 * resolution on every token lookup.
 *
 * @property string              $id
 * @property string              $user_id
 * @property string              $tenant_id
 * @property string              $device_id
 * @property string              $token_hash
 * @property \Carbon\Carbon      $expires_at
 * @property \Carbon\Carbon|null $revoked_at
 * @property \Carbon\Carbon      $created_at
 */
class RefreshToken extends Model
{
    use GeneratesUuid;

    /** @var string */
    protected $table = 'refresh_tokens';

    /** @var bool */
    public $incrementing = false;

    /** @var string */
    protected $keyType = 'string';

    /** @var bool — refresh tokens have no updated_at column */
    public $timestamps = false;

    /**
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Boot and generate UUID on creation.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(static function (self $model): void {
            if (empty($model->id)) {
                $model->id = static::generateUuidV4();
            }

            if (empty($model->created_at)) {
                $model->created_at = now();
            }
        });
    }

    /**
     * Determine whether this refresh token has been revoked.
     *
     * @return bool
     */
    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    /**
     * Determine whether this refresh token has expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Determine whether this refresh token is currently valid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return !$this->isRevoked() && !$this->isExpired();
    }

    /**
     * Return the owning user relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
