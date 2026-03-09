<?php

declare(strict_types=1);

namespace App\Application\Auth\Handlers;

use App\Application\Auth\Commands\LogoutCommand;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Domain\User\Events\UserLoggedOut;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use RuntimeException;

final class LogoutHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * @throws RuntimeException
     */
    public function handle(LogoutCommand $command): void
    {
        $user = $this->userRepository->findById($command->userId);

        if ($user === null) {
            throw new RuntimeException("User [{$command->userId}] not found.", 404);
        }

        if ($command->revokeAll) {
            // Revoke all tokens for this user
            $user->tokens()->each(function ($token): void {
                $token->revoke();
            });
        } else {
            // Revoke only the current token
            $user->token()?->revoke();
        }

        Event::dispatch(new UserLoggedOut($user, $command->tenantId));

        Log::info('User logged out', [
            'user_id'    => $command->userId,
            'tenant_id'  => $command->tenantId,
            'revoke_all' => $command->revokeAll,
        ]);
    }
}
