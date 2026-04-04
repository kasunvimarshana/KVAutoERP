<?php

declare(strict_types=1);

namespace Modules\User\Application\Services;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Modules\User\Application\Contracts\ChangePasswordServiceInterface;
use Modules\User\Application\DTOs\ChangePasswordData;
use Modules\User\Domain\Events\UserPasswordChanged;
use Modules\User\Domain\Exceptions\InvalidCredentialsException;
use Modules\User\Domain\Exceptions\UserNotFoundException;
use Modules\User\Domain\RepositoryInterfaces\UserRepositoryInterface;

class ChangePasswordService implements ChangePasswordServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function execute(ChangePasswordData $data): void
    {
        $user = $this->repository->findById($data->userId);
        if ($user === null) {
            throw new UserNotFoundException($data->userId);
        }

        if (!$this->repository->verifyPassword($data->userId, $data->currentPassword)) {
            throw new InvalidCredentialsException();
        }

        $newHashed = Hash::make($data->newPassword);
        $this->repository->changePassword($data->userId, $newHashed);

        Event::dispatch(new UserPasswordChanged($user->tenantId, $user->id, $user->orgUnitId));
    }
}
