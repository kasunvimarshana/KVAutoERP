<?php

declare(strict_types=1);

namespace Modules\User\Application\Services;

use Modules\User\Application\Contracts\GetUserServiceInterface;
use Modules\User\Domain\Entities\User;
use Modules\User\Domain\Exceptions\UserNotFoundException;
use Modules\User\Domain\Repositories\UserRepositoryInterface;

class GetUserService implements GetUserServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function execute(int $id): User
    {
        $user = $this->repository->findById($id);

        if ($user === null) {
            throw new UserNotFoundException($id);
        }

        return $user;
    }
}
