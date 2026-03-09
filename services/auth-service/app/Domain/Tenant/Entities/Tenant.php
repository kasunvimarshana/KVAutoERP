<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Entities;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Tenant Entity.
 *
 * Represents an organizational tenant in the multi-tenant SaaS platform.
 * Each tenant has isolated data, configuration, and auth credentials.
 */
class Tenant extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'tenants';

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'database_name',
        'status',
        'plan',
        'configuration',
        'metadata',
    ];

    protected $casts = [
        'configuration' => 'array',
        'metadata'      => 'array',
        'status'        => 'string',
    ];

    protected $hidden = [
        'configuration',
    ];

    // =========================================================================
    // Accessors & Mutators
    // =========================================================================

    /**
     * Check if this tenant is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get a specific configuration value.
     *
     * @param string $key     Dot-notation config key
     * @param mixed  $default
     * @return mixed
     */
    public function getConfig(string $key, mixed $default = null): mixed
    {
        return data_get($this->configuration, $key, $default);
    }

    /**
     * Get the tenant's database connection name.
     *
     * Uses tenant-specific connection if configured, otherwise falls back to default.
     */
    public function getDatabaseConnection(): string
    {
        return $this->getConfig('database.connection', "tenant_{$this->slug}");
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Domain\User\Entities\User::class, 'tenant_id');
    }

    public function roles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Domain\Auth\Entities\Role::class, 'tenant_id');
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeBySlug(\Illuminate\Database\Eloquent\Builder $query, string $slug): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('slug', $slug);
    }
}
