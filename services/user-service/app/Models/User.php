<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'id',
        'tenant_id',
        'organisation_id',
        'branch_id',
        'location_id',
        'department_id',
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'is_active',
        'metadata',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata'  => 'array',
        'password'  => 'hashed',
    ];

    // -----------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    // -----------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------

    public function scopeForTenant(
        \Illuminate\Database\Eloquent\Builder $query,
        string $tenantId,
    ): \Illuminate\Database\Eloquent\Builder {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeActive(
        \Illuminate\Database\Eloquent\Builder $query,
    ): \Illuminate\Database\Eloquent\Builder {
        return $query->where('is_active', true);
    }

    public function scopeSearch(
        \Illuminate\Database\Eloquent\Builder $query,
        string $term,
    ): \Illuminate\Database\Eloquent\Builder {
        return $query->where(function (\Illuminate\Database\Eloquent\Builder $q) use ($term): void {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%");
        });
    }
}
