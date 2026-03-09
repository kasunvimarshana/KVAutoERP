<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-tenant runtime configuration entry.
 *
 * Each row represents a single dot-notation config key/value pair for a tenant.
 *
 * @property int    $id
 * @property int    $tenant_id
 * @property string $key        e.g. "mail.default"
 * @property string $value      JSON-encoded value
 * @property bool   $is_active
 */
class TenantConfiguration extends Model
{
    protected $table = 'tenant_configurations';

    protected $fillable = ['tenant_id', 'key', 'value', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** @return BelongsTo<Tenant, TenantConfiguration> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
