<?php

declare(strict_types=1);

namespace App\Domain\Auth\Entities;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * ABAC Policy Entity.
 *
 * Stores attribute-based access control policies per resource/action.
 */
class AbacPolicy extends Model
{
    use HasUuids;

    protected $table = 'abac_policies';

    protected $fillable = [
        'tenant_id',
        'name',
        'resource',
        'action',
        'conditions',
        'is_active',
        'description',
    ];

    protected $casts = [
        'conditions' => 'array',
        'is_active'  => 'boolean',
    ];

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }
}
