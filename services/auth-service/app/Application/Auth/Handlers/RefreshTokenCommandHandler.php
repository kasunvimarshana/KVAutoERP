<?php

declare(strict_types=1);

namespace App\Application\Auth\Handlers;

use App\Application\Auth\Commands\RefreshTokenCommand;
use App\Domain\Auth\Events\TokenRefreshed;
use App\Domain\Auth\Services\AuthDomainService;
use Illuminate\Support\Facades\Event;
use Ramsey\Uuid\Uuid;

/**
 * Refresh Token Command Handler.
 *
 * Validates the provided refresh token, issues a fresh access + refresh token
 * pair, and fires the TokenRefreshed domain event.
 */
final class RefreshTokenCommandHandler
{
    public function __construct(
        private readonly AuthDomainService $authDomainService,
    ) {}

    /**
     * Handle the token refresh command.
     *
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int}
     *
     * @throws \RuntimeException  When the refresh token is invalid or expired.
     */
    public function handle(RefreshTokenCommand $command): array
    {
        $oldTokenId = Uuid::uuid4()->toString();
        $newTokenId = Uuid::uuid4()->toString();

        $tokens = $this->authDomainService->refreshToken($command->refreshToken);

        Event::dispatch(new TokenRefreshed(
            userId: '',
            tenantId: $command->tenantId,
            oldTokenId: $oldTokenId,
            newTokenId: $newTokenId,
        ));

        return array_merge($tokens, ['token_type' => 'Bearer']);
    }
}
