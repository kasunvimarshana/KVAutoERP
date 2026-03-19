<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceSession extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'user_id',
        'tenant_id',
        'device_id',
        'device_name',
        'device_type',
        'platform',
        'ip_address',
        'user_agent',
        'refresh_token_hash',
        'refresh_token_expires_at',
        'last_activity_at',
        'revoked_at',
        'revocation_reason',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'refresh_token_expires_at' => 'datetime',
        'last_activity_at'         => 'datetime',
        'revoked_at'               => 'datetime',
        'is_active'                => 'boolean',
        'metadata'                 => 'array',
    ];

    protected $hidden = [
        'refresh_token_hash',
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

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true)
            ->whereNull('revoked_at')
            ->where('refresh_token_expires_at', '>', now());
    }

    public function scopeForUser(\Illuminate\Database\Eloquent\Builder $query, string $userId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForTenant(\Illuminate\Database\Eloquent\Builder $query, string $tenantId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    public function isExpired(): bool
    {
        return $this->refresh_token_expires_at !== null && $this->refresh_token_expires_at->isPast();
    }

    public function revoke(string $reason = 'manual'): void
    {
        $this->update([
            'is_active'         => false,
            'revoked_at'        => now(),
            'revocation_reason' => $reason,
        ]);
    }
}
