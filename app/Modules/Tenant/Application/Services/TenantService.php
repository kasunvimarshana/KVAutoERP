<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Services;

use Illuminate\Support\Collection;
use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Tenant\Application\Contracts\TenantServiceInterface;
use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;

class TenantService implements TenantServiceInterface
{
    public function __construct(
        private readonly TenantRepositoryInterface $repository,
    ) {}

    public function createTenant(array $data): Tenant
    {
        $data['status'] = $data['status'] ?? 'trial';
        $data['settings'] = $data['settings'] ?? [];

        return $this->repository->create($data);
    }

    public function updateTenant(string $id, array $data): Tenant
    {
        $this->getTenant($id);

        return $this->repository->update($id, $data);
    }

    public function suspendTenant(string $id): Tenant
    {
        $this->getTenant($id);

        return $this->repository->update($id, ['status' => 'suspended']);
    }

    public function activateTenant(string $id): Tenant
    {
        $this->getTenant($id);

        return $this->repository->update($id, ['status' => 'active']);
    }

    public function getTenant(string $id): Tenant
    {
        $tenant = $this->repository->findById($id);

        if (! $tenant) {
            throw new NotFoundException('Tenant', $id);
        }

        return $tenant;
    }

    public function getAll(): Collection
    {
        return $this->repository->all();
    }
}
