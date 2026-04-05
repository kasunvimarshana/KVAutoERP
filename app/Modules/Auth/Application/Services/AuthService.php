<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Services;

use Illuminate\Support\Facades\Hash;
use Modules\Auth\Application\Contracts\AuthServiceInterface;
use Modules\Auth\Domain\Entities\User;
use Modules\Auth\Domain\RepositoryInterfaces\UserRepositoryInterface;
use Modules\Auth\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository;
use Modules\Core\Domain\Exceptions\DomainException;
use Modules\Core\Domain\Exceptions\NotFoundException;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function register(array $data): array
    {
        $data['status'] = 'active';
        $data['password'] = Hash::make($data['password']);

        $existing = $this->repository->findByEmail($data['email'], $data['tenant_id']);
        if ($existing) {
            throw new DomainException('Email already registered for this tenant.');
        }

        $user = $this->repository->create($data);

        /** @var EloquentUserRepository $repo */
        $repo = $this->repository;
        $model = $repo->findModelById($user->getId());
        $token = $model->createToken('auth-token')->accessToken;

        return ['user' => $user, 'token' => $token];
    }

    public function login(array $credentials): array
    {
        /** @var EloquentUserRepository $repo */
        $repo = $this->repository;
        $model = $repo->findModelByEmail($credentials['email'], $credentials['tenant_id']);

        if (! $model || ! Hash::check($credentials['password'], $model->password)) {
            throw new DomainException('Invalid credentials.');
        }

        if ($model->status !== 'active') {
            throw new DomainException('Account is inactive.');
        }

        $model->update(['last_login_at' => now()]);
        $token = $model->createToken('auth-token')->accessToken;

        return ['user' => $this->repository->findById($model->id), 'token' => $token];
    }

    public function logout(string $userId): void
    {
        /** @var EloquentUserRepository $repo */
        $repo = $this->repository;
        $model = $repo->findModelById($userId);

        if (! $model) {
            throw new NotFoundException('User', $userId);
        }

        $model->tokens()->delete();
    }

    public function refreshToken(string $userId): array
    {
        /** @var EloquentUserRepository $repo */
        $repo = $this->repository;
        $model = $repo->findModelById($userId);

        if (! $model) {
            throw new NotFoundException('User', $userId);
        }

        $model->tokens()->delete();
        $token = $model->createToken('auth-token')->accessToken;

        return ['token' => $token];
    }

    public function me(string $userId): User
    {
        $user = $this->repository->findById($userId);

        if (! $user) {
            throw new NotFoundException('User', $userId);
        }

        return $user;
    }
}
