<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Services;

use Illuminate\Support\Collection;
use Modules\Tenant\Application\Contracts\TenantServiceInterface;
use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;

class TenantService implements TenantServiceInterface
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository,
    ) {}

    public function create(array $data): Tenant
    {
        return $this->tenantRepository->create($data);
    }

    public function update(int $id, array $data): Tenant
    {
        return $this->tenantRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->tenantRepository->delete($id);
    }

    public function findById(int $id): ?Tenant
    {
        return $this->tenantRepository->findById($id);
    }

    public function findBySlug(string $slug): ?Tenant
    {
        return $this->tenantRepository->findBySlug($slug);
    }

    public function findByDomain(string $domain): ?Tenant
    {
        return $this->tenantRepository->findByDomain($domain);
    }

    public function list(): Collection
    {
        return $this->tenantRepository->all();
    }
}
