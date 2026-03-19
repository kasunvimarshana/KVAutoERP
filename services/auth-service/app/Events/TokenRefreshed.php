<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TokenRefreshed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string             $userId,
        public readonly string             $deviceId,
        public readonly \DateTimeImmutable $occurredAt = new \DateTimeImmutable(),
    ) {}
}
