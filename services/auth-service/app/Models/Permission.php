<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'id',
        'name',
        'display_name',
        'description',
        'guard_name',
        'group',
        'is_system',
        'metadata',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'metadata'  => 'array',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions')
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_permissions')
            ->withPivot(['granted', 'organisation_id'])
            ->withTimestamps();
    }

    public function scopeForGroup(\Illuminate\Database\Eloquent\Builder $query, string $group): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('group', $group);
    }
}
