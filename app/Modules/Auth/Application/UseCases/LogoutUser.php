<?php

namespace Modules\Auth\Application\UseCases;

use Modules\Auth\Application\Contracts\LogoutServiceInterface;

class LogoutUser
{
    public function __construct(
        private readonly LogoutServiceInterface $logoutService,
    ) {}

    public function execute(int $userId): bool
    {
        return $this->logoutService->logout($userId);
    }
}
