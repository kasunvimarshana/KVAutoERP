<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Tenant\Entities\Tenant;
use App\Domain\Tenant\Repositories\Interfaces\TenantRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent Tenant Repository Implementation.
 *
 * Extends BaseRepository with Tenant-specific query methods.
 */
class TenantRepository extends BaseRepository implements TenantRepositoryInterface
{
    protected array $searchableColumns = ['name', 'slug', 'domain'];

    protected array $filterableColumns = ['status', 'plan', 'name', 'slug', 'domain', 'created_at'];

    protected function resolveModel(): Model
    {
        return new Tenant();
    }

    // =========================================================================
    // TenantRepositoryInterface Implementation
    // =========================================================================

    public function all(array $params = []): LengthAwarePaginator|Collection
    {
        return parent::all($params);
    }

    public function find(string $id): ?Tenant
    {
        /** @var Tenant|null */
        return parent::find($id);
    }

    public function findBySlug(string $slug): ?Tenant
    {
        /** @var Tenant|null */
        return $this->findOneBy(['slug' => $slug]);
    }

    public function findByDomain(string $domain): ?Tenant
    {
        /** @var Tenant|null */
        return $this->findOneBy(['domain' => $domain]);
    }

    public function create(array $data): Tenant
    {
        /** @var Tenant */
        return parent::create($data);
    }

    public function update(string $id, array $data): Tenant
    {
        /** @var Tenant */
        return parent::update($id, $data);
    }

    public function updateConfiguration(string $id, array $config): Tenant
    {
        $tenant = $this->findOrFail($id);

        // Merge configuration to preserve unspecified keys
        $existing = $tenant->configuration ?? [];
        $merged   = array_replace_recursive($existing, $config);

        $tenant->update(['configuration' => $merged]);

        return $tenant->fresh() ?? $tenant;
    }

    public function delete(string $id): bool
    {
        return parent::delete($id);
    }
}
