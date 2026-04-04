<?php

declare(strict_types=1);

namespace Modules\User\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\User\Application\Contracts\DeleteUserServiceInterface;
use Modules\User\Domain\Events\UserDeleted;
use Modules\User\Domain\Exceptions\UserNotFoundException;
use Modules\User\Domain\Repositories\UserRepositoryInterface;

class DeleteUserService implements DeleteUserServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function execute(int $id): bool
    {
        return DB::transaction(function () use ($id): bool {
            $user = $this->repository->findById($id);

            if ($user === null) {
                throw new UserNotFoundException($id);
            }

            $result = $this->repository->delete($id);

            if ($result) {
                Event::dispatch(new UserDeleted($id, $user->tenantId));
            }

            return $result;
        });
    }
}
