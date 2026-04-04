<?php

declare(strict_types=1);

namespace Modules\User\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\User\Application\Contracts\ChangePasswordServiceInterface;
use Modules\User\Application\DTOs\ChangePasswordData;
use Modules\User\Domain\Events\UserPasswordChanged;
use Modules\User\Domain\Exceptions\InvalidPasswordException;
use Modules\User\Domain\Exceptions\UserNotFoundException;
use Modules\User\Domain\Repositories\UserRepositoryInterface;

class ChangePasswordService implements ChangePasswordServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function execute(int $id, ChangePasswordData $data): bool
    {
        return DB::transaction(function () use ($id, $data): bool {
            $user = $this->repository->findById($id);

            if ($user === null) {
                throw new UserNotFoundException($id);
            }

            if (! $this->repository->verifyPassword($id, $data->currentPassword)) {
                throw new InvalidPasswordException();
            }

            $result = $this->repository->changePassword($id, $data->newPassword);

            if ($result) {
                Event::dispatch(new UserPasswordChanged($id, $user->tenantId));
            }

            return $result;
        });
    }
}
