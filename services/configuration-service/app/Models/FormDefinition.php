<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Metadata-driven form definitions.
 * Describes fields, layouts, and validation rules for dynamic UI rendering.
 * Versioned to allow backward-compatible form evolution.
 */
class FormDefinition extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'id',
        'tenant_id',
        'service_name',
        'entity_type',
        'fields',
        'validations',
        'is_active',
        'version',
        'metadata',
    ];

    protected $casts = [
        'fields'      => 'array',
        'validations' => 'array',
        'is_active'   => 'boolean',
        'version'     => 'integer',
        'metadata'    => 'array',
    ];

    // -----------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------

    public function scopeForTenant(\Illuminate\Database\Eloquent\Builder $query, string $tenantId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForService(\Illuminate\Database\Eloquent\Builder $query, string $serviceName): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('service_name', $serviceName);
    }

    public function scopeForEntity(\Illuminate\Database\Eloquent\Builder $query, string $entityType): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('entity_type', $entityType);
    }
}
