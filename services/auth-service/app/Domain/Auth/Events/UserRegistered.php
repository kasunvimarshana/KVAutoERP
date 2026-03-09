<?php

declare(strict_types=1);

namespace App\Domain\Auth\Events;

use DateTimeImmutable;

/**
 * Domain event raised when a new user account is created.
 */
final readonly class UserRegistered
{
    public DateTimeImmutable $timestamp;

    public function __construct(
        public string $userId,
        public string $tenantId,
        public string $email,
        public string $name,
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
            'event'     => 'user.registered',
            'user_id'   => $this->userId,
            'tenant_id' => $this->tenantId,
            'email'     => $this->email,
            'name'      => $this->name,
            'timestamp' => $this->timestamp->format(DateTimeImmutable::ATOM),
        ];
    }
}
