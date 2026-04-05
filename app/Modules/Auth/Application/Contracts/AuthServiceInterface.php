<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Contracts;

use Modules\Auth\Domain\Entities\User;

interface AuthServiceInterface
{
    public function register(array $data): array;
    public function login(array $credentials): array;
    public function logout(string $userId): void;
    public function refreshToken(string $userId): array;
    public function me(string $userId): User;
}
