<?php
declare(strict_types=1);
namespace Modules\Auth\Domain\Events;

class UserLoggedIn {
    public function __construct(
        public readonly int $userId,
        public readonly string $email,
        public readonly string $ipAddress,
        public readonly string $userAgent = ''
    ) {}
}
