<?php

declare(strict_types=1);

namespace Modules\User\Application\Services;

use Illuminate\Support\Facades\Event;
use Modules\User\Application\Contracts\UploadAvatarServiceInterface;
use Modules\User\Domain\Entities\User;
use Modules\User\Domain\Events\UserAvatarUpdated;
use Modules\User\Domain\Exceptions\UserNotFoundException;
use Modules\User\Domain\RepositoryInterfaces\UserRepositoryInterface;

class UploadAvatarService implements UploadAvatarServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, string $avatarPath): User
    {
        $user = $this->repository->findById($userId);
        if ($user === null) {
            throw new UserNotFoundException($userId);
        }

        $this->repository->updateAvatar($userId, $avatarPath);

        $updated = $this->repository->findById($userId);

        Event::dispatch(new UserAvatarUpdated($updated->tenantId, $updated->id, $updated->orgUnitId));

        return $updated;
    }
}
