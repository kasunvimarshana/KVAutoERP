<?php

declare(strict_types=1);

namespace Modules\User\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\User\Application\Contracts\UploadAvatarServiceInterface;
use Modules\User\Domain\Entities\User;
use Modules\User\Domain\Events\UserAvatarUpdated;
use Modules\User\Domain\Exceptions\UserNotFoundException;
use Modules\User\Domain\Repositories\UserRepositoryInterface;

class UploadAvatarService implements UploadAvatarServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function execute(int $id, string $avatarPath): User
    {
        return DB::transaction(function () use ($id, $avatarPath): User {
            $user = $this->repository->findById($id);

            if ($user === null) {
                throw new UserNotFoundException($id);
            }

            $user = $this->repository->updateAvatar($id, $avatarPath);

            Event::dispatch(new UserAvatarUpdated($id, $user->tenantId, $avatarPath));

            return $user;
        });
    }
}
