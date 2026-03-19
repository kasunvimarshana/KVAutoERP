<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\User;

final readonly class AuthResultDto
{
    public function __construct(
        public User $user,
        public TokenPairDto $tokenPair,
        public string $sessionId,
    ) {}
}
