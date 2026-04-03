<?php
declare(strict_types=1);
namespace Modules\Auth\Domain\Events;

class UserLoggedOut {
    public function __construct(
        public readonly int $userId,
        public readonly string $email
    ) {}
}
