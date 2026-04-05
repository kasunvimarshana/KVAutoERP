<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Services;

use Modules\Auth\Application\Contracts\UserServiceInterface;
use Modules\Auth\Domain\Entities\User;
use Modules\Auth\Domain\RepositoryInterfaces\UserRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

class UserService implements UserServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function create(array $data): User
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): User
    {
        $user = $this->repository->update($id, $data);

        if ($user === null) {
            throw new NotFoundException("User with id {$id} not found.");
        }

        return $user;
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function find(int $id): User
    {
        $user = $this->repository->findById($id);

        if ($user === null) {
            throw new NotFoundException("User with id {$id} not found.");
        }

        return $user;
    }

    public function findByEmail(string $email, ?int $tenantId = null): User
    {
        $user = $this->repository->findByEmail($email, $tenantId);

        if ($user === null) {
            throw new NotFoundException("User with email '{$email}' not found.");
        }

        return $user;
    }

    public function allForTenant(int $tenantId): array
    {
        return $this->repository->allForTenant($tenantId);
    }

    public function assignRole(int $userId, int $roleId): void
    {
        $this->repository->assignRole($userId, $roleId);
    }

    public function removeRole(int $userId, int $roleId): void
    {
        $this->repository->removeRole($userId, $roleId);
    }
}
