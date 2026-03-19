<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use KvEnterprise\SharedKernel\Exceptions\TenantException;

/**
 * Provides automatic tenant scoping for Eloquent models.
 *
 * When applied to a model, all queries are automatically filtered by
 * the `tenant_id` column, preventing cross-tenant data access.
 *
 * Requirements for the host model:
 *   - Table must have a `tenant_id` (UUID string) column.
 *   - The host model must be used within a request or job context where
 *     a `TenantId` binding exists in the service container.
 */
trait HasTenantScope
{
    /**
     * Boot the HasTenantScope trait.
     *
     * Attaches a `creating` listener to auto-populate `tenant_id`
     * when it is not explicitly provided.
     *
     * @return void
     */
    public static function bootHasTenantScope(): void
    {
        static::creating(static function (Model $model): void {
            if (empty($model->tenant_id)) {
                $model->tenant_id = static::resolveTenantId();
            }
        });
    }

    /**
     * Apply the tenant scope to a query builder (named scope).
     *
     * Usage: Model::scopeForTenant($query, $tenantId)
     * Or via: Model::forTenant($tenantId)->get()
     *
     * @param  Builder  $query     The Eloquent query builder.
     * @param  string   $tenantId  The UUID of the tenant to filter by.
     * @return Builder
     */
    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        return $query->where($this->getTable() . '.tenant_id', $tenantId);
    }

    /**
     * Apply a global tenant scope to every query on this model.
     *
     * Called by the host model's boot method when auto-scoping is required.
     * Add the TenantGlobalScope class to the model's `$globalScopes` if you
     * prefer the global approach; this method exists for on-demand use.
     *
     * @param  Builder  $query  The Eloquent query builder.
     * @return Builder
     */
    public function scopeCurrentTenant(Builder $query): Builder
    {
        $tenantId = static::resolveTenantId();

        if ($tenantId === null) {
            return $query;
        }

        return $query->where($this->getTable() . '.tenant_id', $tenantId);
    }

    /**
     * Return the tenant_id attribute value.
     *
     * @return string|null
     */
    public function getTenantId(): ?string
    {
        return $this->getAttribute('tenant_id');
    }

    /**
     * Set the tenant_id attribute value.
     *
     * @param  string  $tenantId  UUID string.
     * @return void
     */
    public function setTenantId(string $tenantId): void
    {
        $this->setAttribute('tenant_id', $tenantId);
    }

    /**
     * Verify that this model belongs to the given tenant.
     *
     * @param  string  $tenantId  The expected tenant UUID.
     * @return void
     *
     * @throws TenantException When the model belongs to a different tenant.
     */
    public function assertBelongsToTenant(string $tenantId): void
    {
        if ($this->getAttribute('tenant_id') !== $tenantId) {
            throw TenantException::accessDenied(
                $this->getAttribute('tenant_id') ?? 'unknown',
                $tenantId,
            );
        }
    }

    /**
     * Attempt to resolve the current tenant ID from the service container.
     *
     * Returns null (rather than throwing) so that the trait can be used
     * in contexts where the container is not yet fully bootstrapped.
     *
     * @return string|null
     */
    private static function resolveTenantId(): ?string
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
