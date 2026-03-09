<?php

declare(strict_types=1);

namespace App\Domain\Auth\Events;

use DateTimeImmutable;

/**
 * Domain event raised when a user successfully authenticates.
 */
final readonly class UserLoggedIn
{
    public DateTimeImmutable $timestamp;

    public function __construct(
        public string $userId,
        public string $tenantId,
        public string $email,
        public string $ipAddress,
        public string $userAgent,
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
            'event'      => 'user.logged_in',
            'user_id'    => $this->userId,
            'tenant_id'  => $this->tenantId,
            'email'      => $this->email,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'timestamp'  => $this->timestamp->format(DateTimeImmutable::ATOM),
        ];
    }
}
