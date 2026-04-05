<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Application\Contracts\UserServiceInterface;
use Modules\Auth\Domain\Entities\User;
use Modules\Auth\Domain\RepositoryInterfaces\UserRepositoryInterface;
use Modules\Core\Domain\Exceptions\DomainException;
use Modules\Core\Domain\Exceptions\NotFoundException;

class UserService implements UserServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function createUser(array $data): User
    {
        $data['status'] = $data['status'] ?? 'active';

        // Check for duplicates before hashing to avoid unnecessary computation.
        $existing = $this->repository->findByEmail($data['email'], $data['tenant_id']);
        if ($existing) {
            throw new DomainException('Email already exists for this tenant.');
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->repository->create($data);
    }

    public function updateUser(string $id, array $data): User
    {
        $this->getUser($id);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->repository->update($id, $data);
    }

    public function deleteUser(string $id): bool
    {
        $this->getUser($id);

        return $this->repository->delete($id);
    }

    public function getUser(string $id): User
    {
        $user = $this->repository->findById($id);

        if (! $user) {
            throw new NotFoundException('User', $id);
        }

        return $user;
    }

    public function getAllByTenant(string $tenantId): Collection
    {
        return $this->repository->allByTenant($tenantId);
    }
}
