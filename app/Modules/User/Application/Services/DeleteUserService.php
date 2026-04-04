<?php

declare(strict_types=1);

namespace Modules\User\Application\Services;

use Illuminate\Support\Facades\Event;
use Modules\User\Application\Contracts\DeleteUserServiceInterface;
use Modules\User\Domain\Events\UserDeleted;
use Modules\User\Domain\Exceptions\UserNotFoundException;
use Modules\User\Domain\RepositoryInterfaces\UserRepositoryInterface;

class DeleteUserService implements DeleteUserServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function execute(int $id): void
    {
        $user = $this->repository->findById($id);
        if ($user === null) {
            throw new UserNotFoundException($id);
        }

        Event::dispatch(new UserDeleted($user->tenantId, $id, $user->orgUnitId));

        $this->repository->delete($id);
    }
}
