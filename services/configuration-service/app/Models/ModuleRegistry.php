<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Module registry for the plugin marketplace.
 * Tracks enabled modules per tenant, their configuration, and dependency graph.
 * Modules can be enabled/disabled at runtime without redeployment.
 */
class ModuleRegistry extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'id',
        'tenant_id',
        'module_name',
        'module_key',
        'is_enabled',
        'configuration',
        'dependencies',
        'version',
        'metadata',
    ];

    protected $casts = [
        'is_enabled'    => 'boolean',
        'configuration' => 'array',
        'dependencies'  => 'array',
        'metadata'      => 'array',
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

    public function hasDependency(string $moduleKey): bool
    {
        return in_array($moduleKey, $this->dependencies ?? [], true);
    }

    public function getConfigValue(string $key, mixed $default = null): mixed
    {
        return $this->configuration[$key] ?? $default;
    }
}
