<?php

declare(strict_types=1);

namespace App\Domain\Auth\Events;

use DateTimeImmutable;

/**
 * Domain event raised when a user logs out (tokens revoked).
 */
final readonly class UserLoggedOut
{
    public DateTimeImmutable $timestamp;

    public function __construct(
        public string $userId,
        public string $tenantId,
        public bool $allDevices,
        ?DateTimeImmutable $timestamp = null,
    ) {
        $this->timestamp = $timestamp ?? new DateTimeImmutable();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'event'       => 'user.logged_out',
            'user_id'     => $this->userId,
            'tenant_id'   => $this->tenantId,
            'all_devices' => $this->allDevices,
            'timestamp'   => $this->timestamp->format(DateTimeImmutable::ATOM),
        ];
    }
}
