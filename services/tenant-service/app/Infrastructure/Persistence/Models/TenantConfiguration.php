<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TenantConfiguration Eloquent Model.
 *
 * Maps to the `tenant_configurations` table.
 * Provides query scopes for filtering by tenant and environment.
 */
class TenantConfiguration extends Model
{
    use HasUuids;

    /** @var string */
    protected $table = 'tenant_configurations';

    /** @var string */
    protected $primaryKey = 'id';

    /** @var bool */
    public $incrementing = false;

    /** @var string */
    protected $keyType = 'string';

    /** @var array<int, string> */
    protected $fillable = [
        'id',
        'tenant_id',
        'config_key',
        'config_value',
        'environment',
        'is_secret',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_secret'  => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ──────────────────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────────────────

    /**
     * @return BelongsTo<Tenant, TenantConfiguration>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Query Scopes
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Scope: filter by tenant ID.
     *
     * @param  Builder<TenantConfiguration>  $query
     * @param  string                         $tenantId
     * @return Builder<TenantConfiguration>
     */
    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope: filter by environment.
     *
     * @param  Builder<TenantConfiguration>  $query
     * @param  string                         $environment
     * @return Builder<TenantConfiguration>
     */
    public function scopeForEnvironment(Builder $query, string $environment): Builder
    {
        return $query->where('environment', $environment);
    }
}
