<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Infrastructure\Repositories;

use App\Core\Abstracts\Repositories\BaseRepository;
use App\Modules\Tenant\Domain\Models\Tenant;
use Illuminate\Database\Eloquent\Model;

/**
 * TenantRepository
 *
 * Concrete repository for Tenant persistence.
 * Extends BaseRepository to inherit full CRUD, filtering, sorting,
 * searching, and conditional pagination without duplicating logic.
 */
class TenantRepository extends BaseRepository
{
    protected string $model = Tenant::class;

    protected array $searchableColumns = ['name', 'slug', 'domain'];

    protected array $filterableColumns = ['plan', 'is_active', 'domain'];

    protected array $sortableColumns = ['name', 'created_at', 'updated_at', 'plan'];

    /**
     * Find a tenant by its unique slug.
     */
    public function findBySlug(string $slug): ?Model
    {
        return $this->findBy(['slug' => $slug]);
    }

    /**
     * Find a tenant by domain (used in subdomain-based tenant resolution).
     */
    public function findByDomain(string $domain): ?Model
    {
        return $this->findBy(['domain' => $domain]);
    }

    /**
     * Return only active tenants.
     */
    public function allActive(
        array $sort = [],
        array $with = [],
        ?int $perPage = null,
        int $page = 1
    ): \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection {
        return $this->all(
            filters:  ['is_active' => true],
            sort:     $sort,
            with:     $with,
            perPage:  $perPage,
            page:     $page
        );
    }
}
