<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Tenant Eloquent Model.
 *
 * Maps to the `tenants` table and provides relationships to
 * tenant-specific configuration and webhooks.
 */
class Tenant extends Model
{
    use HasUuids;
    use SoftDeletes;

    /** @var string */
    protected $table = 'tenants';

    /** @var string */
    protected $primaryKey = 'id';

    /** @var bool */
    public $incrementing = false;

    /** @var string */
    protected $keyType = 'string';

    /** @var array<int, string> */
    protected $fillable = [
        'id',
        'name',
        'slug',
        'domain',
        'database_name',
        'settings',
        'is_active',
        'plan',
        'billing_email',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'settings'   => 'array',
        'is_active'  => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ──────────────────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────────────────

    /**
     * A tenant owns many configuration entries.
     *
     * @return HasMany<TenantConfiguration>
     */
    public function configurations(): HasMany
    {
        return $this->hasMany(TenantConfiguration::class, 'tenant_id', 'id');
    }

    /**
     * A tenant owns many webhooks.
     *
     * @return HasMany<Webhook>
     */
    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class, 'tenant_id', 'id');
    }
}
