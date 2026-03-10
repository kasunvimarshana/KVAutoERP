<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Permission Domain Model
 *
 * Granular permissions for Attribute-Based Access Control (ABAC).
 * Each permission has a resource, action, and optional conditions.
 */
class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'resource',
        'action',
        'description',
        'conditions',
    ];

    protected $casts = [
        'conditions' => 'array',
    ];

    public function scopeForTenant($query, string|int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForResource($query, string $resource)
    {
        return $query->where('resource', $resource);
    }
}
