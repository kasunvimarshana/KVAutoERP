<?php

declare(strict_types=1);

namespace App\Application\Contracts\Services;

use App\Application\DTOs\LoginDTO;
use App\Application\DTOs\RegisterDTO;
use App\Application\DTOs\TokenDTO;
use App\Domain\Models\User;

/**
 * Authentication Service Contract
 */
interface AuthServiceInterface
{
    public function login(LoginDTO $dto): TokenDTO;
    public function register(RegisterDTO $dto): TokenDTO;
    public function logout(User $user): bool;
    public function refreshToken(string $refreshToken): TokenDTO;
    public function validateToken(string $token): ?User;
    public function introspect(string $token): array;
}
