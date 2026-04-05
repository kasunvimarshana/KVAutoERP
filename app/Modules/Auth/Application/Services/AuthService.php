<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Services;

use Illuminate\Contracts\Hashing\Hasher;
use Modules\Auth\Application\Contracts\AuthServiceInterface;
use Modules\Auth\Domain\Entities\User;
use Modules\Auth\Domain\RepositoryInterfaces\UserRepositoryInterface;
use Modules\Core\Domain\Exceptions\DomainException;
use Modules\Core\Domain\Exceptions\NotFoundException;

final class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly Hasher $hasher,
    ) {}

    public function login(array $credentials): string
    {
        $tenantId = isset($credentials['tenant_id']) ? (int) $credentials['tenant_id'] : null;
        $user     = $this->userRepository->findByEmail($credentials['email'], $tenantId);

        if ($user === null || ! $this->hasher->check($credentials['password'], $user->password)) {
            throw new DomainException('Invalid credentials.');
        }

        if (! $user->isActive()) {
            throw new DomainException('User account is not active.');
        }

        return $this->userRepository->createToken($user->id, 'api');
    }

    public function logout(User $user): void
    {
        $this->userRepository->revokeCurrentToken($user->id);
    }

    public function register(array $data): User
    {
        $data['password'] = $this->hasher->make($data['password']);
        $data['status']   = $data['status'] ?? User::STATUS_ACTIVE;
        $data['role']     = $data['role'] ?? User::ROLE_EMPLOYEE;

        return $this->userRepository->create($data);
    }

    public function refreshToken(User $user): string
    {
        $this->userRepository->revokeCurrentToken($user->id);

        return $this->userRepository->createToken($user->id, 'api');
    }

    public function validateToken(string $token): ?User
    {
        return $this->userRepository->findByAccessToken($token);
    }

    public function assignRole(int $userId, int $roleId): void
    {
        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            throw new NotFoundException("User with ID {$userId} not found.");
        }

        $this->userRepository->assignRole($userId, $roleId);
    }

    public function revokeRole(int $userId, int $roleId): void
    {
        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            throw new NotFoundException("User with ID {$userId} not found.");
        }

        $this->userRepository->revokeRole($userId, $roleId);
    }
}
