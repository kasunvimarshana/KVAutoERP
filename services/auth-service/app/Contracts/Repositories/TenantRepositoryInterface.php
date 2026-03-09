<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Domain\Tenant\Entities\Tenant;

interface TenantRepositoryInterface extends RepositoryInterface
{
    public function findBySlug(string $slug): ?Tenant;

    public function findByDomain(string $domain): ?Tenant;

    public function findActive(): \Illuminate\Support\Collection;
}
