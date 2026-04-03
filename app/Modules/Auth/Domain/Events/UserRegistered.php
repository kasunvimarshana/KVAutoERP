<?php
declare(strict_types=1);
namespace Modules\Auth\Domain\Events;

class UserRegistered {
    public function __construct(
        public readonly int $userId,
        public readonly string $email,
        public readonly string $firstName,
        public readonly string $lastName
    ) {}
}
