<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserProfile extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'id',
        'user_id',
        'tenant_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'bio',
        'address',
        'city',
        'country',
        'timezone',
        'language',
        'preferences',
        'metadata',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'preferences'   => 'array',
        'metadata'      => 'array',
    ];

    // -----------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
}
