<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Services;

use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Tenant\Application\Contracts\TenantServiceInterface;
use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;

class TenantService implements TenantServiceInterface
{
    public function __construct(
        private readonly TenantRepositoryInterface $repository,
    ) {}

    public function create(array $data): Tenant
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Tenant
    {
        $tenant = $this->repository->update($id, $data);

        if ($tenant === null) {
            throw new NotFoundException("Tenant with id {$id} not found.");
        }

        return $tenant;
    }

    public function suspend(int $id): void
    {
        $this->update($id, ['status' => 'suspended']);
    }

    public function activate(int $id): void
    {
        $this->update($id, ['status' => 'active']);
    }

    public function find(int $id): Tenant
    {
        $tenant = $this->repository->findById($id);

        if ($tenant === null) {
            throw new NotFoundException("Tenant with id {$id} not found.");
        }

        return $tenant;
    }

    public function findBySlug(string $slug): Tenant
    {
        $tenant = $this->repository->findBySlug($slug);

        if ($tenant === null) {
            throw new NotFoundException("Tenant with slug '{$slug}' not found.");
        }

        return $tenant;
    }

    public function all(): array
    {
        return $this->repository->all();
    }
}
