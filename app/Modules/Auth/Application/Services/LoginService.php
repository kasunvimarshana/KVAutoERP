<?php

namespace Modules\Auth\Application\Services;

use Modules\Auth\Application\Contracts\AuthenticationServiceInterface;
use Modules\Auth\Application\Contracts\LoginServiceInterface;
use Modules\Auth\Domain\Entities\AccessToken;
use Modules\Auth\Domain\Events\UserLoggedIn;
use Modules\User\Infrastructure\Persistence\Eloquent\Models\UserModel;

class LoginService implements LoginServiceInterface
{
    public function __construct(
        private readonly AuthenticationServiceInterface $authService,
    ) {}

    public function login(string $email, string $password): AccessToken
    {
        $token = $this->authService->authenticate($email, $password);

        /** @var UserModel|null $user */
        $user = UserModel::where('email', $email)->first();

        if ($user) {
            UserLoggedIn::dispatch($user->id, $user->email);
        }

        return $token;
    }
}
