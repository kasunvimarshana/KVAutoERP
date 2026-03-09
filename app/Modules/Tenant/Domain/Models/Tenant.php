<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Domain\Models;

use App\Core\Traits\HasConditionalPagination;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Tenant model.
 *
 * Represents an isolated organisation (tenant) in the multi-tenant SaaS platform.
 *
 * @property int    $id
 * @property string $name
 * @property string $slug
 * @property string $domain
 * @property string $plan          Subscription plan: free|starter|pro|enterprise
 * @property bool   $is_active
 * @property array  $settings      JSON column for tenant-specific settings
 * @property string $database_name Optional dedicated database name (DB-per-tenant)
 */
class Tenant extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'tenants';

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'plan',
        'is_active',
        'settings',
        'database_name',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings'  => 'array',
    ];

    // -------------------------------------------------------------------------
    //  Relations
    // -------------------------------------------------------------------------

    /** @return HasMany<\App\Modules\Auth\Domain\Models\User> */
    public function users(): HasMany
    {
        return $this->hasMany(\App\Modules\Auth\Domain\Models\User::class, 'tenant_id');
    }

    /** @return HasMany<\App\Modules\Tenant\Domain\Models\TenantConfiguration> */
    public function configurations(): HasMany
    {
        return $this->hasMany(TenantConfiguration::class, 'tenant_id');
    }

    // -------------------------------------------------------------------------
    //  Helpers
    // -------------------------------------------------------------------------

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings ?? [], $key, $default);
    }
}
