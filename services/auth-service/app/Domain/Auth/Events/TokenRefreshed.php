<?php

declare(strict_types=1);

namespace App\Domain\Auth\Events;

use DateTimeImmutable;

/**
 * Domain event raised when an access token is successfully refreshed.
 */
final readonly class TokenRefreshed
{
    public DateTimeImmutable $timestamp;

    public function __construct(
        public string $userId,
        public string $tenantId,
        public string $oldTokenId,
        public string $newTokenId,
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
            'event'        => 'token.refreshed',
            'user_id'      => $this->userId,
            'tenant_id'    => $this->tenantId,
            'old_token_id' => $this->oldTokenId,
            'new_token_id' => $this->newTokenId,
            'timestamp'    => $this->timestamp->format(DateTimeImmutable::ATOM),
        ];
    }
}
