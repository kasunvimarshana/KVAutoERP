<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Contracts\Repositories\TenantRepositoryInterface;
use App\Domain\Tenant\Entities\Tenant;
use App\Support\Repository\BaseRepository;
use Illuminate\Support\Collection;

class TenantRepository extends BaseRepository implements TenantRepositoryInterface
{
    /** @var class-string<Tenant> */
    protected string $model = Tenant::class;

    public function findBySlug(string $slug): ?Tenant
    {
        /** @var Tenant|null */
        return $this->newQuery()
            ->where('slug', $slug)
            ->first();
    }

    public function findByDomain(string $domain): ?Tenant
    {
        /** @var Tenant|null */
        return $this->newQuery()
            ->where('domain', $domain)
            ->first();
    }

    public function findActive(): Collection
    {
        return $this->newQuery()
            ->where('status', Tenant::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();
    }
}
