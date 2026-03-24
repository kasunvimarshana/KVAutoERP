<?php

namespace Modules\Auth\Application\Services;

use Modules\Auth\Application\Contracts\AuthenticationServiceInterface;
use Modules\Auth\Application\Contracts\LogoutServiceInterface;
use Modules\Auth\Domain\Events\UserLoggedOut;
use Modules\User\Infrastructure\Persistence\Eloquent\Models\UserModel;

class LogoutService implements LogoutServiceInterface
{
    public function __construct(
        private readonly AuthenticationServiceInterface $authService,
    ) {}

    public function logout(int $userId): bool
    {
        $result = $this->authService->invalidate($userId);

        if ($result) {
            /** @var UserModel|null $user */
            $user = UserModel::find($userId);
            if ($user) {
                UserLoggedOut::dispatch($user->id, $user->email);
            }
        }

        return $result;
    }
}
