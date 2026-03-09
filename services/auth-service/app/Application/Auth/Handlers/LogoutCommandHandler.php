<?php

declare(strict_types=1);

namespace App\Application\Auth\Handlers;

use App\Application\Auth\Commands\LogoutCommand;
use App\Domain\Auth\Events\UserLoggedOut;
use App\Domain\Auth\Services\AuthDomainService;
use Illuminate\Support\Facades\Event;

/**
 * Logout Command Handler.
 *
 * Revokes the user's tokens and fires the UserLoggedOut domain event.
 */
final class LogoutCommandHandler
{
    public function __construct(
        private readonly AuthDomainService $authDomainService,
    ) {}

    /**
     * Handle the logout command.
     *
     * When $command->allDevices is true, all tokens are revoked (every active
     * session).  Otherwise only the current token is revoked.
     */
    public function handle(LogoutCommand $command): void
    {
        $this->authDomainService->revokeTokens($command->userId);

        Event::dispatch(new UserLoggedOut(
            userId: $command->userId,
            tenantId: $command->tenantId,
            allDevices: $command->allDevices,
        ));
    }
}
