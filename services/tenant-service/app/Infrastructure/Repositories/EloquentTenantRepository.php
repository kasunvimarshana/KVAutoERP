<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Tenant\Entities\Tenant;
use App\Domain\Tenant\Entities\TenantConfiguration;
use App\Domain\Tenant\Repositories\TenantRepositoryInterface;
use App\Infrastructure\Persistence\Models\Tenant as TenantModel;
use App\Infrastructure\Persistence\Models\TenantConfiguration as ConfigModel;
use App\Shared\Base\BaseRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

/**
 * Eloquent implementation of TenantRepositoryInterface.
 *
 * Maps between Eloquent models and pure domain entities.
 */
final class EloquentTenantRepository extends BaseRepository implements TenantRepositoryInterface
{
    /** @var class-string<TenantModel> */
    protected string $modelClass = TenantModel::class;

    protected bool $softDelete = true;

    // ──────────────────────────────────────────────────────────────────────
    // TenantRepositoryInterface — domain-specific queries
    // ──────────────────────────────────────────────────────────────────────

    /**
     * {@inheritDoc}
     */
    public function findBySlug(string $slug): ?Tenant
    {
        /** @var TenantModel|null $model */
        $model = $this->newQuery()->where('slug', $slug)->first();

        return $model ? Tenant::fromArray($model->toArray()) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function findByDomain(string $domain): ?Tenant
    {
        /** @var TenantModel|null $model */
        $model = $this->newQuery()->where('domain', $domain)->first();

        return $model ? Tenant::fromArray($model->toArray()) : null;
    }

    /**
     * {@inheritDoc}
     *
     * @return array<int, Tenant>
     */
    public function findActive(): array
    {
        return $this->newQuery()
            ->where('is_active', true)
            ->get()
            ->map(fn (TenantModel $m) => Tenant::fromArray($m->toArray()))
            ->all();
    }

    /**
     * {@inheritDoc}
     */
    public function updateConfiguration(string $tenantId, string $key, mixed $value): void
    {
        $serialized = is_array($value) || is_object($value)
            ? json_encode($value, JSON_THROW_ON_ERROR)
            : (string) $value;

        ConfigModel::query()->updateOrCreate(
            [
                'tenant_id'  => $tenantId,
                'config_key' => $key,
            ],
            [
                'id'           => Uuid::uuid4()->toString(),
                'config_value' => $serialized,
                'environment'  => 'production',
                'is_secret'    => false,
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getConfiguration(string $tenantId, string $key): mixed
    {
        /** @var ConfigModel|null $config */
        $config = ConfigModel::query()
            ->where('tenant_id', $tenantId)
            ->where('config_key', $key)
            ->first();

        if ($config === null) {
            return null;
        }

        return TenantConfiguration::fromArray($config->toArray())->getValue();
    }

    /**
     * {@inheritDoc}
     *
     * @return array<string, mixed>
     */
    public function getAllConfigurations(string $tenantId): array
    {
        return ConfigModel::query()
            ->where('tenant_id', $tenantId)
            ->get()
            ->mapWithKeys(function (ConfigModel $model): array {
                $entity = TenantConfiguration::fromArray($model->toArray());

                return [$entity->getConfigKey() => $entity->getValue()];
            })
            ->all();
    }

    /**
     * {@inheritDoc}
     */
    public function provisionDatabase(string $tenantId): bool
    {
        $tenantData = $this->findById($tenantId);

        if ($tenantData === null) {
            throw new ModelNotFoundException('Tenant not found: ' . $tenantId);
        }

        $dbName  = $tenantData['database_name'];
        $escaped = '`' . str_replace('`', '``', $dbName) . '`';

        DB::statement(
            "CREATE DATABASE IF NOT EXISTS {$escaped} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteDatabase(string $tenantId): bool
    {
        $tenantData = $this->findById($tenantId);

        if ($tenantData === null) {
            throw new ModelNotFoundException('Tenant not found: ' . $tenantId);
        }

        $dbName  = $tenantData['database_name'];
        $escaped = '`' . str_replace('`', '``', $dbName) . '`';

        DB::statement("DROP DATABASE IF EXISTS {$escaped}");

        return true;
    }

    // ──────────────────────────────────────────────────────────────────────
    // RepositoryInterface — findByTenant is a no-op for the tenant repository
    // itself (tenants are not scoped to a tenant), but we override to keep
    // the contract complete.
    // ──────────────────────────────────────────────────────────────────────

    /**
     * {@inheritDoc}
     *
     * For tenants, this returns all tenants on a specific plan (treating
     * $tenantId as a plan filter) — or all tenants when $tenantId is empty.
     *
     * @return array<int, array>
     */
    public function findByTenant(string $tenantId, array $filters = []): array
    {
        return $this->newQuery()
            ->where('id', $tenantId)
            ->get()
            ->toArray();
    }
}
