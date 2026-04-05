<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Services;

use Illuminate\Support\Facades\Hash;
use Modules\Auth\Application\Contracts\AuthServiceInterface;
use Modules\Auth\Domain\Entities\User;
use Modules\Auth\Domain\RepositoryInterfaces\UserRepositoryInterface;
use Modules\Core\Domain\Exceptions\DomainException;
use Modules\Core\Domain\Exceptions\NotFoundException;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function login(string $email, string $password, ?int $tenantId = null): User
    {
        $user = $this->repository->findByEmail($email, $tenantId);

        if ($user === null) {
            throw new NotFoundException("No account found for '{$email}'.");
        }

        if (! Hash::check($password, $user->getPassword())) {
            throw new DomainException('Invalid credentials.');
        }

        if (! $user->isActive()) {
            throw new DomainException('Account is not active.');
        }

        return $user;
    }

    public function logout(int $userId): void
    {
        // Token revocation is handled at the HTTP/infrastructure layer (Passport).
        // Domain-level hook is intentionally a no-op; extend here for audit events.
    }

    public function refresh(int $userId): array
    {
        // Token generation belongs to the infrastructure layer (Passport).
        // Return a placeholder; the controller/middleware should issue the real token.
        return [
            'user_id'    => $userId,
            'expires_at' => null,
        ];
    }
}
