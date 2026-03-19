<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use KvEnterprise\SharedKernel\Concerns\GeneratesUuid;
use KvEnterprise\SharedKernel\Traits\HasAuditLog;
use KvEnterprise\SharedKernel\Traits\HasTenantScope;

/**
 * Abstract base Eloquent model for all tenant-aware domain entities.
 *
 * Every model in the platform that stores tenant-scoped data MUST extend
 * this class. It provides:
 *
 *   - Automatic global scope filtering by `tenant_id` on all queries.
 *   - Automatic `tenant_id` injection on record creation.
 *   - Audit log population (`created_by`, `updated_by`, `deleted_by`).
 *
 * Convention: UUID primary keys are used across the platform.
 * Set `$incrementing = false` and `$keyType = 'string'` (already done here).
 */
abstract class TenantAwareModel extends Model
{
    use GeneratesUuid;
    use HasAuditLog;
    use HasTenantScope;

    /**
     * Disable auto-incrementing integer primary keys.
     * All models use UUID v4 string primary keys.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Primary key type for UUID support.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Allow mass assignment for all attributes (guarding is handled
     * at the service/request layer, not the model layer).
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * Columns that every tenant-aware model must expose via toArray() / JSON.
     * Subclasses should merge their own casts on top of these.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model and register the global tenant scope.
     *
     * The global scope is applied to every query on this model (and its
     * subclasses), ensuring that all database reads are always filtered
     * to the current tenant's data without requiring explicit scoping
     * at the repository or service layer.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope('tenant', static function (Builder $query): void {
            $tenantId = static::resolveTenantIdForScope();

            if ($tenantId !== null) {
                $query->where(
                    (new static())->getTable() . '.tenant_id',
                    $tenantId,
                );
            }
        });
    }

    /**
     * Generate a UUID v4 primary key when creating a new model instance.
     *
     * Called by Eloquent's creating event when the key is not already set.
     *
     * @return void
     */
    protected static function booted(): void
    {
        parent::booted();

        static::creating(static function (Model $model): void {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = static::generateUuidV4();
            }
        });
    }

    /**
     * Resolve the current tenant ID string for use in the global scope closure.
     *
     * Separated from the trait's resolveTenantId() to avoid visibility issues.
     *
     * @return string|null
     */
    private static function resolveTenantIdForScope(): ?string
    {
        try {
            /** @var \KvEnterprise\SharedKernel\ValueObjects\TenantId|null $tenantId */
            $tenantId = app(\KvEnterprise\SharedKernel\ValueObjects\TenantId::class);

            return $tenantId?->getValue();
        } catch (\Throwable) {
            return null;
        }
    }
}
