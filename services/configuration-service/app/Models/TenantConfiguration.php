<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Runtime tenant configuration scoped per service.
 * Supports string, json, boolean, and integer value types.
 * Sensitive values may be stored encrypted.
 */
class TenantConfiguration extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'id',
        'tenant_id',
        'service_name',
        'config_key',
        'config_value',
        'config_type',
        'is_encrypted',
        'is_active',
        'description',
        'metadata',
    ];

    protected $casts = [
        'config_value' => 'array',
        'is_encrypted' => 'boolean',
        'is_active'    => 'boolean',
        'metadata'     => 'array',
    ];

    // -----------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------

    public function scopeForTenant(\Illuminate\Database\Eloquent\Builder $query, string $tenantId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForService(\Illuminate\Database\Eloquent\Builder $query, string $serviceName): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('service_name', $serviceName);
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    public function getTypedValue(): mixed
    {
        $raw = $this->config_value['value'] ?? null;

        return match ($this->config_type) {
            'boolean' => (bool) $raw,
            'integer' => (int) $raw,
            'json'    => is_string($raw) ? json_decode($raw, true) : $raw,
            default   => (string) ($raw ?? ''),
        };
    }
}
