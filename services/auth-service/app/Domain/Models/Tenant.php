<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

/**
 * Tenant Domain Model
 * 
 * Represents a tenant in the multi-tenant SaaS system.
 * Stores all tenant-specific configurations that can be
 * dynamically reloaded at runtime without redeployment.
 */
class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'plan',
        'is_active',
        'db_host',
        'db_port',
        'db_name',
        'db_username',
        'db_password',
        'cache_driver',
        'queue_driver',
        'mail_driver',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_from_address',
        'mail_from_name',
        'api_keys',
        'feature_flags',
        'webhook_url',
        'webhook_secret',
        'settings',
        'expires_at',
    ];

    protected $hidden = [
        'db_password',
        'mail_password',
        'webhook_secret',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'api_keys' => 'encrypted:array',
        'feature_flags' => 'array',
        'settings' => 'array',
        'expires_at' => 'datetime',
    ];

    /**
     * Get users belonging to this tenant.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'tenant_id');
    }

    /**
     * Check if a feature is enabled for this tenant.
     */
    public function hasFeature(string $feature): bool
    {
        return (bool) ($this->feature_flags[$feature] ?? false);
    }

    /**
     * Get a tenant setting with optional default.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Dynamically update tenant configuration at runtime.
     * Clears cache so the change is picked up immediately.
     */
    public function updateConfig(string $key, mixed $value): void
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->update(['settings' => $settings]);
        Cache::tags(['tenant', "tenant:{$this->id}"])->flush();
    }

    /**
     * Enable or disable a feature flag at runtime.
     */
    public function setFeatureFlag(string $feature, bool $enabled): void
    {
        $flags = $this->feature_flags ?? [];
        $flags[$feature] = $enabled;
        $this->update(['feature_flags' => $flags]);
        Cache::tags(['tenant', "tenant:{$this->id}"])->flush();
    }
}
