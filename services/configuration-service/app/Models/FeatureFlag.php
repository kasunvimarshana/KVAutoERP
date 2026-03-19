<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Feature flags with optional percentage-based rollout and condition rules.
 * Enables gradual feature releases and A/B testing per tenant.
 */
class FeatureFlag extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'id',
        'tenant_id',
        'flag_key',
        'is_enabled',
        'rollout_percentage',
        'conditions',
        'description',
        'metadata',
    ];

    protected $casts = [
        'is_enabled'         => 'boolean',
        'rollout_percentage' => 'integer',
        'conditions'         => 'array',
        'metadata'           => 'array',
    ];

    // -----------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------

    public function scopeForTenant(\Illuminate\Database\Eloquent\Builder $query, string $tenantId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeEnabled(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_enabled', true);
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    /**
     * Evaluate whether this flag is active for a given user context.
     * Respects rollout_percentage and condition rules.
     */
    public function isActiveForContext(array $context = []): bool
    {
        if (! $this->is_enabled) {
            return false;
        }

        if ($this->rollout_percentage < 100) {
            $userId = $context['user_id'] ?? '';
            $hash = crc32($this->flag_key . $userId) % 100;
            if ($hash >= $this->rollout_percentage) {
                return false;
            }
        }

        return true;
    }
}
