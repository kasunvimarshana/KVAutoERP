<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'id',
        'name',
        'slug',
        'domain',
        'is_active',
        'plan',
        'feature_flags',
        'configurations',
        'token_lifetimes',
        'metadata',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'feature_flags'  => 'array',
        'configurations' => 'array',
        'token_lifetimes' => 'array',
        'metadata'       => 'array',
    ];

    // -----------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------

    public function organisations(): HasMany
    {
        return $this->hasMany(Organisation::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    public function isFeatureEnabled(string $feature): bool
    {
        $flags = $this->feature_flags ?? [];
        return (bool) ($flags[$feature] ?? config("tenant.default_features.{$feature}", false));
    }

    public function getConfiguration(string $key, mixed $default = null): mixed
    {
        return data_get($this->configurations, $key, $default);
    }

    public function getAccessTokenTtl(): int
    {
        return (int) ($this->token_lifetimes['access'] ?? config('jwt.ttl.access', 15));
    }

    public function getRefreshTokenTtl(): int
    {
        return (int) ($this->token_lifetimes['refresh'] ?? config('jwt.ttl.refresh', 43200));
    }

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }
}
